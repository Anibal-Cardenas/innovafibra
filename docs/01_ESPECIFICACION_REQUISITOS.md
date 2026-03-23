# ESPECIFICACIÓN DE REQUISITOS DE SOFTWARE (ERS)
## Sistema de Gestión de Producción - Taller de Napa

**Versión:** 1.0  
**Fecha:** 09 de Enero, 2026  
**Elaborado por:** Equipo de Desarrollo  
**Cliente:** Taller Familiar de Napa

---

## 1. INTRODUCCIÓN

### 1.1 Propósito
Este documento describe los requisitos funcionales y no funcionales para el desarrollo de un sistema web de gestión de producción para un taller familiar de fabricación de Napa. El sistema está enfocado en la detección de pérdidas (mermas) y el control estricto de inventario.

### 1.2 Alcance
El sistema **"Sistema de Gestión de Producción Napa"** cubrirá:
- Gestión de compras de materia prima (fibra)
- Control de insumos secundarios (bolsas plásticas)
- Producción y control de eficiencia
- Recursos humanos (pago por destajo)
- Ventas y precios
- Logística y entregas
- Control de acceso con roles diferenciados

### 1.3 Usuarios del Sistema
- **Administrador**: Dueño del negocio con acceso completo
- **Trabajador**: Operarios y supervisores con acceso limitado

---

## 2. REQUISITOS FUNCIONALES

### 2.1 Módulo de Autenticación y Seguridad (RF-AUTH)

#### RF-AUTH-01: Inicio de Sesión
**Prioridad:** ALTA  
**Descripción:** El sistema debe permitir el inicio de sesión con usuario y contraseña.

**Criterios de Aceptación:**
- Validación de credenciales contra base de datos
- Sesión persistente durante el uso
- Cierre de sesión manual
- Timeout de sesión por inactividad (30 minutos)

#### RF-AUTH-02: Gestión de Roles
**Prioridad:** ALTA  
**Descripción:** El sistema debe implementar dos roles:

**Rol Administrador:**
- Acceso total a todos los módulos
- Creación y edición de usuarios
- Visualización de reportes y costos
- Configuración del sistema

**Rol Trabajador:**
- Acceso a módulo de producción (registro)
- Consulta de su producción personal
- Sin acceso a información financiera

---

### 2.2 Módulo de Compras - Materia Prima (RF-COMP)

#### RF-COMP-01: Registro de Compra de Fibra
**Prioridad:** ALTA  
**Descripción:** Registrar la compra de fardos de fibra (materia prima principal).

**Campos Requeridos:**
- Fecha de compra
- Proveedor
- Peso bruto (kg)
- Peso neto (kg)
- Precio total de compra
- Cantidad estimada de bolsas (default: 70, editable)
- Notas/observaciones

**Cálculos Automáticos:**
- ID de Lote interno (auto-generado): `LOTE-YYYY-MM-NNNN`
- Precio por kilogramo: `Precio Total / Peso Neto`
- Rendimiento estimado: `Cantidad Estimada / Peso Neto` (bolsas/kg)

**Criterios de Aceptación:**
- El sistema genera un ID de lote único
- Se registra el estado inicial del lote como "Disponible"
- Se actualiza el inventario de materia prima
- Se genera registro en historial de compras

#### RF-COMP-02: Consulta de Lotes de Fibra
**Prioridad:** MEDIA  
**Descripción:** Visualizar listado de lotes de fibra disponibles y su estado.

**Filtros:**
- Por rango de fechas
- Por estado (Disponible, En Proceso, Agotado)
- Por proveedor

**Información Mostrada:**
- ID de Lote
- Fecha de compra
- Peso neto
- Cantidad estimada de bolsas
- Cantidad producida (real)
- Estado actual
- Indicador de rendimiento

---

### 2.3 Módulo de Insumos Secundarios (RF-INSU)

#### RF-INSU-01: Registro de Compra de Bolsas Plásticas
**Prioridad:** ALTA  
**Descripción:** Registrar compra de bolsas plásticas que se compran por peso (kilogramos).

**Campos Requeridos:**
- Fecha de compra
- Proveedor
- Peso en kilogramos
- Precio total
- Tipo de bolsa

**Cálculos Automáticos:**
- Precio por kilogramo
- Cantidad equivalente en unidades (usando factor de conversión)

**Criterios de Aceptación:**
- Se actualiza el inventario de bolsas en kilogramos
- Se registra el historial de compra

#### RF-INSU-02: Factor de Conversión Configurable
**Prioridad:** ALTA  
**Descripción:** El administrador puede configurar el factor de conversión bolsa-peso.

**Parámetro:**
- Factor de conversión (kg/bolsa) - Ejemplo: 0.02 kg/bolsa

**Criterios de Aceptación:**
- Solo el administrador puede modificarlo
- El cambio aplica a partir de la siguiente transacción
- Se registra historial de cambios del factor

#### RF-INSU-03: Descuento Automático de Inventario
**Prioridad:** ALTA  
**Descripción:** Al registrar producción, se descuenta automáticamente el peso equivalente del inventario de bolsas.

