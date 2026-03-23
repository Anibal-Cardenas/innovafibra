<?php
/**
 * Constantes del Sistema
 * Sistema de Gestión de Producción - Taller de Napa
 */

// Roles de usuario
define('ROL_ADMINISTRADOR', 'administrador');
define('ROL_OPERADOR', 'operador');
define('ROL_VENDEDOR', 'vendedor');
define('ROL_SUPERVISOR', 'supervisor');
define('ROL_TRABAJADOR', 'trabajador'); // Legacy - usar operador

// Estados de usuario
define('ESTADO_ACTIVO', 'activo');
define('ESTADO_INACTIVO', 'inactivo');

// Estados de lote de fibra
define('LOTE_DISPONIBLE', 'disponible');
define('LOTE_EN_PROCESO', 'en_proceso');
define('LOTE_AGOTADO', 'agotado');
define('LOTE_MERMA_EXCESIVA', 'merma_excesiva');

// Estados de validación de producción
define('VALIDACION_PENDIENTE', 'pendiente');
define('VALIDACION_APROBADO', 'aprobado');
define('VALIDACION_RECHAZADO', 'rechazado');

// Estados de pago de venta
define('PAGO_PENDIENTE', 'pendiente');
define('PAGO_PAGADO', 'pagado');
define('PAGO_CREDITO', 'credito');
define('PAGO_CANCELADO', 'cancelado');

// Estados de entrega
define('ENTREGA_PENDIENTE', 'pendiente');
define('ENTREGA_ENTREGADO', 'entregado');

// Formas de pago
define('FORMA_PAGO_EFECTIVO', 'efectivo');
define('FORMA_PAGO_TRANSFERENCIA', 'transferencia');
define('FORMA_PAGO_CHEQUE', 'cheque');
define('FORMA_PAGO_CREDITO', 'credito');

// Tipos de proveedor
define('PROVEEDOR_FIBRA', 'fibra');
define('PROVEEDOR_BOLSAS', 'bolsas');
define('PROVEEDOR_OTROS', 'otros');

// Tipos de item de inventario
define('INVENTARIO_FIBRA', 'fibra');
define('INVENTARIO_BOLSAS', 'bolsas_plasticas');
define('INVENTARIO_PRODUCTO', 'producto_terminado');
define('INVENTARIO_PRODUCTO_TERMINADO', 'producto_terminado'); // Alias para consistencia

// Tipos de movimiento de kardex
define('MOVIMIENTO_ENTRADA', 'entrada');
define('MOVIMIENTO_SALIDA', 'salida');
define('MOVIMIENTO_AJUSTE', 'ajuste');
define('MOVIMIENTO_MERMA', 'merma');

// Tipos de referencia de kardex
define('REFERENCIA_COMPRA_FIBRA', 'compra_fibra');
define('REFERENCIA_COMPRA_BOLSA', 'compra_bolsa');
define('REFERENCIA_PRODUCCION', 'produccion');
define('REFERENCIA_VENTA', 'venta');
define('REFERENCIA_AJUSTE', 'ajuste');

// Acciones de auditoría
define('AUDITORIA_INSERT', 'INSERT');
define('AUDITORIA_UPDATE', 'UPDATE');
define('AUDITORIA_DELETE', 'DELETE');
define('AUDITORIA_LOGIN', 'LOGIN');
define('AUDITORIA_LOGOUT', 'LOGOUT');

// Configuración por defecto del sistema
define('DEFAULT_CANTIDAD_ESTIMADA', 70); // bolsas por fardo
define('DEFAULT_FACTOR_CONVERSION', 50); // bolsas por kg (para compra de bolsas plásticas)
define('DEFAULT_FACTOR_CONVERSION_CUBO', 50); // bolsas por kg (para estimar desde peso de cubo de fibra)
define('DEFAULT_TOLERANCIA_MERMA', 5); // porcentaje
define('DEFAULT_STOCK_MINIMO_BOLSAS', 10); // kg
define('DEFAULT_STOCK_MINIMO_FIBRA', 100); // kg
define('DEFAULT_MARGEN_MINIMO', 10); // porcentaje

// Niveles de alerta de inventario
define('ALERTA_CRITICO', 'CRÍTICO');
define('ALERTA_BAJO', 'BAJO');
define('ALERTA_NORMAL', 'NORMAL');

// Mensajes del sistema
define('MSG_SUCCESS', 'success');
define('MSG_ERROR', 'error');
define('MSG_WARNING', 'warning');
define('MSG_INFO', 'info');

// Colores para alertas
define('COLOR_SUCCESS', '#28a745');
define('COLOR_ERROR', '#dc3545');
define('COLOR_WARNING', '#ffc107');
define('COLOR_INFO', '#17a2b8');

// Prefijos para códigos auto-generados
define('PREFIX_LOTE', 'LOTE');
define('PREFIX_GUIA', 'GUIA');

// Unidades de medida
define('UNIDAD_KG', 'kg');
define('UNIDAD_UNIDADES', 'unidades');
define('UNIDAD_BOLSAS', 'bolsas');

// Moneda
define('MONEDA_SIMBOLO', 'S/');
define('MONEDA_CODIGO', 'PEN');
