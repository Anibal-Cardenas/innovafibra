-- ============================================================================
-- MIGRACIÓN: Cambiar producciones de lotes a cubos individuales
-- Fecha: 2026-01-09
-- Descripción: 
-- - Agregar cantidad_estimada_bolsas y rendimiento_estimado a cubos_fibra
-- - Cambiar producciones para referenciar cubos en lugar de lotes
-- - Actualizar triggers para trabajar por cubo
-- ============================================================================

USE `sistema_napa`;

-- ============================================================================
-- PASO 1: MODIFICAR TABLA cubos_fibra
-- ============================================================================

-- Agregar columnas para estimación por cubo
ALTER TABLE `cubos_fibra` 
ADD COLUMN `cantidad_estimada_bolsas` INT UNSIGNED NOT NULL DEFAULT 70 COMMENT 'Rendimiento estimado por este cubo' AFTER `peso_neto`,
ADD COLUMN `rendimiento_estimado` DECIMAL(10,4) NOT NULL COMMENT 'Bolsas por kg' AFTER `cantidad_estimada_bolsas`,
ADD COLUMN `cantidad_producida_real` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Acumulado de producción de este cubo' AFTER `rendimiento_estimado`;

-- Calcular valores para cubos existentes basados en el lote
UPDATE cubos_fibra c
INNER JOIN lotes_fibra l ON c.id_lote = l.id_lote
SET 
    c.cantidad_estimada_bolsas = ROUND((c.peso_neto / l.peso_neto) * l.cantidad_estimada_bolsas),
    c.rendimiento_estimado = l.rendimiento_estimado,
    c.cantidad_producida_real = CASE 
        WHEN l.estado = 'agotado' THEN ROUND((c.peso_neto / l.peso_neto) * l.cantidad_producida_real)
        ELSE 0
    END;

-- ============================================================================
-- PASO 2: MODIFICAR TABLA producciones
-- ============================================================================

-- Agregar columna id_cubo (mantenemos id_lote_fibra por ahora para migración)
ALTER TABLE `producciones`
ADD COLUMN `id_cubo` INT UNSIGNED NULL COMMENT 'Cubo específico usado' AFTER `id_lote_fibra`,
ADD INDEX `fk_prod_cubo_idx` (`id_cubo` ASC);

-- Migrar producciones existentes al primer cubo de cada lote
UPDATE producciones p
INNER JOIN (
    SELECT id_lote, MIN(id_cubo) as primer_cubo
    FROM cubos_fibra
    GROUP BY id_lote
) c ON p.id_lote_fibra = c.id_lote
SET p.id_cubo = c.primer_cubo
WHERE p.id_cubo IS NULL;

-- Hacer id_cubo NOT NULL después de migrar datos
ALTER TABLE `producciones`
MODIFY COLUMN `id_cubo` INT UNSIGNED NOT NULL;