**Lógica:**
```
Peso_Descuento = Cantidad_Producida × Factor_Conversión
Nuevo_Inventario = Inventario_Actual - Peso_Descuento
```

**Validaciones:**
- Verificar que hay stock suficiente antes de registrar producción
- Alertar si el inventario cae por debajo del mínimo configurado

---

### 2.4 Módulo de Producción y Control de Eficiencia (RF-PROD)

#### RF-PROD-01: Registro de Producción
**Prioridad:** CRÍTICA  
**Descripción:** Registrar la producción diaria de bolsas de Napa.

**Campos Requeridos:**
- Fecha de producción
- ID de Lote de fibra utilizado
- Operario(s) asignados
- Cantidad real producida (bolsas)
- Supervisor que valida

**Cálculos Automáticos:**
- Consumo de fibra (peso)
- Merma en peso: `Peso Lote - Peso Producción Equivalente`
- Porcentaje de merma
- Eficiencia: `(Producción Real / Cantidad Estimada) × 100`

**Criterios de Aceptación:**
- Se descuenta del inventario de fibra
- Se descuenta del inventario de bolsas plásticas
- Se actualiza el estado del lote
- Se genera registro para pago por destajo

#### RF-PROD-02: Alerta de Rendimiento Bajo
**Prioridad:** ALTA  
**Descripción:** El sistema debe alertar cuando el rendimiento real es inferior al estimado.

**Lógica de Alerta:**
```
Tolerancia_Configurable = 5% (editable por admin)

Si (Producción_Real < Cantidad_Estimada × (1 - Tolerancia/100))
    Entonces: Marcar como "Bajo Rendimiento"
```

**Criterios de Aceptación:**
- Flag visual en el registro (color rojo/amarillo)
- Estado: "Merma Excesiva" o "Rendimiento Normal"
- Notificación al administrador
- Listado filtrable de producciones con bajo rendimiento

#### RF-PROD-03: Trazabilidad de Lote
**Prioridad:** MEDIA  
**Descripción:** Seguimiento completo de un lote desde compra hasta consumo final.

**Información Rastreable:**
- Historial de compra
- Fechas de uso en producción
- Cantidad producida por fecha
- Operarios involucrados
- Mermas generadas
- Balance final del lote

---

### 2.5 Módulo de Recursos Humanos (RF-RRHH)

#### RF-RRHH-01: Gestión de Operarios
**Prioridad:** ALTA  
**Descripción:** Administrar información de operarios y sus datos.

**Campos:**
- Nombre completo
- DNI/Identificación
- Fecha de ingreso
- Tarifa por bolsa producida
- Estado (Activo/Inactivo)

#### RF-RRHH-02: Cálculo de Pago por Destajo
**Prioridad:** ALTA  
**Descripción:** Calcular automáticamente el pago basado en producción validada.

**Lógica:**
```
Pago_Total = Cantidad_Bolsas_Validadas × Tarifa_Por_Bolsa
```

**Criterios de Aceptación:**
- Solo se cuentan bolsas validadas por el supervisor
- Se puede consultar por rango de fechas
- Reporte individual por operario
- Reporte consolidado de nómina

#### RF-RRHH-03: Validación de Calidad
**Prioridad:** ALTA  
**Descripción:** El supervisor valida la calidad antes de aprobar para pago.

**Proceso:**
1. Operario termina producción
2. Supervisor inspecciona
3. Supervisor aprueba/rechaza
4. Solo producción aprobada suma a comisión

**Campos:**
- Estado de validación (Pendiente, Aprobado, Rechazado)
- Supervisor que valida
- Fecha de validación
- Observaciones de calidad

---

### 2.6 Módulo de Ventas (RF-VENT)

#### RF-VENT-01: Registro de Venta
**Prioridad:** ALTA  
**Descripción:** Registrar ventas de bolsas de Napa con precio variable.

**Campos Requeridos:**
- Fecha de venta
- Cliente
- Cantidad vendida (bolsas)
- Precio unitario (manual)
- Precio total
- Forma de pago
- Estado (Pendiente, Pagado, Crédito)

**Información de Referencia (visible al registrar):**
- Costo unitario promedio
- Margen de ganancia estimado
- Stock disponible actual

**Criterios de Aceptación:**
- Se descuenta del inventario de producto terminado
- Se calcula el precio total automáticamente
- Validación de stock disponible
- Registro en historial de ventas

#### RF-VENT-02: Cálculo de Costo Unitario
**Prioridad:** ALTA  
**Descripción:** El sistema calcula el costo unitario para referencia del administrador.

**Fórmula:**
```
Costo_Unitario = (Costo_Fibra_Usada + Costo_Bolsas + Costo_Mano_Obra) / Cantidad_Producida
```

**Criterios de Aceptación:**
- Cálculo automático por lote producido
- Visualización al momento de registrar venta
- Reporte de rentabilidad por venta

---

### 2.7 Módulo de Logística (RF-LOG)

#### RF-LOG-01: Registro de Entrega
**Prioridad:** MEDIA  
**Descripción:** Registrar entregas de pedidos a clientes.

**Campos Requeridos:**
- Fecha de entrega
- Venta asociada
- Cliente
- Chofer asignado
- Vehículo
- Dirección de entrega

