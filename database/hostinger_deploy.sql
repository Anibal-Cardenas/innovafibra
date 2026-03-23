    -- ============================================================================
    -- BASE DE DATOS SISTEMA NAPA - VERSIÓN PRODUCCIÓN (HOSTINGER)
    -- Fecha Generación: 2026-01-15
    -- Incluye: Roles, Comisiones, Calidades, Cubos, Choferes y Correcciones
    -- ============================================================================

    SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
    SET time_zone = "+00:00";
    SET NAMES utf8mb4;

    -- --------------------------------------------------------
    -- 1. ESTRUCTURA DE TABLAS
    -- --------------------------------------------------------

    -- Tabla: usuarios
    CREATE TABLE `usuarios` (
    `id_usuario` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `nombre_completo` VARCHAR(150) NOT NULL,
    `dni` VARCHAR(20) NULL,
    `email` VARCHAR(100) NULL,
    `rol` ENUM('administrador', 'operador', 'vendedor', 'trabajador', 'supervisor') NOT NULL DEFAULT 'operador',
    `tarifa_por_bolsa` DECIMAL(10,2) NULL DEFAULT 0.00,
    `fecha_ingreso` DATE NULL,
    `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_modificacion` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `usuario_creacion` INT UNSIGNED NULL,
    PRIMARY KEY (`id_usuario`),
    UNIQUE INDEX `username_UNIQUE` (`username` ASC)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: calidades_napa (Producto Terminado)
    CREATE TABLE `calidades_napa` (
    `id_calidad_napa` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `codigo` VARCHAR(20) NOT NULL,
    `nombre` VARCHAR(100) NOT NULL,
    `descripcion` TEXT NULL,
    `precio_base_sugerido` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `usuario_creacion` INT UNSIGNED NULL,
    PRIMARY KEY (`id_calidad_napa`),
    UNIQUE INDEX `codigo_UNIQUE` (`codigo` ASC)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: calidades_fibra (Materia Prima)
    CREATE TABLE `calidades_fibra` (
    `id_calidad_fibra` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `descripcion` TEXT NULL,
    `factor_precio` DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    `color` VARCHAR(20) NULL COMMENT 'success, warning, info, secondary',
    `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    `id_calidad_napa_destino` INT UNSIGNED NULL COMMENT 'Calidad de napa que produce',
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `usuario_creacion` INT UNSIGNED NULL,
    PRIMARY KEY (`id_calidad_fibra`),
    UNIQUE INDEX `nombre_UNIQUE` (`nombre` ASC),
    CONSTRAINT `fk_calidad_fibra_napa` FOREIGN KEY (`id_calidad_napa_destino`) REFERENCES `calidades_napa` (`id_calidad_napa`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: proveedores
    CREATE TABLE `proveedores` (
    `id_proveedor` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(200) NOT NULL,
    `ruc` VARCHAR(20) NULL,
    `direccion` VARCHAR(255) NULL,
    `telefono` VARCHAR(20) NULL,
    `email` VARCHAR(100) NULL,
    `contacto_principal` VARCHAR(150) NULL,
    `tipo_proveedor` ENUM('fibra', 'bolsas', 'otros') NOT NULL,
    `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_proveedor`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: lotes_fibra
    CREATE TABLE `lotes_fibra` (
    `id_lote` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `codigo_lote` VARCHAR(50) NOT NULL,
    `fecha_compra` DATE NOT NULL,
    `id_proveedor` INT UNSIGNED NOT NULL,
    `id_calidad_fibra` INT UNSIGNED NULL,
    `numero_cubos` INT UNSIGNED NOT NULL DEFAULT 1,
    `numero_guia` VARCHAR(50) NULL,
    `peso_bruto` DECIMAL(10,2) NOT NULL,
    `peso_neto` DECIMAL(10,2) NOT NULL,
    `precio_total` DECIMAL(12,2) NOT NULL,
    `precio_por_kg` DECIMAL(10,2) NOT NULL,
    `cantidad_estimada_bolsas` INT UNSIGNED NOT NULL DEFAULT 70,
    `rendimiento_estimado` DECIMAL(10,4) NOT NULL,
    `cantidad_producida_real` INT UNSIGNED NOT NULL DEFAULT 0,
    `estado` ENUM('disponible', 'en_proceso', 'agotado', 'merma_excesiva') NOT NULL DEFAULT 'disponible',
    `observaciones` TEXT NULL,
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `usuario_creacion` INT UNSIGNED NULL,
    PRIMARY KEY (`id_lote`),
    UNIQUE INDEX `codigo_lote_UNIQUE` (`codigo_lote` ASC),
    CONSTRAINT `fk_lote_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`),
    CONSTRAINT `fk_lote_calidad` FOREIGN KEY (`id_calidad_fibra`) REFERENCES `calidades_fibra` (`id_calidad_fibra`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: cubos_fibra
    CREATE TABLE `cubos_fibra` (
    `id_cubo` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_lote` INT UNSIGNED NOT NULL,
    `numero_cubo` INT UNSIGNED NOT NULL,
    `peso_bruto` DECIMAL(10,2) NOT NULL,
    `peso_neto` DECIMAL(10,2) NOT NULL,
    `cantidad_estimada_bolsas` INT UNSIGNED NULL,
    `rendimiento_estimado` DECIMAL(10,4) NULL,
    `cantidad_producida_real` INT UNSIGNED DEFAULT 0,
    `estado` ENUM('disponible', 'en_uso', 'agotado') NOT NULL DEFAULT 'disponible',
    `fecha_uso` DATETIME NULL,
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_cubo`),
    CONSTRAINT `fk_cubo_lote` FOREIGN KEY (`id_lote`) REFERENCES `lotes_fibra` (`id_lote`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: compras_bolsas
    CREATE TABLE `compras_bolsas` (
    `id_compra_bolsa` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `fecha_compra` DATE NOT NULL,
    `id_proveedor` INT UNSIGNED NOT NULL,
    `peso_kg` DECIMAL(10,2) NOT NULL,
    `precio_total` DECIMAL(12,2) NOT NULL,
    `precio_por_kg` DECIMAL(10,2) NOT NULL,
    `tipo_bolsa` VARCHAR(100) NULL,
    `observaciones` TEXT NULL,
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `usuario_creacion` INT UNSIGNED NULL,
    PRIMARY KEY (`id_compra_bolsa`),
    CONSTRAINT `fk_compra_bolsa_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: comisiones
    CREATE TABLE `comisiones` (
    `id_comision` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_operario` INT UNSIGNED NOT NULL,
    `fecha_inicio` DATE NOT NULL,
    `fecha_fin` DATE NOT NULL,
    `total_bolsas_producidas` INT UNSIGNED NOT NULL DEFAULT 0,
    `tarifa_aplicada` DECIMAL(10,2) NOT NULL,
    `monto_comision` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `monto_bonificacion` DECIMAL(12,2) NULL DEFAULT 0.00,
    `monto_descuento` DECIMAL(12,2) NULL DEFAULT 0.00,
    `monto_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `estado` ENUM('pendiente', 'calculado', 'pagado', 'anulado') NOT NULL DEFAULT 'pendiente',
    `fecha_pago` DATE NULL,
    `metodo_pago` ENUM('efectivo', 'transferencia', 'cheque') NULL,
    `numero_operacion` VARCHAR(100) NULL,
    `observaciones` TEXT NULL,
    `fecha_calculo` TIMESTAMP NULL,
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `usuario_creo` INT UNSIGNED NULL,
    `usuario_pago` INT UNSIGNED NULL,
    PRIMARY KEY (`id_comision`),
    UNIQUE INDEX `unique_operario_periodo` (`id_operario`, `fecha_inicio`, `fecha_fin`),
    CONSTRAINT `fk_comision_operario` FOREIGN KEY (`id_operario`) REFERENCES `usuarios` (`id_usuario`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: producciones
    CREATE TABLE `producciones` (
    `id_produccion` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `fecha_produccion` DATE NOT NULL,
    `id_lote_fibra` INT UNSIGNED NOT NULL,
    `id_cubo` INT UNSIGNED NOT NULL,
    `id_operario` INT UNSIGNED NOT NULL,
    `id_calidad_napa` INT UNSIGNED NULL,
    `cantidad_producida` INT UNSIGNED NOT NULL,
    `peso_bolsas_consumido` DECIMAL(10,2) NOT NULL,
    `eficiencia_porcentual` DECIMAL(5,2) NULL,
    `flag_merma_excesiva` BOOLEAN NOT NULL DEFAULT FALSE,
    `estado_validacion` ENUM('pendiente', 'aprobado', 'rechazado') NOT NULL DEFAULT 'pendiente',
    `id_supervisor` INT UNSIGNED NULL,
    `fecha_validacion` DATETIME NULL,
    `observaciones_validacion` TEXT NULL,
    `observaciones` TEXT NULL,
    `id_comision` INT UNSIGNED NULL,
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `usuario_creacion` INT UNSIGNED NULL,
    PRIMARY KEY (`id_produccion`),
    CONSTRAINT `fk_prod_lote` FOREIGN KEY (`id_lote_fibra`) REFERENCES `lotes_fibra` (`id_lote`),
    CONSTRAINT `fk_prod_cubo` FOREIGN KEY (`id_cubo`) REFERENCES `cubos_fibra` (`id_cubo`),
    CONSTRAINT `fk_prod_operario` FOREIGN KEY (`id_operario`) REFERENCES `usuarios` (`id_usuario`),
    CONSTRAINT `fk_prod_calidad` FOREIGN KEY (`id_calidad_napa`) REFERENCES `calidades_napa` (`id_calidad_napa`),
    CONSTRAINT `fk_prod_comision` FOREIGN KEY (`id_comision`) REFERENCES `comisiones` (`id_comision`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: comisiones_detalle
    CREATE TABLE `comisiones_detalle` (
    `id_comision_detalle` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_comision` INT UNSIGNED NOT NULL,
    `id_produccion` INT UNSIGNED NOT NULL,
    `fecha_produccion` DATE NOT NULL,
    `cantidad_bolsas` INT UNSIGNED NOT NULL,
    `tarifa_por_bolsa` DECIMAL(10,2) NOT NULL,
    `subtotal` DECIMAL(12,2) NOT NULL,
    `incluido_en_calculo` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (`id_comision_detalle`),
    UNIQUE INDEX `unique_comision_produccion` (`id_comision`, `id_produccion`),
    CONSTRAINT `fk_detalle_comision` FOREIGN KEY (`id_comision`) REFERENCES `comisiones` (`id_comision`) ON DELETE CASCADE,
    CONSTRAINT `fk_detalle_produccion` FOREIGN KEY (`id_produccion`) REFERENCES `producciones` (`id_produccion`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: clientes
    CREATE TABLE `clientes` (
    `id_cliente` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(200) NOT NULL,
    `ruc` VARCHAR(20) NULL,
    `direccion` VARCHAR(255) NULL,
    `telefono` VARCHAR(20) NULL,
    `email` VARCHAR(100) NULL,
    `contacto_principal` VARCHAR(150) NULL,
    `limite_credito` DECIMAL(12,2) NULL DEFAULT 0.00,
    `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_cliente`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: ventas
    CREATE TABLE `ventas` (
    `id_venta` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `fecha_venta` DATE NOT NULL,
    `id_cliente` INT UNSIGNED NOT NULL,
    `id_calidad_napa` INT UNSIGNED NULL,
    `cantidad_vendida` INT UNSIGNED NOT NULL,
    `precio_unitario` DECIMAL(10,2) NOT NULL,
    `precio_total` DECIMAL(12,2) NOT NULL,
    `costo_unitario_referencia` DECIMAL(10,2) NULL,
    `margen_porcentual` DECIMAL(5,2) NULL,
    `forma_pago` ENUM('efectivo', 'transferencia', 'cheque', 'credito') NOT NULL DEFAULT 'efectivo',
    `estado_pago` ENUM('pendiente', 'pagado', 'credito', 'cancelado') NOT NULL DEFAULT 'pendiente',
    `estado_entrega` ENUM('pendiente', 'entregado') NOT NULL DEFAULT 'pendiente',
    `fecha_pago` DATE NULL,
    `observaciones` TEXT NULL,
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `usuario_creacion` INT UNSIGNED NULL,
    PRIMARY KEY (`id_venta`),
    CONSTRAINT `fk_venta_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
    CONSTRAINT `fk_venta_calidad` FOREIGN KEY (`id_calidad_napa`) REFERENCES `calidades_napa` (`id_calidad_napa`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: choferes
    CREATE TABLE `choferes` (
    `id_chofer` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre_completo` VARCHAR(150) NOT NULL,
    `dni` VARCHAR(20) NULL,
    `licencia` VARCHAR(50) NULL,
    `telefono` VARCHAR(20) NULL,
    `vehiculo` VARCHAR(100) NULL,
    `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_chofer`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: entregas
    CREATE TABLE `entregas` (
    `id_entrega` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `codigo_guia` VARCHAR(50) NOT NULL,
    `id_venta` INT UNSIGNED NOT NULL,
    `id_chofer` INT UNSIGNED NOT NULL,
    `fecha_entrega` DATE NOT NULL,
    `fecha_entrega_estimada` DATE NULL,
    `estado_entrega` ENUM('pendiente', 'en_ruta', 'entregado', 'cancelado') NOT NULL DEFAULT 'pendiente',
    `hora_salida` TIME NULL,
    `hora_llegada` TIME NULL,
    `direccion_entrega` VARCHAR(255) NULL,
    `nombre_receptor` VARCHAR(150) NULL,
    `dni_receptor` VARCHAR(20) NULL,
    `firma_recibido` BOOLEAN NOT NULL DEFAULT FALSE,
    `observaciones` TEXT NULL,
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `usuario_creacion` INT UNSIGNED NULL,
    PRIMARY KEY (`id_entrega`),
    UNIQUE INDEX `codigo_guia_UNIQUE` (`codigo_guia` ASC),
    CONSTRAINT `fk_entrega_venta` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`),
    CONSTRAINT `fk_entrega_chofer` FOREIGN KEY (`id_chofer`) REFERENCES `choferes` (`id_chofer`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: inventario
    CREATE TABLE `inventario` (
    `id_inventario` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tipo_item` ENUM('fibra', 'bolsas_plasticas', 'producto_terminado') NOT NULL,
    `id_calidad_napa` INT UNSIGNED NULL,
    `cantidad` DECIMAL(12,2) NOT NULL,
    `unidad_medida` VARCHAR(20) NOT NULL,
    `stock_minimo` DECIMAL(12,2) NULL DEFAULT 0,
    `fecha_ultima_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_inventario`),
    UNIQUE INDEX `idx_tipo_calidad` (`tipo_item`, `id_calidad_napa`),
    CONSTRAINT `fk_inventario_calidad` FOREIGN KEY (`id_calidad_napa`) REFERENCES `calidades_napa` (`id_calidad_napa`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: kardex
    CREATE TABLE `kardex` (
    `id_kardex` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `fecha_movimiento` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `tipo_item` ENUM('fibra', 'bolsas_plasticas', 'producto_terminado') NOT NULL,
    `id_calidad_napa` INT UNSIGNED NULL,
    `tipo_movimiento` ENUM('entrada', 'salida', 'ajuste', 'merma') NOT NULL,
    `cantidad` DECIMAL(12,2) NOT NULL,
    `unidad_medida` VARCHAR(20) NOT NULL,
    `saldo_anterior` DECIMAL(12,2) NOT NULL,
    `saldo_nuevo` DECIMAL(12,2) NOT NULL,
    `referencia_tipo` VARCHAR(50) NULL,
    `referencia_id` INT UNSIGNED NULL,
    `documento_referencia` VARCHAR(100) NULL,
    `observaciones` TEXT NULL,
    `usuario_registro` INT UNSIGNED NULL,
    PRIMARY KEY (`id_kardex`),
    CONSTRAINT `fk_kardex_calidad` FOREIGN KEY (`id_calidad_napa`) REFERENCES `calidades_napa` (`id_calidad_napa`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: configuracion_sistema
    CREATE TABLE `configuracion_sistema` (
    `id_config` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parametro` VARCHAR(100) NOT NULL,
    `valor` VARCHAR(255) NOT NULL,
    `tipo_dato` ENUM('entero', 'decimal', 'texto', 'boolean') NOT NULL,
    `descripcion` TEXT NULL,
    `fecha_modificacion` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `usuario_modificacion` INT UNSIGNED NULL,
    PRIMARY KEY (`id_config`),
    UNIQUE INDEX `parametro_UNIQUE` (`parametro` ASC)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: historial_configuracion
    CREATE TABLE `historial_configuracion` (
    `id_historial_config` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parametro` VARCHAR(100) NOT NULL,
    `valor_anterior` VARCHAR(255) NOT NULL,
    `valor_nuevo` VARCHAR(255) NOT NULL,
    `fecha_cambio` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `usuario_cambio` INT UNSIGNED NULL,
    `motivo` VARCHAR(255) NULL,
    PRIMARY KEY (`id_historial_config`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: historial_tarifas
    CREATE TABLE `historial_tarifas` (
    `id_historial_tarifa` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_usuario` INT UNSIGNED NOT NULL,
    `tarifa_anterior` DECIMAL(10,2) NOT NULL,
    `tarifa_nueva` DECIMAL(10,2) NOT NULL,
    `fecha_cambio` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `usuario_autorizo` INT UNSIGNED NULL,
    `motivo` VARCHAR(255) NULL,
    PRIMARY KEY (`id_historial_tarifa`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Tabla: auditoria
    CREATE TABLE `auditoria` (
    `id_auditoria` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_usuario` INT UNSIGNED NULL,
    `tabla_afectada` VARCHAR(100) NOT NULL,
    `id_registro` INT UNSIGNED NULL,
    `accion` ENUM('INSERT', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT') NOT NULL,
    `descripcion` TEXT NULL,
    `datos_anteriores` JSON NULL,
    `datos_nuevos` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `fecha_accion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_auditoria`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- --------------------------------------------------------
    -- 2. VISTAS
    -- --------------------------------------------------------

    CREATE OR REPLACE VIEW `v_stock_ventas` AS
    SELECT 
        cn.id_calidad_napa as id_calidad,
        cn.codigo,
        cn.nombre as calidad_napa,
        cn.precio_base_sugerido as precio,
        COALESCE(i.cantidad, 0) as stock_disponible,
        i.unidad_medida,
        CASE 
            WHEN COALESCE(i.cantidad, 0) = 0 THEN 'Sin stock'
            WHEN i.cantidad <= i.stock_minimo THEN 'Stock bajo'
            ELSE 'Disponible'
        END as estado_stock
    FROM calidades_napa cn
    LEFT JOIN inventario i ON i.tipo_item = 'producto_terminado' AND i.id_calidad_napa = cn.id_calidad_napa
    WHERE cn.estado = 'activo'
    ORDER BY cn.codigo;

    CREATE OR REPLACE VIEW `v_inventario_por_calidad` AS
    SELECT 
        i.tipo_item,
        CASE 
            WHEN i.tipo_item = 'producto_terminado' THEN cn.nombre
            ELSE 'N/A'
        END AS calidad,
        i.id_calidad_napa,
        i.cantidad,
        i.unidad_medida,
        i.stock_minimo,
        CASE
            WHEN i.cantidad <= i.stock_minimo THEN 'Bajo'
            WHEN i.cantidad <= (i.stock_minimo * 1.5) THEN 'Medio'
            ELSE 'Normal'
        END AS nivel_stock,
        i.fecha_ultima_actualizacion
    FROM inventario i
    LEFT JOIN calidades_napa cn ON i.id_calidad_napa = cn.id_calidad_napa
    ORDER BY i.tipo_item, cn.codigo;

    CREATE OR REPLACE VIEW `v_resumen_lotes` AS
    SELECT 
        l.id_lote,
        l.codigo_lote,
        l.fecha_compra,
        p.nombre AS proveedor,
        l.peso_neto,
        l.precio_total,
        l.cantidad_estimada_bolsas,
        l.cantidad_producida_real,
        l.estado,
        ROUND((l.cantidad_producida_real / NULLIF(l.cantidad_estimada_bolsas, 0)) * 100, 2) AS eficiencia_porcentual,
        CASE 
            WHEN l.cantidad_producida_real < (l.cantidad_estimada_bolsas * 0.95) AND l.estado = 'agotado' THEN 'SI'
            ELSE 'NO'
        END AS tiene_merma_excesiva
    FROM lotes_fibra l
    INNER JOIN proveedores p ON l.id_proveedor = p.id_proveedor;

    CREATE OR REPLACE VIEW `v_comisiones_pendientes` AS
    SELECT 
        c.id_comision,
        u.username,
        u.nombre_completo AS operario,
        c.fecha_inicio,
        c.fecha_fin,
        c.total_bolsas_producidas,
        c.tarifa_aplicada,
        c.monto_comision,
        c.monto_bonificacion,
        c.monto_descuento,
        c.monto_total,
        c.estado,
        c.fecha_calculo,
        DATEDIFF(CURDATE(), c.fecha_fin) AS dias_pendiente
    FROM comisiones c
    INNER JOIN usuarios u ON c.id_operario = u.id_usuario
    WHERE c.estado IN ('pendiente', 'calculado')
    ORDER BY c.fecha_fin DESC;

    CREATE OR REPLACE VIEW `v_historial_comisiones` AS
    SELECT 
        c.id_comision,
        u.username,
        u.nombre_completo AS operario,
        c.fecha_inicio,
        c.fecha_fin,
        c.total_bolsas_producidas,
        c.tarifa_aplicada,
        c.monto_total,
        c.estado,
        c.fecha_pago,
        c.metodo_pago,
        c.numero_operacion,
        admin.nombre_completo AS pagado_por
    FROM comisiones c
    INNER JOIN usuarios u ON c.id_operario = u.id_usuario
    LEFT JOIN usuarios admin ON c.usuario_pago = admin.id_usuario
    WHERE c.estado = 'pagado'
    ORDER BY c.fecha_pago DESC;

    CREATE OR REPLACE VIEW `v_produccion_operador` AS
    SELECT 
        u.id_usuario AS id_operario,
        u.username,
        u.nombre_completo AS operario,
        u.tarifa_por_bolsa,
        DATE(pr.fecha_produccion) AS fecha,
        COUNT(pr.id_produccion) AS total_producciones,
        SUM(CASE WHEN pr.estado_validacion = 'aprobado' THEN pr.cantidad_producida ELSE 0 END) AS bolsas_aprobadas,
        SUM(CASE WHEN pr.estado_validacion = 'rechazado' THEN pr.cantidad_producida ELSE 0 END) AS bolsas_rechazadas,
        SUM(CASE WHEN pr.estado_validacion = 'pendiente' THEN pr.cantidad_producida ELSE 0 END) AS bolsas_pendientes,
        SUM(CASE WHEN pr.estado_validacion = 'aprobado' THEN pr.cantidad_producida * u.tarifa_por_bolsa ELSE 0 END) AS monto_comision_dia,
        pr.id_comision
    FROM usuarios u
    INNER JOIN producciones pr ON u.id_usuario = pr.id_operario
    WHERE u.rol IN ('operador', 'trabajador')
    GROUP BY u.id_usuario, u.username, u.nombre_completo, u.tarifa_por_bolsa, DATE(pr.fecha_produccion), pr.id_comision;

    CREATE OR REPLACE VIEW `v_estado_inventario` AS
    SELECT 
        tipo_item,
        cantidad,
        unidad_medida,
        stock_minimo,
        CASE 
            WHEN cantidad < stock_minimo * 0.5 THEN 'CRÍTICO'
            WHEN cantidad < stock_minimo THEN 'BAJO'
            ELSE 'NORMAL'
        END AS estado_alerta,
        fecha_ultima_actualizacion
    FROM inventario;

    -- --------------------------------------------------------
    -- 3. PROCEDIMIENTOS ALMACENADOS
    -- --------------------------------------------------------

    DELIMITER $$

    CREATE PROCEDURE `sp_generar_codigo_lote`(OUT p_codigo_lote VARCHAR(50))
    BEGIN
        DECLARE v_anio CHAR(4);
        DECLARE v_mes CHAR(2);
        DECLARE v_secuencia INT;
        SET v_anio = YEAR(CURDATE());
        SET v_mes = LPAD(MONTH(CURDATE()), 2, '0');
        SELECT COALESCE(MAX(CAST(SUBSTRING(codigo_lote, 14, 4) AS UNSIGNED)), 0) + 1 
        INTO v_secuencia
        FROM lotes_fibra
        WHERE codigo_lote LIKE CONCAT('LOTE-', v_anio, '-', v_mes, '-%');
        SET p_codigo_lote = CONCAT('LOTE-', v_anio, '-', v_mes, '-', LPAD(v_secuencia, 4, '0'));
    END$$

    CREATE PROCEDURE `sp_generar_codigo_guia`(OUT p_codigo_guia VARCHAR(50))
    BEGIN
        DECLARE v_anio CHAR(4);
        DECLARE v_secuencia INT;
        SET v_anio = YEAR(CURDATE());
        SELECT COALESCE(MAX(CAST(SUBSTRING(codigo_guia, 11, 4) AS UNSIGNED)), 0) + 1 
        INTO v_secuencia
        FROM entregas
        WHERE codigo_guia LIKE CONCAT('GUIA-', v_anio, '-%');
        SET p_codigo_guia = CONCAT('GUIA-', v_anio, '-', LPAD(v_secuencia, 4, '0'));
    END$$

    CREATE PROCEDURE `sp_calcular_comision_operario`(
        IN p_id_operario INT UNSIGNED,
        IN p_fecha_inicio DATE,
        IN p_fecha_fin DATE,
        IN p_id_usuario_admin INT UNSIGNED,
        OUT p_id_comision INT UNSIGNED,
        OUT p_mensaje VARCHAR(255)
    )
    BEGIN
        DECLARE v_total_bolsas INT UNSIGNED DEFAULT 0;
        DECLARE v_tarifa DECIMAL(10,2) DEFAULT 0.00;
        DECLARE v_monto_comision DECIMAL(12,2) DEFAULT 0.00;
        DECLARE v_existe_comision INT DEFAULT 0;
        
        SELECT COUNT(*) INTO v_existe_comision
        FROM comisiones
        WHERE id_operario = p_id_operario
        AND fecha_inicio = p_fecha_inicio
        AND fecha_fin = p_fecha_fin
        AND estado != 'anulado';
        
        IF v_existe_comision > 0 THEN
            SET p_mensaje = 'Ya existe una comisión calculada para este periodo';
            SET p_id_comision = NULL;
        ELSE
            SELECT tarifa_por_bolsa INTO v_tarifa FROM usuarios WHERE id_usuario = p_id_operario;
            
            SELECT COALESCE(SUM(cantidad_producida), 0) INTO v_total_bolsas
            FROM producciones
            WHERE id_operario = p_id_operario
            AND DATE(fecha_produccion) BETWEEN p_fecha_inicio AND p_fecha_fin
            AND estado_validacion = 'aprobado'
            AND id_comision IS NULL;
            
            SET v_monto_comision = v_total_bolsas * v_tarifa;
            
            INSERT INTO comisiones (id_operario, fecha_inicio, fecha_fin, total_bolsas_producidas, tarifa_aplicada, monto_comision, monto_total, estado, fecha_calculo, usuario_creo) 
            VALUES (p_id_operario, p_fecha_inicio, p_fecha_fin, v_total_bolsas, v_tarifa, v_monto_comision, v_monto_comision, 'calculado', NOW(), p_id_usuario_admin);
            
            SET p_id_comision = LAST_INSERT_ID();
            
            INSERT INTO comisiones_detalle (id_comision, id_produccion, fecha_produccion, cantidad_bolsas, tarifa_por_bolsa, subtotal)
            SELECT p_id_comision, id_produccion, DATE(fecha_produccion), cantidad_producida, v_tarifa, cantidad_producida * v_tarifa
            FROM producciones
            WHERE id_operario = p_id_operario
            AND DATE(fecha_produccion) BETWEEN p_fecha_inicio AND p_fecha_fin
            AND estado_validacion = 'aprobado'
            AND id_comision IS NULL;
            
            UPDATE producciones SET id_comision = p_id_comision
            WHERE id_operario = p_id_operario
            AND DATE(fecha_produccion) BETWEEN p_fecha_inicio AND p_fecha_fin
            AND estado_validacion = 'aprobado'
            AND id_comision IS NULL;
            
            SET p_mensaje = CONCAT('Comisión calculada exitosamente. Total: S/ ', v_monto_comision);
        END IF;
    END$$

    CREATE PROCEDURE `sp_registrar_pago_comision`(
        IN p_id_comision INT UNSIGNED,
        IN p_fecha_pago DATE,
        IN p_metodo_pago VARCHAR(50),
        IN p_numero_operacion VARCHAR(100),
        IN p_id_usuario_admin INT UNSIGNED,
        OUT p_mensaje VARCHAR(255)
    )
    BEGIN
        UPDATE comisiones
        SET estado = 'pagado',
            fecha_pago = p_fecha_pago,
            metodo_pago = p_metodo_pago,
            numero_operacion = p_numero_operacion,
            usuario_pago = p_id_usuario_admin
        WHERE id_comision = p_id_comision;
        SET p_mensaje = 'Pago registrado exitosamente';
    END$$

    CREATE PROCEDURE `sp_resumen_comisiones_operario`(
        IN p_id_operario INT UNSIGNED,
        IN p_anio INT,
        IN p_mes INT
    )
    BEGIN
        SELECT 
            DATE(p.fecha_produccion) AS fecha,
            COUNT(p.id_produccion) AS total_producciones,
            SUM(CASE WHEN p.estado_validacion = 'aprobado' THEN p.cantidad_producida ELSE 0 END) AS bolsas_aprobadas,
            SUM(CASE WHEN p.estado_validacion = 'rechazado' THEN p.cantidad_producida ELSE 0 END) AS bolsas_rechazadas,
            SUM(CASE WHEN p.estado_validacion = 'pendiente' THEN p.cantidad_producida ELSE 0 END) AS bolsas_pendientes,
            u.tarifa_por_bolsa,
            SUM(CASE WHEN p.estado_validacion = 'aprobado' THEN p.cantidad_producida * u.tarifa_por_bolsa ELSE 0 END) AS comision_estimada,
            CASE WHEN p.id_comision IS NOT NULL THEN 'SI' ELSE 'NO' END AS incluida_en_comision,
            p.id_comision
        FROM producciones p
        INNER JOIN usuarios u ON p.id_operario = u.id_usuario
        WHERE p.id_operario = p_id_operario
        AND YEAR(p.fecha_produccion) = p_anio
        AND MONTH(p.fecha_produccion) = p_mes
        GROUP BY DATE(p.fecha_produccion), u.tarifa_por_bolsa, p.id_comision
        ORDER BY DATE(p.fecha_produccion) DESC;
    END$$

    CREATE PROCEDURE `sp_calcular_costo_unitario`(IN p_id_produccion INT)
    BEGIN
        DECLARE v_costo_fibra DECIMAL(12,4);
        DECLARE v_costo_bolsas DECIMAL(12,4);
        DECLARE v_costo_mano_obra DECIMAL(12,4);
        DECLARE v_cantidad DECIMAL(12,2);
        DECLARE v_factor_calidad DECIMAL(5,2);
        DECLARE v_costo_total DECIMAL(12,4);
        
        SELECT COALESCE(cf.factor_precio, 1.00) INTO v_factor_calidad
        FROM producciones pr
        INNER JOIN lotes_fibra l ON pr.id_lote_fibra = l.id_lote
        LEFT JOIN calidades_fibra cf ON l.id_calidad_fibra = cf.id_calidad_fibra
        WHERE pr.id_produccion = p_id_produccion;
        
        SELECT 
            ((l.precio_total / l.peso_neto) * (l.peso_neto / NULLIF(l.cantidad_producida_real, 0))) * v_factor_calidad,
            pr.peso_bolsas_consumido * (SELECT AVG(precio_por_kg) FROM compras_bolsas ORDER BY id_compra_bolsa DESC LIMIT 5),
            u.tarifa_por_bolsa,
            pr.cantidad_producida
        INTO v_costo_fibra, v_costo_bolsas, v_costo_mano_obra, v_cantidad
        FROM producciones pr
        INNER JOIN lotes_fibra l ON pr.id_lote_fibra = l.id_lote
        INNER JOIN usuarios u ON pr.id_operario = u.id_usuario
        WHERE pr.id_produccion = p_id_produccion;
        
        SET v_costo_total = (v_costo_fibra + v_costo_bolsas + v_costo_mano_obra) / NULLIF(v_cantidad, 0);
        SELECT COALESCE(v_costo_total, 0) AS costo_unitario;
    END$$

    DELIMITER ;

    -- --------------------------------------------------------
    -- 4. TRIGGERS
    -- --------------------------------------------------------

    DELIMITER $$

    -- Trigger: Actualizar inventario de fibra al crear lote
    CREATE TRIGGER `trg_after_insert_lote_fibra` AFTER INSERT ON `lotes_fibra`
    FOR EACH ROW
    BEGIN
        UPDATE `inventario` SET `cantidad` = `cantidad` + NEW.peso_neto WHERE `tipo_item` = 'fibra';
        INSERT INTO `kardex` (`tipo_item`, `tipo_movimiento`, `cantidad`, `unidad_medida`, `saldo_anterior`, `saldo_nuevo`, `referencia_tipo`, `referencia_id`, `observaciones`, `usuario_registro`)
        SELECT 'fibra', 'entrada', NEW.peso_neto, 'kg', `cantidad` - NEW.peso_neto, `cantidad`, 'compra_fibra', NEW.id_lote, CONCAT('Compra de lote ', NEW.codigo_lote), NEW.usuario_creacion
        FROM `inventario` WHERE `tipo_item` = 'fibra';
    END$$

    -- Trigger: Actualizar inventario de bolsas al comprar
    CREATE TRIGGER `trg_after_insert_compra_bolsas` AFTER INSERT ON `compras_bolsas`
    FOR EACH ROW
    BEGIN
        UPDATE `inventario` SET `cantidad` = `cantidad` + NEW.peso_kg WHERE `tipo_item` = 'bolsas_plasticas';
        INSERT INTO `kardex` (`tipo_item`, `tipo_movimiento`, `cantidad`, `unidad_medida`, `saldo_anterior`, `saldo_nuevo`, `referencia_tipo`, `referencia_id`, `usuario_registro`)
        SELECT 'bolsas_plasticas', 'entrada', NEW.peso_kg, 'kg', `cantidad` - NEW.peso_kg, `cantidad`, 'compra_bolsa', NEW.id_compra_bolsa, NEW.usuario_creacion
        FROM `inventario` WHERE `tipo_item` = 'bolsas_plasticas';
    END$$

    -- Trigger: Marcar cubo en uso al insertar producción
    CREATE TRIGGER `trg_produccion_cubo_en_uso` AFTER INSERT ON `producciones`
    FOR EACH ROW
    BEGIN
        UPDATE cubos_fibra SET estado = 'en_uso' WHERE id_cubo = NEW.id_cubo AND estado = 'disponible';
    END$$

    -- Trigger: Actualizar inventario y asignar calidad al aprobar producción
    CREATE TRIGGER `trg_produccion_aprobada_inventario` AFTER UPDATE ON `producciones`
    FOR EACH ROW
    BEGIN
        DECLARE v_calidad_napa INT UNSIGNED;
        DECLARE v_peso_bolsas_consumido DECIMAL(12,2);
        DECLARE v_saldo_bolsas DECIMAL(12,2);
        DECLARE v_saldo_producto DECIMAL(12,2);
        DECLARE v_cantidad_producida INT;
        
        IF OLD.estado_validacion != 'aprobado' AND NEW.estado_validacion = 'aprobado' THEN
            SET v_calidad_napa = NEW.id_calidad_napa;
            
            IF v_calidad_napa IS NULL THEN
                SELECT cf.id_calidad_napa_destino INTO v_calidad_napa
                FROM cubos_fibra c
                INNER JOIN lotes_fibra l ON c.id_lote = l.id_lote
                LEFT JOIN calidades_fibra cf ON l.id_calidad_fibra = cf.id_calidad_fibra
                WHERE c.id_cubo = NEW.id_cubo
                LIMIT 1;
                IF v_calidad_napa IS NULL THEN SELECT MIN(id_calidad_napa) INTO v_calidad_napa FROM calidades_napa WHERE estado = 'activo'; END IF;
            END IF;
            
            SET v_cantidad_producida = NEW.cantidad_producida;
            SET v_peso_bolsas_consumido = NEW.peso_bolsas_consumido;
            
            UPDATE inventario SET cantidad = cantidad - v_peso_bolsas_consumido WHERE tipo_item = 'bolsas_plasticas';
            SELECT cantidad INTO v_saldo_bolsas FROM inventario WHERE tipo_item = 'bolsas_plasticas';
            INSERT INTO kardex (tipo_item, tipo_movimiento, cantidad, unidad_medida, saldo_anterior, saldo_nuevo, referencia_tipo, referencia_id, observaciones) VALUES ('bolsas_plasticas', 'salida', v_peso_bolsas_consumido, 'kg', v_saldo_bolsas + v_peso_bolsas_consumido, v_saldo_bolsas, 'produccion', NEW.id_produccion, CONCAT('Consumo en producción #', NEW.id_produccion));
            
            INSERT IGNORE INTO inventario (tipo_item, id_calidad_napa, cantidad, unidad_medida, stock_minimo) VALUES ('producto_terminado', v_calidad_napa, 0, 'unidades', 100);
            UPDATE inventario SET cantidad = cantidad + v_cantidad_producida WHERE tipo_item = 'producto_terminado' AND id_calidad_napa = v_calidad_napa;
            SELECT cantidad INTO v_saldo_producto FROM inventario WHERE tipo_item = 'producto_terminado' AND id_calidad_napa = v_calidad_napa;
            INSERT INTO kardex (tipo_item, id_calidad_napa, tipo_movimiento, cantidad, unidad_medida, saldo_anterior, saldo_nuevo, referencia_tipo, referencia_id, observaciones) VALUES ('producto_terminado', v_calidad_napa, 'entrada', v_cantidad_producida, 'unidades', v_saldo_producto - v_cantidad_producida, v_saldo_producto, 'produccion', NEW.id_produccion, CONCAT('Producción #', NEW.id_produccion, ' aprobada'));
            
            UPDATE cubos_fibra SET cantidad_producida_real = COALESCE(cantidad_producida_real, 0) + v_cantidad_producida, estado = IF((cantidad_estimada_bolsas - (COALESCE(cantidad_producida_real, 0) + v_cantidad_producida)) <= 0, 'agotado', 'en_uso') WHERE id_cubo = NEW.id_cubo;
            UPDATE lotes_fibra l SET cantidad_producida_real = (SELECT COALESCE(SUM(c.cantidad_producida_real), 0) FROM cubos_fibra c WHERE c.id_lote = l.id_lote), cantidad_estimada_bolsas = (SELECT COALESCE(SUM(c.cantidad_estimada_bolsas), 0) FROM cubos_fibra c WHERE c.id_lote = l.id_lote), estado = CASE WHEN (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote AND estado = 'agotado') = (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote) THEN 'agotado' WHEN (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote AND estado = 'en_uso') > 0 THEN 'en_proceso' ELSE 'disponible' END WHERE l.id_lote = (SELECT id_lote FROM cubos_fibra WHERE id_cubo = NEW.id_cubo);
        END IF;
    END$$

    -- Trigger: Descontar inventario al vender
    CREATE TRIGGER `trg_after_insert_venta` AFTER INSERT ON `ventas`
    FOR EACH ROW
    BEGIN
        DECLARE v_saldo DECIMAL(12,2);
        DECLARE v_id_calidad INT UNSIGNED;
        IF NEW.estado_pago != 'cancelado' THEN
            SET v_id_calidad = NEW.id_calidad_napa;
            UPDATE inventario SET cantidad = cantidad - NEW.cantidad_vendida WHERE tipo_item = 'producto_terminado' AND id_calidad_napa = v_id_calidad;
            SELECT cantidad INTO v_saldo FROM inventario WHERE tipo_item = 'producto_terminado' AND id_calidad_napa = v_id_calidad;
            INSERT INTO kardex (tipo_item, id_calidad_napa, tipo_movimiento, cantidad, unidad_medida, saldo_anterior, saldo_nuevo, referencia_tipo, referencia_id, descripcion) VALUES ('producto_terminado', v_id_calidad, 'salida', NEW.cantidad_vendida, 'unidades', v_saldo + NEW.cantidad_vendida, v_saldo, 'venta', NEW.id_venta, CONCAT('Venta #', NEW.id_venta));
        END IF;
    END$$

    -- Trigger: Actualizar estado de venta al crear entrega
    CREATE TRIGGER `trg_after_insert_entrega` AFTER INSERT ON `entregas`
    FOR EACH ROW
    BEGIN
        UPDATE `ventas` SET `estado_entrega` = 'entregado' WHERE `id_venta` = NEW.id_venta;
    END$$

    DELIMITER ;

    -- --------------------------------------------------------
    -- 5. DATOS INICIALES
    -- --------------------------------------------------------

    -- Usuarios
    INSERT INTO `usuarios` (`username`, `password_hash`, `nombre_completo`, `rol`, `estado`) VALUES
    ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador del Sistema', 'administrador', 'activo'),
    ('operador1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez - Operador', 'operador', 'activo'),
    ('vendedor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María González - Vendedora', 'vendedor', 'activo');

    -- Configuración
    INSERT INTO `configuracion_sistema` (`parametro`, `valor`, `tipo_dato`, `descripcion`) VALUES
    ('cantidad_estimada_default', '70', 'entero', 'Cantidad estimada por defecto de bolsas por fardo'),
    ('factor_conversion_bolsas', '0.02', 'decimal', 'Factor de conversión: kg por bolsa plástica'),
    ('tolerancia_merma', '5', 'decimal', 'Porcentaje de tolerancia de merma'),
    ('stock_minimo_bolsas', '10', 'decimal', 'Stock mínimo de bolsas plásticas en kg'),
    ('stock_minimo_fibra', '100', 'decimal', 'Stock mínimo de fibra en kg'),
    ('margen_minimo_venta', '10', 'decimal', 'Porcentaje de margen mínimo sugerido'),
    ('timeout_sesion', '30', 'entero', 'Minutos de inactividad antes de cerrar sesión'),
    ('nombre_empresa', 'Taller de Napa Familiar', 'texto', 'Nombre del negocio'),
    ('ruc_empresa', '', 'texto', 'RUC del negocio');

    -- Calidades Napa
    INSERT INTO `calidades_napa` (`nombre`, `codigo`, `precio_base_sugerido`, `estado`) VALUES
    ('Napa Premium (Fibra Virgen)', 'A', 15.00, 'activo'),
    ('Napa Estándar (Fibra Cristalizada)', 'B', 12.00, 'activo'),
    ('Napa Económica (Fibra Reciclada)', 'C', 10.00, 'activo'),
    ('Napa Básica', 'D', 8.00, 'activo');

    -- Calidades Fibra
    INSERT INTO `calidades_fibra` (`nombre`, `descripcion`, `factor_precio`, `color`, `estado`, `id_calidad_napa_destino`) VALUES
    ('Fibra Virgen', 'Fibra de primera calidad', 1.20, 'success', 'activo', 1),
    ('Fibra Cristalizada', 'Fibra procesada estándar', 1.00, 'info', 'activo', 2),
    ('Fibra Reciclada Premium', 'Fibra reciclada alta calidad', 0.85, 'warning', 'activo', 3),
    ('Fibra Estándar', 'Fibra uso general', 0.80, 'secondary', 'activo', 4);

    -- Inventario Inicial
    INSERT INTO `inventario` (`tipo_item`, `cantidad`, `unidad_medida`, `stock_minimo`) VALUES
    ('fibra', 0.00, 'kg', 100.00),
    ('bolsas_plasticas', 0.00, 'kg', 10.00);

    INSERT INTO `inventario` (`tipo_item`, `id_calidad_napa`, `cantidad`, `unidad_medida`, `stock_minimo`) VALUES
    ('producto_terminado', 1, 0.00, 'unidades', 50.00),
    ('producto_terminado', 2, 0.00, 'unidades', 50.00),
    ('producto_terminado', 3, 0.00, 'unidades', 50.00),
    ('producto_terminado', 4, 0.00, 'unidades', 50.00);