-- Agregar foreign key para id_cubo
ALTER TABLE `producciones`
ADD CONSTRAINT `fk_prod_cubo`
    FOREIGN KEY (`id_cubo`)
    REFERENCES `cubos_fibra` (`id_cubo`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

-- ============================================================================
-- PASO 3: ELIMINAR TRIGGERS ANTIGUOS
-- ============================================================================

DROP TRIGGER IF EXISTS `trg_after_update_produccion_validacion`;
DROP TRIGGER IF EXISTS `trg_after_update_produccion_descontar_fibra`;
DROP TRIGGER IF EXISTS `trg_after_insert_produccion`;
DROP TRIGGER IF EXISTS `trg_after_update_lote_produccion`;

-- ============================================================================
-- PASO 4: CREAR NUEVOS TRIGGERS PARA CUBOS
-- ============================================================================

-- Trigger: Actualizar inventarios y cubo cuando se valida producción
DELIMITER $$
CREATE TRIGGER `trg_after_update_produccion_cubo_validacion`
AFTER UPDATE ON `producciones`
FOR EACH ROW
BEGIN
    DECLARE v_saldo_bolsas DECIMAL(12,2);
    DECLARE v_saldo_producto DECIMAL(12,2);
    
    -- Si cambió de NO aprobado a aprobado
    IF NEW.estado_validacion = 'aprobado' AND OLD.estado_validacion != 'aprobado' THEN
        
        -- Descontar bolsas plásticas del inventario
        UPDATE `inventario` 
        SET `cantidad` = `cantidad` - NEW.peso_bolsas_consumido
        WHERE `tipo_item` = 'bolsas_plasticas';
        
        SELECT `cantidad` INTO v_saldo_bolsas 
        FROM `inventario` WHERE `tipo_item` = 'bolsas_plasticas';
        
        INSERT INTO `kardex` (`tipo_item`, `tipo_movimiento`, `cantidad`, `unidad_medida`, 
                              `saldo_anterior`, `saldo_nuevo`, `referencia_tipo`, `referencia_id`,
                              `observaciones`)
        VALUES ('bolsas_plasticas', 'salida', NEW.peso_bolsas_consumido, 'kg',
                v_saldo_bolsas + NEW.peso_bolsas_consumido, v_saldo_bolsas, 
                'produccion', NEW.id_produccion,
                CONCAT('Producción validada ID:', NEW.id_produccion, ' Cubo:', NEW.id_cubo));
        
        -- Incrementar producto terminado
        UPDATE `inventario` 
        SET `cantidad` = `cantidad` + NEW.cantidad_producida
        WHERE `tipo_item` = 'producto_terminado';
        
        SELECT `cantidad` INTO v_saldo_producto 
        FROM `inventario` WHERE `tipo_item` = 'producto_terminado';
        
        INSERT INTO `kardex` (`tipo_item`, `tipo_movimiento`, `cantidad`, `unidad_medida`, 
                              `saldo_anterior`, `saldo_nuevo`, `referencia_tipo`, `referencia_id`,
                              `observaciones`)
        VALUES ('producto_terminado', 'entrada', NEW.cantidad_producida, 'unidades',
                v_saldo_producto - NEW.cantidad_producida, v_saldo_producto, 
                'produccion', NEW.id_produccion,
                CONCAT('Producción validada ID:', NEW.id_produccion, ' Cubo:', NEW.id_cubo));
        
        -- Actualizar cantidad producida del CUBO específico
        UPDATE `cubos_fibra` 
        SET `cantidad_producida_real` = `cantidad_producida_real` + NEW.cantidad_producida
        WHERE `id_cubo` = NEW.id_cubo;
        
        -- Actualizar estado del cubo
        UPDATE `cubos_fibra` c
        SET c.estado = CASE
            WHEN c.cantidad_producida_real >= c.cantidad_estimada_bolsas THEN 'agotado'
            WHEN c.cantidad_producida_real > 0 THEN 'en_uso'
            ELSE 'disponible'
        END
        WHERE c.id_cubo = NEW.id_cubo;
        
        -- Actualizar cantidad producida del LOTE (sumar todos los cubos)
        UPDATE `lotes_fibra` l
        SET l.cantidad_producida_real = (
            SELECT COALESCE(SUM(c.cantidad_producida_real), 0)
            FROM cubos_fibra c
            WHERE c.id_lote = l.id_lote
        )
        WHERE l.id_lote = NEW.id_lote_fibra;
        
        -- Actualizar estado del lote basado en sus cubos
        UPDATE `lotes_fibra` l
        SET l.estado = CASE
            WHEN (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote AND estado = 'disponible') = 0 
                 AND (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote AND estado IN ('en_uso', 'agotado')) > 0 
            THEN 'agotado'
            WHEN (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote AND estado IN ('en_uso', 'agotado')) > 0
            THEN 'en_proceso'
            ELSE 'disponible'
        END
        WHERE l.id_lote = NEW.id_lote_fibra;
        
    -- Si cambió de aprobado a NO aprobado (reversar)
    ELSEIF OLD.estado_validacion = 'aprobado' AND NEW.estado_validacion != 'aprobado' THEN
        
        -- Reversar bolsas plásticas
        UPDATE `inventario` 
        SET `cantidad` = `cantidad` + OLD.peso_bolsas_consumido
        WHERE `tipo_item` = 'bolsas_plasticas';
        
        SELECT `cantidad` INTO v_saldo_bolsas 
        FROM `inventario` WHERE `tipo_item` = 'bolsas_plasticas';
        
        INSERT INTO `kardex` (`tipo_item`, `tipo_movimiento`, `cantidad`, `unidad_medida`, 
                              `saldo_anterior`, `saldo_nuevo`, `referencia_tipo`, `referencia_id`,
                              `observaciones`)
        VALUES ('bolsas_plasticas', 'entrada', OLD.peso_bolsas_consumido, 'kg',
                v_saldo_bolsas - OLD.peso_bolsas_consumido, v_saldo_bolsas, 
                'produccion', OLD.id_produccion,
                CONCAT('Reversión producción ID:', OLD.id_produccion, ' Cubo:', OLD.id_cubo));
        
        -- Reversar producto terminado
        UPDATE `inventario` 
        SET `cantidad` = `cantidad` - OLD.cantidad_producida
        WHERE `tipo_item` = 'producto_terminado';
        
        SELECT `cantidad` INTO v_saldo_producto 
        FROM `inventario` WHERE `tipo_item` = 'producto_terminado';
        
        INSERT INTO `kardex` (`tipo_item`, `tipo_movimiento`, `cantidad`, `unidad_medida`, 
                              `saldo_anterior`, `saldo_nuevo`, `referencia_tipo`, `referencia_id`,
                              `observaciones`)
        VALUES ('producto_terminado', 'salida', OLD.cantidad_producida, 'unidades',
                v_saldo_producto + OLD.cantidad_producida, v_saldo_producto, 
                'produccion', OLD.id_produccion,
                CONCAT('Reversión producción ID:', OLD.id_produccion, ' Cubo:', OLD.id_cubo));
        
        -- Reversar cantidad producida del cubo
        UPDATE `cubos_fibra` 
        SET `cantidad_producida_real` = GREATEST(0, `cantidad_producida_real` - OLD.cantidad_producida)
        WHERE `id_cubo` = OLD.id_cubo;
        
        -- Actualizar estado del cubo
        UPDATE `cubos_fibra` c
        SET c.estado = CASE
            WHEN c.cantidad_producida_real >= c.cantidad_estimada_bolsas THEN 'agotado'
            WHEN c.cantidad_producida_real > 0 THEN 'en_uso'
            ELSE 'disponible'
        END
        WHERE c.id_cubo = OLD.id_cubo;
        
        -- Actualizar cantidad producida del lote
        UPDATE `lotes_fibra` l
        SET l.cantidad_producida_real = (
            SELECT COALESCE(SUM(c.cantidad_producida_real), 0)
            FROM cubos_fibra c
            WHERE c.id_lote = l.id_lote
        )
        WHERE l.id_lote = OLD.id_lote_fibra;
        
        -- Actualizar estado del lote
        UPDATE `lotes_fibra` l
        SET l.estado = CASE
            WHEN (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote AND estado = 'disponible') = 0 
                 AND (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote AND estado IN ('en_uso', 'agotado')) > 0 
            THEN 'agotado'
            WHEN (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote AND estado IN ('en_uso', 'agotado')) > 0
            THEN 'en_proceso'
            ELSE 'disponible'
        END
        WHERE l.id_lote = OLD.id_lote_fibra;
        
    END IF;
END$$
DELIMITER ;

-- Trigger: Marcar cubo como 'en_uso' al registrar producción pendiente
DELIMITER $$
CREATE TRIGGER `trg_after_insert_produccion_cubo`
AFTER INSERT ON `producciones`
FOR EACH ROW
BEGIN
    -- Marcar cubo como en_uso (si estaba disponible)
    UPDATE `cubos_fibra`
    SET `estado` = 'en_uso'
    WHERE `id_cubo` = NEW.id_cubo 
      AND `estado` = 'disponible';
    
    -- Actualizar estado del lote si es necesario
    UPDATE `lotes_fibra` l
    SET l.estado = 'en_proceso'
    WHERE l.id_lote = NEW.id_lote_fibra
      AND l.estado = 'disponible'
      AND EXISTS (
          SELECT 1 FROM cubos_fibra 
          WHERE id_lote = l.id_lote 
          AND estado IN ('en_uso', 'agotado')
      );
END$$
DELIMITER ;

-- ============================================================================
-- PASO 5: ACTUALIZAR VISTAS
-- ============================================================================

-- Vista: Resumen de producción por cubo (nueva)
CREATE OR REPLACE VIEW `v_resumen_cubos` AS
SELECT 
    c.id_cubo,
    c.numero_cubo,
    l.id_lote,
    l.codigo_lote,
    l.fecha_compra,
    p.nombre AS proveedor,
    c.peso_neto,
    c.cantidad_estimada_bolsas,
    c.cantidad_producida_real,
    c.estado,
    ROUND((c.cantidad_producida_real / NULLIF(c.cantidad_estimada_bolsas, 0)) * 100, 2) AS eficiencia_porcentual,
    CASE 
        WHEN c.cantidad_producida_real < (c.cantidad_estimada_bolsas * 0.95) AND c.estado = 'agotado' THEN 'SI'
        ELSE 'NO'
    END AS tiene_merma_excesiva
FROM cubos_fibra c
INNER JOIN lotes_fibra l ON c.id_lote = l.id_lote
INNER JOIN proveedores p ON l.id_proveedor = p.id_proveedor
WHERE c.cantidad_producida_real > 0;

-- Actualizar vista de cubos detallados
CREATE OR REPLACE VIEW `v_cubos_detallados` AS
SELECT 
    c.id_cubo,
    c.id_lote,
    l.codigo_lote,
    l.fecha_compra,
    p.nombre AS proveedor,
    c.numero_cubo,
    c.peso_bruto,
    c.peso_neto,
    c.cantidad_estimada_bolsas,
    c.rendimiento_estimado,
    c.cantidad_producida_real,
    c.estado,
    ROUND((c.cantidad_producida_real / NULLIF(c.cantidad_estimada_bolsas, 0)) * 100, 2) AS eficiencia_porcentual
FROM cubos_fibra c
INNER JOIN lotes_fibra l ON c.id_lote = l.id_lote
INNER JOIN proveedores p ON l.id_proveedor = p.id_proveedor
ORDER BY c.id_cubo;

-- Actualizar vista de inventario actual
CREATE OR REPLACE VIEW `v_inventario_actual` AS
SELECT 
    'Cubos de Fibra' AS item,
    COUNT(*) AS cantidad,
    CONCAT(ROUND(SUM(peso_neto), 2), ' kg') AS detalle,
    'disponibles' AS estado
FROM cubos_fibra 
WHERE estado = 'disponible'
UNION ALL
SELECT 
    'Bolsas Plásticas' AS item,
    cantidad,
    CONCAT(ROUND(cantidad, 2), ' kg') AS detalle,
    'en stock' AS estado
FROM inventario 
WHERE tipo_item = 'bolsas_plasticas'
UNION ALL
SELECT 
    'Producto Terminado' AS item,
    cantidad,
    CONCAT(ROUND(cantidad, 2), ' unidades') AS detalle,
    'en stock' AS estado
FROM inventario 
WHERE tipo_item = 'producto_terminado';

-- Actualizar vista de resumen de lotes (ahora agrega datos de cubos)
CREATE OR REPLACE VIEW `v_resumen_lotes` AS
SELECT 
    l.id_lote,
    l.codigo_lote,
    l.fecha_compra,
    p.nombre AS proveedor,
    l.peso_neto,
    l.precio_total,
    COUNT(c.id_cubo) AS total_cubos,
    SUM(c.cantidad_estimada_bolsas) AS cantidad_estimada_bolsas,
    l.cantidad_producida_real,
    l.estado,
    ROUND((l.cantidad_producida_real / NULLIF(SUM(c.cantidad_estimada_bolsas), 0)) * 100, 2) AS eficiencia_porcentual,
    CASE 
        WHEN l.cantidad_producida_real < (SUM(c.cantidad_estimada_bolsas) * 0.95) AND l.estado = 'agotado' THEN 'SI'
        ELSE 'NO'
    END AS tiene_merma_excesiva
FROM lotes_fibra l
INNER JOIN proveedores p ON l.id_proveedor = p.id_proveedor
LEFT JOIN cubos_fibra c ON l.id_lote = c.id_lote
GROUP BY l.id_lote, l.codigo_lote, l.fecha_compra, p.nombre, l.peso_neto, 
         l.precio_total, l.cantidad_producida_real, l.estado;

-- ============================================================================
-- PASO 6: RE-CALCULAR ESTADOS DESPUÉS DE MIGRACIÓN
-- ============================================================================

-- Re-calcular cantidad_producida_real de lotes basado en suma de cubos
UPDATE `lotes_fibra` l
SET l.cantidad_producida_real = (
    SELECT COALESCE(SUM(c.cantidad_producida_real), 0)
    FROM cubos_fibra c
    WHERE c.id_lote = l.id_lote
);

-- Re-calcular estados de cubos
UPDATE `cubos_fibra` c
SET c.estado = CASE
    WHEN c.cantidad_producida_real >= c.cantidad_estimada_bolsas THEN 'agotado'
    WHEN c.cantidad_producida_real > 0 THEN 'en_uso'
    ELSE 'disponible'
END;

-- Re-calcular estados de lotes
UPDATE `lotes_fibra` l
SET l.estado = CASE
    WHEN (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote AND estado = 'disponible') = 0 
         AND (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote AND estado IN ('en_uso', 'agotado')) > 0 
    THEN 'agotado'
    WHEN (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote AND estado IN ('en_uso', 'agotado')) > 0
    THEN 'en_proceso'
    ELSE 'disponible'
END;

-- ============================================================================
-- VERIFICACIÓN FINAL
-- ============================================================================

SELECT 'Migración completada exitosamente' AS status;
SELECT '✅ Tabla cubos_fibra actualizada con columnas de estimación' AS resultado;
SELECT '✅ Tabla producciones ahora referencia cubos individuales' AS resultado;
SELECT '✅ Triggers actualizados para trabajar por cubo' AS resultado;
SELECT '✅ Vistas actualizadas con información de cubos' AS resultado;
SELECT '✅ Estados re-calculados' AS resultado;

-- Mostrar resumen de cubos
SELECT 
    estado,
    COUNT(*) AS cantidad_cubos,
    ROUND(SUM(peso_neto), 2) AS peso_total_kg,
    SUM(cantidad_estimada_bolsas) AS bolsas_estimadas,
    SUM(cantidad_producida_real) AS bolsas_producidas
FROM cubos_fibra
GROUP BY estado
ORDER BY estado;