#### RF-LOG-02: Guía de Entrega
**Prioridad:** MEDIA  
**Descripción:** Generar documento de guía de entrega.

**Información en Guía:**
- Número de guía (auto-generado)
- Fecha
- Cliente
- Dirección
- Detalle de productos
- Chofer
- Firma de recepción

**Criterios de Aceptación:**
- Generación en PDF
- Impresión directa
- Actualización de estado de venta a "Entregado"

#### RF-LOG-03: Gestión de Choferes
**Prioridad:** BAJA  
**Descripción:** Administrar información de choferes.

**Campos:**
- Nombre
- Licencia
- Vehículo asignado
- Teléfono

---

### 2.8 Módulo de Reportes y Dashboard (RF-REP)

#### RF-REP-01: Dashboard Principal
**Prioridad:** ALTA  
**Descripción:** Panel de control con KPIs principales.

**Indicadores:**
- Producción del día/semana/mes
- Ventas del día/semana/mes
- Mermas totales
- Inventario actual (fibra, bolsas, producto terminado)
- Alertas de bajo rendimiento
- Lotes con merma excesiva

#### RF-REP-02: Reporte de Mermas
**Prioridad:** ALTA  
**Descripción:** Reporte detallado de pérdidas por lote y periodo.

**Información:**
- Lote
- Fecha
- Merma en peso
- Porcentaje de merma
- Operario
- Causa probable

#### RF-REP-03: Reporte de Producción
**Prioridad:** MEDIA  
**Descripción:** Análisis de producción por periodo.

**Métricas:**
- Producción total
- Producción por operario
- Eficiencia promedio
- Comparativa con estimaciones

#### RF-REP-04: Reporte Financiero
**Prioridad:** MEDIA  
**Descripción:** Análisis de costos y rentabilidad.

**Información:**
- Costos de materia prima
- Costos de insumos
- Costos de mano de obra
- Ingresos por ventas
- Margen de ganancia
- Rentabilidad por periodo

---

## 3. REQUISITOS NO FUNCIONALES

### 3.1 Rendimiento (RNF-REND)
- **RNF-REND-01**: El tiempo de respuesta para operaciones básicas no debe exceder 2 segundos
- **RNF-REND-02**: El sistema debe soportar al menos 20 usuarios simultáneos

### 3.2 Seguridad (RNF-SEG)
- **RNF-SEG-01**: Las contraseñas deben estar encriptadas (hash)
- **RNF-SEG-02**: Implementar protección contra inyección SQL
- **RNF-SEG-03**: Implementar protección XSS
- **RNF-SEG-04**: Auditoría de acciones críticas (quién, qué, cuándo)

### 3.3 Usabilidad (RNF-USAB)
- **RNF-USAB-01**: Interfaz responsive (adaptable a tablets)
- **RNF-USAB-02**: Mensajes de error claros y en español
- **RNF-USAB-03**: Formularios con validación en tiempo real
- **RNF-USAB-04**: Navegación intuitiva con máximo 3 clics para operaciones comunes

### 3.4 Disponibilidad (RNF-DISP)
- **RNF-DISP-01**: Disponibilidad del 99% en horario laboral (6am - 8pm)
- **RNF-DISP-02**: Backup automático diario de base de datos

### 3.5 Mantenibilidad (RNF-MANT)
- **RNF-MANT-01**: Código documentado
- **RNF-MANT-02**: Base de datos normalizada
- **RNF-MANT-03**: Logs de errores y actividad

### 3.6 Compatibilidad (RNF-COMP)
- **RNF-COMP-01**: Compatible con Chrome, Firefox, Edge (últimas 2 versiones)
- **RNF-COMP-02**: Base de datos: MySQL 5.7 o superior
- **RNF-COMP-03**: Servidor: Apache/Nginx con PHP 7.4+

---

## 4. RESTRICCIONES Y SUPUESTOS

### 4.1 Restricciones
- Presupuesto limitado (negocio familiar)
- Infraestructura: Servidor local (XAMPP)
- Personal técnico limitado para mantenimiento

### 4.2 Supuestos
- Conexión a internet estable en el taller
- Al menos una PC dedicada para el sistema
- Personal capacitado en uso básico de computadoras

---

## 5. GLOSARIO

| Término | Definición |
|---------|-----------|
| Napa | Producto final: bolsas de fibra procesada |
| Fardo | Unidad de compra de fibra (materia prima) |
| Lote | Conjunto de materia prima identificable para trazabilidad |
| Merma | Pérdida de material en el proceso productivo |
| Destajo | Sistema de pago por unidad producida |
| Yield/Rendimiento | Relación entre cantidad producida y materia prima usada |
| Factor de Conversión | Equivalencia peso-unidad para bolsas plásticas |

---

## 6. APROBACIONES

| Rol | Nombre | Firma | Fecha |
|-----|--------|-------|-------|
| Cliente/Dueño | ___________ | _______ | ______ |
| Analista | ___________ | _______ | ______ |
| Desarrollador | ___________ | _______ | ______ |

---

**FIN DEL DOCUMENTO**
