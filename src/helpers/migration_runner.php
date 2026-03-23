<?php
/**
 * Helper para Migraciones Automáticas
 * Ejecuta cambios de esquema necesarios si no existen.
 */

function reinstall_produccion_triggers() {
    $db = Database::getInstance()->getConnection();
    try {
        // 0. AUTO-REPARACIÓN DE TABLA KARDEX
        // Asegurar que existan las columnas requeridas por el trigger y los reportes
        $colsKardex = $db->query("SHOW COLUMNS FROM kardex")->fetchAll(PDO::FETCH_COLUMN);
        $missingCols = [];
        
        if (!in_array('documento_referencia', $colsKardex)) $missingCols[] = "ADD COLUMN documento_referencia VARCHAR(100) NULL AFTER unidad_medida";
        if (!in_array('id_calidad_napa', $colsKardex)) $missingCols[] = "ADD COLUMN id_calidad_napa INT UNSIGNED NULL AFTER tipo_item";
        if (!in_array('observaciones', $colsKardex)) $missingCols[] = "ADD COLUMN observaciones TEXT NULL";
        
        if (!empty($missingCols)) {
            $db->exec("ALTER TABLE kardex " . implode(', ', $missingCols));
        }

        // 1. Limpieza PROFUNDA: Buscar y eliminar TODOS los triggers de la tabla 'producciones'
        // Esto elimina triggers con nombres desconocidos que causan el error 1172
        $stmt = $db->prepare("SELECT TRIGGER_NAME FROM information_schema.TRIGGERS WHERE EVENT_OBJECT_TABLE = 'producciones' AND TRIGGER_SCHEMA = DATABASE()");
        $stmt->execute();
        $existingTriggers = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($existingTriggers as $trigger) {
            // Usar backticks por si el nombre tiene espacios o caracteres especiales
            $db->exec("DROP TRIGGER IF EXISTS `$trigger`");
        }
        
        $db->exec("
            CREATE TRIGGER trg_produccion_aprobada_inventario
            AFTER UPDATE ON producciones
            FOR EACH ROW
            BEGIN
                IF NEW.estado_validacion = 'aprobado' AND OLD.estado_validacion != 'aprobado' THEN
                    -- 1. Actualizar Inventario de Producto Terminado
                    -- Usamos UPDATE directo con LIMIT 1 para evitar error 1172
                    UPDATE inventario 
                    SET cantidad = cantidad + NEW.cantidad_producida,
                        fecha_ultima_actualizacion = NOW()
                    WHERE tipo_item = 'producto_terminado' 
                    AND (id_calidad_napa = NEW.id_calidad_napa OR (id_calidad_napa IS NULL AND NEW.id_calidad_napa IS NULL))
                    LIMIT 1;
                    
                    IF ROW_COUNT() = 0 THEN
                        INSERT INTO inventario (tipo_item, cantidad, unidad_medida, stock_minimo, id_calidad_napa, fecha_ultima_actualizacion)
                        VALUES ('producto_terminado', NEW.cantidad_producida, 'unidades', 1000, NEW.id_calidad_napa, NOW());
                    END IF;
                    
                    -- Registrar Kardex Entrada Producto
                    INSERT INTO kardex (tipo_movimiento, tipo_item, cantidad, unidad_medida, fecha_movimiento, documento_referencia, observaciones, id_calidad_napa)
                    VALUES ('entrada', 'producto_terminado', NEW.cantidad_producida, 'unidades', NOW(), CONCAT('PROD-', NEW.id_produccion), 'Producción aprobada', NEW.id_calidad_napa);
                    
                    -- 2. Actualizar Inventario de Bolsas (Salida)
                    IF NEW.peso_bolsas_consumido > 0 THEN
                        UPDATE inventario 
                        SET cantidad = cantidad - NEW.peso_bolsas_consumido,
                            fecha_ultima_actualizacion = NOW()
                        WHERE tipo_item = 'bolsas_plasticas'
                        LIMIT 1;
                        
                        -- Registrar Kardex Salida Bolsas
                        INSERT INTO kardex (tipo_movimiento, tipo_item, cantidad, unidad_medida, fecha_movimiento, documento_referencia, observaciones)
                        VALUES ('salida', 'bolsas_plasticas', NEW.peso_bolsas_consumido, 'kg', NOW(), CONCAT('PROD-', NEW.id_produccion), 'Consumo en producción');
                    END IF;
                END IF;
            END
        ");
    } catch (PDOException $e) {
        error_log("Trigger Fix Error: " . $e->getMessage());
    }
}

function reinstall_ventas_triggers() {
    $db = Database::getInstance()->getConnection();
    try {
        $db->exec("DROP TRIGGER IF EXISTS trg_after_insert_venta");
        
        $db->exec("
            CREATE TRIGGER trg_after_insert_venta
            AFTER INSERT ON ventas
            FOR EACH ROW
            BEGIN
                UPDATE inventario 
                SET cantidad = cantidad - NEW.cantidad_vendida,
                    fecha_ultima_actualizacion = NOW()
                WHERE tipo_item = 'producto_terminado' 
                AND (id_calidad_napa = NEW.id_calidad_napa OR (id_calidad_napa IS NULL AND NEW.id_calidad_napa IS NULL))
                LIMIT 1;
                
                INSERT INTO kardex (tipo_movimiento, tipo_item, id_calidad_napa, cantidad, unidad_medida, fecha_movimiento, documento_referencia, observaciones)
                VALUES ('salida', 'producto_terminado', NEW.id_calidad_napa, NEW.cantidad_vendida, 'unidades', NOW(), CONCAT('VENTA-', NEW.id_venta), CONCAT('Venta registrada. Cliente ID: ', NEW.id_cliente));
            END
        ");
    } catch (PDOException $e) {
        error_log("Ventas Trigger Fix Error: " . $e->getMessage());
    }
}

function update_schema_gastos() {
    $db = Database::getInstance()->getConnection();
    try {
        // 1. Agregar columna sueldo a choferes
        $cols = $db->query("SHOW COLUMNS FROM choferes")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('sueldo', $cols)) {
            $db->exec("ALTER TABLE choferes ADD COLUMN sueldo DECIMAL(10,2) DEFAULT 0.00 AFTER telefono");
        }

        // 2. Crear tabla gastos_operativos
        $db->exec("
            CREATE TABLE IF NOT EXISTS `gastos_operativos` (
              `id_gasto` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `fecha_gasto` DATE NOT NULL,
              `categoria` VARCHAR(50) NOT NULL,
              `descripcion` VARCHAR(255) NULL,
              `monto` DECIMAL(10,2) NOT NULL,
              `usuario_creacion` INT UNSIGNED NULL,
              `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id_gasto`),
              INDEX `idx_fecha_gasto` (`fecha_gasto`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    } catch (PDOException $e) {
        error_log("Gastos Schema Update Error: " . $e->getMessage());
    }
}

function update_schema_otros_ingresos() {
    $db = Database::getInstance()->getConnection();
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS `otros_ingresos` (
              `id_ingreso` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `fecha_ingreso` DATE NOT NULL,
              `categoria` VARCHAR(100) NOT NULL,
              `descripcion` TEXT NULL,
              `monto` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
              `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `usuario_creacion` INT UNSIGNED NULL,
              PRIMARY KEY (`id_ingreso`),
              INDEX `idx_fecha` (`fecha_ingreso`),
              INDEX `idx_categoria` (`categoria`),
              CONSTRAINT `fk_ingreso_usuario`
                FOREIGN KEY (`usuario_creacion`)
                REFERENCES `usuarios` (`id_usuario`)
                ON DELETE SET NULL
                ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Registro de otros ingresos que suman a la utilidad';
        ");
    } catch (PDOException $e) {
        error_log("Otros Ingresos Schema Update Error: " . $e->getMessage());
    }
}

function run_auto_migrations() {
    $db = Database::getInstance()->getConnection();
    
    // 0. CRITICAL FIX: Trigger Producción (Error 1172)
    reinstall_produccion_triggers();
    reinstall_ventas_triggers();
    update_schema_gastos();
    update_schema_otros_ingresos();

    try {
        // 1. Verificar y crear tabla calidades_fibra si no existe
        $db->exec("
            CREATE TABLE IF NOT EXISTS `calidades_fibra` (
              `id_calidad_fibra` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `nombre` VARCHAR(100) NOT NULL COMMENT 'Ej: Virgen, Cristalizada, Reciclada, etc.',
              `descripcion` TEXT NULL,
              `color` VARCHAR(20) NULL COMMENT 'Color badge en UI (success/warning/info)',
              `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
              `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `usuario_creacion` INT UNSIGNED NULL,
              PRIMARY KEY (`id_calidad_fibra`),
              UNIQUE INDEX `nombre_UNIQUE` (`nombre` ASC)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Insertar datos iniciales si está vacía
        $count = $db->query("SELECT COUNT(*) FROM calidades_fibra")->fetchColumn();
        if ($count == 0) {
            $db->exec("
                INSERT INTO calidades_fibra (nombre, descripcion, color, estado) VALUES
                ('Fibra Virgen', 'Fibra de primera calidad, sin procesar previamente', 'success', 'activo'),
                ('Fibra Cristalizada', 'Fibra procesada con características especiales', 'info', 'activo'),
                ('Fibra Reciclada Premium', 'Fibra reciclada de alta calidad', 'warning', 'activo'),
                ('Fibra Estándar', 'Fibra de calidad estándar para uso general', 'secondary', 'activo');
            ");
        }

        // 2. Verificar y agregar columnas a lotes_fibra
        $cols = $db->query("SHOW COLUMNS FROM lotes_fibra")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('numero_cubos', $cols)) {
            $db->exec("ALTER TABLE lotes_fibra ADD COLUMN numero_cubos INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Cantidad de fardos/cubos' AFTER id_proveedor");
        }
        
        if (!in_array('numero_guia', $cols)) {
            $db->exec("ALTER TABLE lotes_fibra ADD COLUMN numero_guia VARCHAR(50) NULL COMMENT 'Número de guía de remisión' AFTER numero_cubos");
        }
        
        if (!in_array('id_calidad_fibra', $cols)) {
             $db->exec("ALTER TABLE lotes_fibra ADD COLUMN id_calidad_fibra INT UNSIGNED NULL AFTER id_proveedor");
             // Intentar agregar FK (puede fallar si hay datos inconsistentes, por eso el try/catch silencioso en prod)
             try {
                $db->exec("ALTER TABLE lotes_fibra ADD CONSTRAINT fk_lote_calidad_fibra FOREIGN KEY (id_calidad_fibra) REFERENCES calidades_fibra(id_calidad_fibra) ON DELETE SET NULL ON UPDATE CASCADE");
             } catch (Exception $e) {}
        }

        // 3. Crear tabla cubos_fibra
        $db->exec("
            CREATE TABLE IF NOT EXISTS `cubos_fibra` (
              `id_cubo` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `id_lote` INT UNSIGNED NOT NULL,
              `numero_cubo` INT UNSIGNED NOT NULL COMMENT 'Número secuencial dentro del lote',
              `peso_bruto` DECIMAL(10,2) NOT NULL COMMENT 'Peso bruto individual del cubo',
              `peso_neto` DECIMAL(10,2) NOT NULL COMMENT 'Peso neto individual del cubo',
              `cantidad_estimada_bolsas` INT UNSIGNED NULL COMMENT 'Bolsas estimadas de este cubo',
              `estado` ENUM('disponible', 'en_uso', 'agotado') NOT NULL DEFAULT 'disponible',
              `cantidad_producida_real` INT UNSIGNED DEFAULT 0,
              `fecha_uso` DATETIME NULL,
              `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id_cubo`),
              INDEX `fk_cubo_lote_idx` (`id_lote` ASC),
              CONSTRAINT `fk_cubo_lote`
                FOREIGN KEY (`id_lote`)
                REFERENCES `lotes_fibra` (`id_lote`)
                ON DELETE CASCADE
                ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Asegurar que existan las columnas de seguimiento en cubos_fibra
        $colsCubos = $db->query("SHOW COLUMNS FROM cubos_fibra")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('cantidad_producida_real', $colsCubos)) {
            $db->exec("ALTER TABLE cubos_fibra ADD COLUMN cantidad_producida_real INT UNSIGNED DEFAULT 0 AFTER estado");
        }
        if (!in_array('fecha_uso', $colsCubos)) {
            $db->exec("ALTER TABLE cubos_fibra ADD COLUMN fecha_uso DATETIME NULL AFTER cantidad_producida_real");
        }

        // 3.5 Verificar y agregar columna observaciones_validacion a producciones
        $colsProd = $db->query("SHOW COLUMNS FROM producciones")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('observaciones_validacion', $colsProd)) {
            $db->exec("ALTER TABLE producciones ADD COLUMN observaciones_validacion TEXT NULL AFTER observaciones");
        }

        // 4. Corregir Stored Procedure de generación de código (Fix para Duplicate Entry)
        // Se recrea siempre para asegurar que tenga la lógica correcta (substring 14)
        $db->exec("DROP PROCEDURE IF EXISTS sp_generar_codigo_lote");
        $db->exec("
            CREATE PROCEDURE `sp_generar_codigo_lote`(
                OUT p_codigo_lote VARCHAR(50)
            )
            BEGIN
                DECLARE v_anio CHAR(4);
                DECLARE v_mes CHAR(2);
                DECLARE v_secuencia INT;
                
                SET v_anio = YEAR(CURDATE());
                SET v_mes = LPAD(MONTH(CURDATE()), 2, '0');
                
                -- Corregido: SUBSTRING debe empezar en 14 para saltar 'LOTE-YYYY-MM-' (13 chars)
                SELECT COALESCE(MAX(CAST(SUBSTRING(codigo_lote, 14, 4) AS UNSIGNED)), 0) + 1 
                INTO v_secuencia
                FROM lotes_fibra
                WHERE codigo_lote LIKE CONCAT('LOTE-', v_anio, '-', v_mes, '-%');
                
                SET p_codigo_lote = CONCAT('LOTE-', v_anio, '-', v_mes, '-', LPAD(v_secuencia, 4, '0'));
            END
        ");

    } catch (PDOException $e) {
        // Loguear error pero no detener la app si es posible
        error_log("Migration Runner Error: " . $e->getMessage());
    }
}

// Ejecutar migración
run_auto_migrations();
