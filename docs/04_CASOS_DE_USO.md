# CASOS DE USO
## Sistema de Gestión de Producción - Taller de Napa

**Versión:** 1.0  
**Fecha:** 09 de Enero, 2026

---

## 1. DIAGRAMA DE ACTORES

```
┌─────────────────────────────────────────────────────────────┐
│                    ACTORES DEL SISTEMA                       │
└─────────────────────────────────────────────────────────────┘

    ┌──────────────┐          ┌──────────────┐          ┌──────────────┐
    │              │          │              │          │              │
    │ ADMINISTRADOR│          │  SUPERVISOR  │          │  TRABAJADOR  │
    │              │          │              │          │              │
    └──────────────┘          └──────────────┘          └──────────────┘
          │                          │                          │
          │                          │                          │
          └──────────────┬───────────┴───────────┬──────────────┘
                         │                       │
                    ┌────▼────────────────────────▼────┐
                    │   SISTEMA DE PRODUCCIÓN NAPA    │
                    └─────────────────────────────────┘
```

### Descripción de Actores

| Actor | Rol | Permisos |
|-------|-----|----------|
| **Administrador** | Dueño del negocio | Acceso total al sistema, visualización de costos y reportes financieros |
| **Supervisor** | Supervisor de calidad | Validación de producción, consulta de reportes operativos |
| **Trabajador** | Operario de producción | Registro de producción propia, consulta de su producción y pagos |

---

## 2. LISTA DE CASOS DE USO

### 2.1 Módulo de Autenticación (CU-AUTH)

- **CU-AUTH-01**: Iniciar Sesión
- **CU-AUTH-02**: Cerrar Sesión
- **CU-AUTH-03**: Gestionar Usuarios

---

### 2.2 Módulo de Compras (CU-COMP)

- **CU-COMP-01**: Registrar Compra de Fibra
- **CU-COMP-02**: Consultar Lotes de Fibra
- **CU-COMP-03**: Registrar Compra de Bolsas Plásticas

---

### 2.3 Módulo de Producción (CU-PROD)

- **CU-PROD-01**: Registrar Producción
- **CU-PROD-02**: Validar Producción (Supervisor)
- **CU-PROD-03**: Consultar Producción con Merma Excesiva
- **CU-PROD-04**: Ver Trazabilidad de Lote

---

### 2.4 Módulo de RRHH (CU-RRHH)

- **CU-RRHH-01**: Gestionar Operarios
- **CU-RRHH-02**: Consultar Producción por Operario
- **CU-RRHH-03**: Generar Reporte de Nómina

---

### 2.5 Módulo de Ventas (CU-VENT)

- **CU-VENT-01**: Registrar Venta
- **CU-VENT-02**: Consultar Ventas
- **CU-VENT-03**: Anular Venta

---

### 2.6 Módulo de Logística (CU-LOG)

- **CU-LOG-01**: Registrar Entrega
- **CU-LOG-02**: Generar Guía de Entrega

---

### 2.7 Módulo de Reportes (CU-REP)

- **CU-REP-01**: Dashboard Principal
- **CU-REP-02**: Reporte de Mermas
- **CU-REP-03**: Reporte de Producción
- **CU-REP-04**: Reporte Financiero

---

## 3. CASOS DE USO DETALLADOS

---

### CU-AUTH-01: Iniciar Sesión

**Actor Principal:** Todos los usuarios  
**Objetivo:** Autenticar al usuario en el sistema  
**Precondiciones:** El usuario debe estar registrado en el sistema  
**Postcondiciones:** El usuario accede al sistema según su rol

#### Flujo Principal

1. El usuario accede a la página de login
2. El sistema muestra el formulario de autenticación
3. El usuario ingresa su username y password
4. El sistema valida las credenciales
5. El sistema registra el acceso en auditoría
6. El sistema redirige al dashboard según el rol del usuario

#### Flujos Alternativos

**4a. Credenciales incorrectas:**
- 4a1. El sistema muestra mensaje "Usuario o contraseña incorrectos"
- 4a2. El sistema incrementa contador de intentos fallidos
- 4a3. Si intentos > 3, bloquea temporalmente la cuenta (15 minutos)

**4b. Usuario inactivo:**
- 4b1. El sistema muestra mensaje "Su cuenta está inactiva. Contacte al administrador"
- 4b2. Termina el caso de uso

#### Reglas de Negocio

- **RN-SEG-02**: La sesión expira después de 30 minutos de inactividad
- **RN-GEN-02**: Se registra el login en auditoría

#### Interfaz

```
┌──────────────────────────────────────────┐
│     SISTEMA DE PRODUCCIÓN NAPA          │
├──────────────────────────────────────────┤
│                                          │
│  Usuario:  [________________]           │
│                                          │
│  Contraseña: [________________]         │
│                                          │
│              [  INGRESAR  ]             │
│                                          │
└──────────────────────────────────────────┘
```

---

### CU-COMP-01: Registrar Compra de Fibra

**Actor Principal:** Administrador  
**Objetivo:** Registrar la compra de un fardo de fibra  
**Precondiciones:** Usuario autenticado con rol Administrador  
**Postcondiciones:** Lote creado, inventario actualizado, código único generado

#### Flujo Principal

1. El administrador selecciona "Compras > Nueva Compra de Fibra"
2. El sistema muestra el formulario de registro
3. El sistema genera automáticamente el código de lote (LOTE-YYYY-MM-NNNN)
4. El administrador ingresa:
   - Fecha de compra
   - Proveedor (selección de lista)
   - Peso bruto (kg)
   - Peso neto (kg)
   - Precio total
   - Cantidad estimada de bolsas (default: 70, editable)
5. El sistema valida que peso_neto <= peso_bruto
6. El sistema calcula automáticamente:
   - Precio por kg = Precio Total / Peso Neto
   - Rendimiento estimado = Cantidad Estimada / Peso Neto
7. El administrador puede agregar observaciones (opcional)
8. El administrador confirma el registro
9. El sistema:
   - Crea el registro del lote con estado "Disponible"
   - Actualiza el inventario de fibra (+peso_neto)
   - Registra movimiento en kardex
   - Registra la acción en auditoría
10. El sistema muestra mensaje de confirmación con el código del lote

#### Flujos Alternativos

**5a. Peso neto mayor que peso bruto:**
- 5a1. El sistema muestra error "El peso neto no puede ser mayor al peso bruto"
- 5a2. Regresa al paso 4

**5b. Cantidad estimada fuera de rango:**
- 5b1. El sistema muestra error "La cantidad debe estar entre 1 y 1000"
- 5b2. Regresa al paso 4

#### Reglas de Negocio

- **RN-COMP-01**: Peso neto <= Peso bruto
- **RN-COMP-02**: Cantidad estimada default = 70
- **RN-COMP-04**: Código de lote auto-generado
- **RN-COMP-05**: Estado inicial = "Disponible"

#### Interfaz

```
┌────────────────────────────────────────────────────────┐
│  REGISTRAR COMPRA DE FIBRA                             │
├────────────────────────────────────────────────────────┤
│  Código Lote: [LOTE-2026-01-0001] (auto)              │
│                                                        │
│  Fecha Compra: [09/01/2026]        [📅]               │
│                                                        │
│  Proveedor:    [Fibras del Norte ▼]                   │
│                                                        │
│  Peso Bruto:   [120.50] kg                            │
│  Peso Neto:    [115.00] kg                            │
│                                                        │
│  Precio Total: [S/ 1,150.00]                          │
│  Precio/Kg:    [S/ 10.00] (calculado)                 │
│                                                        │
│  Cant. Estimada Bolsas: [70]                          │
│  Rendimiento:  [0.6087 bolsas/kg] (calculado)         │
│                                                        │
│  Observaciones: [________________________]            │
│                                                        │
│         [CANCELAR]           [GUARDAR]                │
└────────────────────────────────────────────────────────┘
```

---

### CU-PROD-01: Registrar Producción

**Actor Principal:** Trabajador (Operario)  
**Objetivo:** Registrar la producción diaria de bolsas  
**Precondiciones:** 
- Usuario autenticado
- Existe al menos un lote disponible
- Hay stock suficiente de bolsas plásticas
**Postcondiciones:** Producción registrada pendiente de validación

#### Flujo Principal

1. El operario selecciona "Producción > Registrar Producción"
2. El sistema muestra el formulario
3. El sistema lista los lotes disponibles o en proceso
4. El operario selecciona:
   - Fecha de producción (default: hoy)
   - Lote de fibra usado
   - Cantidad de bolsas producidas
5. El sistema muestra información del lote:
   - Cantidad estimada
   - Cantidad ya producida
   - Balance pendiente
6. El sistema calcula automáticamente:
   - Peso de bolsas plásticas consumido = Cantidad × Factor_Conversión
   - Eficiencia = (Cantidad Producida / Estimado del Lote) × 100
7. El sistema valida stock de bolsas plásticas
8. El sistema detecta si hay merma excesiva:
   - Si Producción < (Estimado × 0.95) → Flag merma excesiva = TRUE
9. El operario puede agregar observaciones
10. El operario confirma el registro
11. El sistema:
    - Crea registro con estado_validacion = "pendiente"
    - NO descuenta inventarios aún (hasta validación)
    - Muestra mensaje "Producción registrada. Pendiente de validación"

#### Flujos Alternativos

**7a. Stock insuficiente de bolsas:**
- 7a1. Sistema muestra "Stock insuficiente. Requiere X kg, hay Y kg"
- 7a2. El sistema no permite continuar
- 7a3. Termina el caso de uso

**8a. Merma excesiva detectada:**
- 8a1. El sistema muestra alerta "ATENCIÓN: Producción por debajo del rendimiento esperado"
- 8a2. El sistema requiere observaciones obligatorias
- 8a3. Continúa con el paso 10

#### Reglas de Negocio

- **RN-PROD-01**: Solo lotes disponibles o en proceso
- **RN-INSU-04**: Validar stock suficiente
- **RN-PROD-05**: Detectar merma excesiva
- **RN-RRHH-02**: Producción pendiente de validación

#### Interfaz

```
┌────────────────────────────────────────────────────────┐
│  REGISTRAR PRODUCCIÓN                                  │
├────────────────────────────────────────────────────────┤
│  Fecha:     [09/01/2026] [📅]                          │
│                                                        │
│  Lote Fibra: [LOTE-2026-01-0001 ▼]                    │
│  ┌──────────────────────────────────────────────────┐ │
│  │ Estimado: 70 bolsas                              │ │
│  │ Producido: 0 bolsas                              │ │
│  │ Pendiente: 70 bolsas                             │ │
│  └──────────────────────────────────────────────────┘ │
│                                                        │
│  Cantidad Producida: [65] bolsas                       │
│                                                        │
│  Stock Bolsas Plásticas: 45.5 kg disponibles          │
│  Consumo Estimado: 1.30 kg (65 × 0.02)                │
│                                                        │
│  ⚠️ ALERTA: Producción por debajo del estimado         │
│  Eficiencia: 92.86% (Tolerancia: 95%)                 │
│                                                        │
│  Observaciones: [____________________________]        │
│                 (obligatorio en caso de merma)         │
│                                                        │
│         [CANCELAR]           [REGISTRAR]              │
└────────────────────────────────────────────────────────┘
```

---

### CU-PROD-02: Validar Producción

**Actor Principal:** Supervisor o Administrador  
**Objetivo:** Validar la calidad de la producción registrada  
**Precondiciones:** 
- Usuario con rol Supervisor o Administrador
- Existe producción pendiente de validación
**Postcondiciones:** 
- Producción aprobada/rechazada
- Si aprobada: inventarios actualizados, se habilita pago

#### Flujo Principal

1. El supervisor selecciona "Producción > Validar Producción"
2. El sistema lista todas las producciones con estado "pendiente"
3. El supervisor selecciona una producción
4. El sistema muestra el detalle:
   - Operario
   - Fecha
   - Lote usado
   - Cantidad producida
   - Eficiencia
   - Flag de merma excesiva
   - Observaciones del operario
5. El supervisor inspecciona físicamente la calidad
6. El supervisor marca como "Aprobado" o "Rechazado"
7. Si rechaza, debe ingresar motivo (obligatorio)
8. El supervisor confirma la validación
9. **Si APROBADO:**
   - Sistema descuenta inventario de bolsas plásticas
   - Sistema incrementa inventario de producto terminado
   - Sistema actualiza producción acumulada del lote
   - Sistema actualiza estado del lote si corresponde
   - Sistema registra en kardex
   - Sistema habilita la producción para pago
10. **Si RECHAZADO:**
    - Sistema NO descuenta inventarios
    - Sistema NO suma a producción del lote
    - Sistema NO habilita para pago
11. El sistema muestra confirmación

#### Flujos Alternativos

**7a. Intenta rechazar sin motivo:**
- 7a1. Sistema muestra "Debe ingresar el motivo del rechazo"
- 7a2. Regresa al paso 7

#### Reglas de Negocio

- **RN-RRHH-02**: Validación obligatoria
- **RN-RRHH-03**: Solo producción aprobada se paga
- **RN-PROD-07**: Descuento de inventarios al aprobar

#### Interfaz

```
┌────────────────────────────────────────────────────────┐
│  VALIDAR PRODUCCIÓN                                    │
├────────────────────────────────────────────────────────┤
│  Producciones Pendientes:                              │
│  ┌──────────────────────────────────────────────────┐ │
│  │ ☐ 09/01/2026 - Juan Pérez - 65 bolsas - 92.86%  │ │
│  │ ☐ 09/01/2026 - María López - 70 bolsas - 100%   │ │
│  └──────────────────────────────────────────────────┘ │
│                                                        │
│  DETALLE DE PRODUCCIÓN SELECCIONADA:                  │
│  ─────────────────────────────────────────────────    │
│  Operario:    Juan Pérez                              │
│  Fecha:       09/01/2026                              │
│  Lote:        LOTE-2026-01-0001                       │
│  Cantidad:    65 bolsas                               │
│  Eficiencia:  92.86%                                  │
│  ⚠️ MERMA EXCESIVA DETECTADA                          │
│                                                        │
│  Observaciones del operario:                          │
│  "Fardo con mucha humedad"                            │
│                                                        │
│  Decisión:                                            │
│  ⚪ Aprobar   ⚪ Rechazar                              │
│                                                        │
│  Observaciones de validación:                         │
│  [____________________________________________]        │
│                                                        │
│         [CANCELAR]           [VALIDAR]                │
└────────────────────────────────────────────────────────┘
```

---

### CU-VENT-01: Registrar Venta

**Actor Principal:** Administrador  
**Objetivo:** Registrar una venta de bolsas de Napa  
**Precondiciones:** 
- Usuario autenticado con rol Administrador
- Hay stock de producto terminado
**Postcondiciones:** 
- Venta registrada
- Inventario descontado
- Se muestra costo de referencia

#### Flujo Principal

1. El administrador selecciona "Ventas > Nueva Venta"
2. El sistema muestra el formulario
3. El sistema muestra el stock disponible actual
4. El administrador ingresa:
   - Fecha de venta (default: hoy)
   - Cliente (selección o nuevo)
   - Cantidad a vender
   - Precio unitario
5. El sistema valida que haya stock suficiente
6. El sistema calcula automáticamente:
   - Precio total = Cantidad × Precio Unitario
   - Costo unitario de referencia (promedio de producciones recientes)
   - Margen = (Precio - Costo) / Precio × 100
7. El sistema muestra información de referencia:
   - Costo unitario estimado
   - Margen de ganancia
   - Alerta si precio < costo
8. El administrador selecciona:
   - Forma de pago
   - Estado de pago
9. El administrador puede agregar observaciones
10. El administrador confirma la venta
11. El sistema:
    - Registra la venta
    - Descuenta del inventario de producto terminado
    - Registra en kardex
    - Muestra confirmación con ID de venta

#### Flujos Alternativos

**5a. Stock insuficiente:**
- 5a1. Sistema muestra "Stock insuficiente. Disponible: X, Solicitado: Y"
- 5a2. No permite continuar
- 5a3. Termina el caso de uso

**7a. Precio por debajo del costo:**
- 7a1. Sistema muestra alerta "⚠️ ADVERTENCIA: Precio de venta es menor al costo"
- 7a2. Sistema solicita confirmación adicional
- 7a3. Si confirma, continúa con paso 8
- 7a4. Si cancela, regresa al paso 4

**7b. Margen muy bajo (<10%):**
- 7b1. Sistema muestra "⚠️ Margen bajo: X%"
- 7b2. Continúa con paso 8

#### Reglas de Negocio

- **RN-VENT-01**: Precio variable por venta
- **RN-VENT-02**: Mostrar costo de referencia
- **RN-VENT-04**: Validar stock
- **RN-VENT-05**: Descontar inventario

#### Interfaz

```
┌────────────────────────────────────────────────────────┐
│  REGISTRAR VENTA                                       │
├────────────────────────────────────────────────────────┤
│  Stock Disponible: 450 bolsas                          │
│                                                        │
│  Fecha:     [09/01/2026] [📅]                          │
│                                                        │
│  Cliente:   [Distribuidora Sur ▼] [+ Nuevo]           │
│                                                        │
│  Cantidad:  [100] bolsas                               │
│                                                        │
│  Precio Unitario: [S/ 25.00]                          │
│  Precio Total:    [S/ 2,500.00] (calculado)           │
│                                                        │
│  ┌──────────────────────────────────────────────────┐ │
│  │ 📊 INFORMACIÓN DE REFERENCIA                     │ │
│  │                                                  │ │
│  │ Costo Unitario:  S/ 18.50 (estimado)            │ │
│  │ Margen:          26.00% ✅                       │ │
│  │                                                  │ │
│  │ Ganancia Estimada: S/ 650.00                    │ │
│  └──────────────────────────────────────────────────┘ │
│                                                        │
│  Forma Pago:   [Efectivo ▼]                           │
│  Estado Pago:  [Pagado ▼]                             │
│                                                        │
│  Observaciones: [____________________________]        │
│                                                        │
│         [CANCELAR]           [REGISTRAR]              │
└────────────────────────────────────────────────────────┘
```

---

### CU-REP-01: Dashboard Principal

**Actor Principal:** Administrador  
**Objetivo:** Visualizar KPIs y estado general del negocio  
**Precondiciones:** Usuario autenticado con rol Administrador  
**Postcondiciones:** Datos actualizados visualizados

#### Flujo Principal

1. El administrador inicia sesión
2. El sistema carga automáticamente el dashboard
3. El sistema consulta y muestra:
   
   **Panel de Producción:**
   - Producción del día
   - Producción del mes
   - Eficiencia promedio
   - Producciones pendientes de validación
   
   **Panel de Inventario:**
   - Stock de fibra (con alerta si < mínimo)
   - Stock de bolsas plásticas (con alerta si < mínimo)
   - Stock de producto terminado
   
   **Panel de Ventas:**
   - Ventas del día
   - Ventas del mes
   - Ventas pendientes de pago
   - Ventas pendientes de entrega
   
   **Panel de Alertas:**
   - Lotes con merma excesiva
   - Producciones con bajo rendimiento
   - Inventarios bajos
   - Validaciones pendientes

4. El administrador puede hacer clic en cualquier KPI para ver detalle
5. El sistema actualiza datos cada 5 minutos automáticamente

#### Reglas de Negocio

- **RN-REP-01**: Dashboard con KPIs principales
- **RN-INSU-05**: Alertas de stock mínimo
- **RN-PROD-05**: Alertas de merma excesiva

#### Interfaz

```
┌────────────────────────────────────────────────────────────────┐
│  DASHBOARD - Sistema de Producción Napa                        │
├────────────────────────────────────────────────────────────────┤
│  Hoy: 09 de Enero, 2026                Usuario: Admin          │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  PRODUCCIÓN                   VENTAS                          │
│  ┌─────────────────────┐     ┌─────────────────────┐         │
│  │ Hoy:     135 bolsas │     │ Hoy:    S/ 3,250   │         │
│  │ Mes:     2,450 bols │     │ Mes:    S/ 58,400  │         │
│  │ Eficiencia: 96.5%   │     │ Pendientes: 3      │         │
│  └─────────────────────┘     └─────────────────────┘         │
│                                                                │
│  INVENTARIO                                                    │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │ Fibra:     850 kg    ⚠️ BAJO (mín: 100 kg)               │ │
│  │ Bolsas:    45.5 kg   ✅ NORMAL                           │ │
│  │ Producto:  450 bolsas ✅ NORMAL                          │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                                │
│  ⚠️ ALERTAS                                                    │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │ • 2 lotes con merma excesiva (ver detalle)               │ │
│  │ • 5 producciones pendientes de validación                │ │
│  │ • Stock de fibra por debajo del mínimo                   │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                                │
│  GRÁFICA: Producción vs Ventas (Últimos 7 días)               │
│  [Aquí iría un gráfico de líneas]                             │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

---

### CU-REP-02: Reporte de Mermas

**Actor Principal:** Administrador  
**Objetivo:** Analizar lotes con mermas excesivas  
**Precondiciones:** Usuario autenticado con rol Administrador  
**Postcondiciones:** Reporte generado y visualizado

#### Flujo Principal

1. El administrador selecciona "Reportes > Reporte de Mermas"
2. El sistema muestra filtros:
   - Rango de fechas (default: último mes)
   - Proveedor (opcional)
   - Operario (opcional)
3. El administrador aplica filtros
4. El sistema genera el reporte mostrando:
   - Código de lote
   - Fecha de compra
   - Proveedor
   - Peso neto del lote
   - Cantidad estimada
   - Cantidad producida real
   - Diferencia (merma en unidades)
   - Eficiencia %
   - Observaciones
5. El sistema calcula totales:
   - Total de lotes analizados
   - Total de lotes con merma excesiva
   - Porcentaje de lotes problemáticos
   - Pérdida estimada en unidades
6. El administrador puede:
   - Exportar a PDF
   - Exportar a Excel
   - Imprimir

#### Reglas de Negocio

- **RN-REP-01**: Filtrado por periodo
- **RN-REP-02**: Análisis de mermas

#### Ejemplo de Reporte

```
┌──────────────────────────────────────────────────────────────────────┐
│  REPORTE DE MERMAS                                                   │
│  Periodo: 01/12/2025 - 09/01/2026                                    │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  Código Lote    | Proveedor      | Estimado | Real | Efic.% | ⚠️  │
│  ──────────────────────────────────────────────────────────────────│
│  LOTE-2025-12-01| Fibras Norte   |    70    |  65  | 92.86% | ⚠️  │
│  LOTE-2025-12-05| Fibras Norte   |    70    |  62  | 88.57% | ⚠️  │
│  LOTE-2025-12-10| Proveedora Sur |    70    |  68  | 97.14% | ✅  │
│  LOTE-2025-12-15| Fibras Norte   |    70    |  58  | 82.86% | ⚠️  │
│  ...                                                                 │
│                                                                      │
│  RESUMEN:                                                            │
│  ───────────────────────────────────────────────────────────────    │
│  Total de lotes analizados:           25                            │
│  Lotes con merma excesiva:            8                             │
│  Porcentaje problemático:             32%                           │
│  Pérdida estimada total:              95 bolsas                     │
│  Eficiencia promedio:                 94.2%                         │
│                                                                      │
│  PROVEEDOR MÁS PROBLEMÁTICO: Fibras del Norte (5 lotes)             │
│                                                                      │
│  [EXPORTAR PDF]  [EXPORTAR EXCEL]  [IMPRIMIR]  [CERRAR]             │
└──────────────────────────────────────────────────────────────────────┘
```

---

### CU-RRHH-03: Generar Reporte de Nómina

**Actor Principal:** Administrador  
**Objetivo:** Calcular pagos por destajo de operarios  
**Precondiciones:** 
- Usuario autenticado con rol Administrador
- Existe producción validada en el periodo
**Postcondiciones:** Reporte de nómina generado

#### Flujo Principal

1. El administrador selecciona "RRHH > Reporte de Nómina"
2. El sistema muestra filtros:
   - Rango de fechas
   - Operario específico o "Todos"
3. El administrador aplica filtros
4. El sistema consulta todas las producciones con:
   - estado_validacion = 'aprobado'
   - fecha_produccion dentro del rango
5. El sistema calcula por operario:
   - Total de bolsas producidas (validadas)
   - Tarifa vigente en cada fecha
   - Total a pagar = Suma(Bolsas × Tarifa)
6. El sistema muestra:
   - Resumen por operario
   - Detalle día por día
   - Total general
7. El administrador puede:
   - Ver detalle de cada operario
   - Exportar a PDF/Excel
   - Marcar como pagado

#### Reglas de Negocio

- **RN-RRHH-01**: Pago por destajo
- **RN-RRHH-04**: Cálculo de nómina
- **RN-RRHH-05**: Histórico de tarifas

#### Ejemplo de Reporte

```
┌──────────────────────────────────────────────────────────────────────┐
│  REPORTE DE NÓMINA POR DESTAJO                                       │
│  Periodo: 01/01/2026 - 09/01/2026                                    │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  Operario          | Bolsas | Tarifa | Total a Pagar | Días Trab. │
│  ──────────────────────────────────────────────────────────────────│
│  Juan Pérez        |  450   | S/ 2.00|   S/ 900.00   |     7      │
│  María López       |  520   | S/ 2.00|   S/ 1,040.00 |     7      │
│  Carlos Ruiz       |  380   | S/ 1.80|   S/ 684.00   |     6      │
│                                                                      │
│  TOTAL:            | 1,350  |        |   S/ 2,624.00 |            │
│                                                                      │
│  ────────────────────────────────────────────────────────────────── │
│                                                                      │
│  DETALLE: Juan Pérez                                                 │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │ Fecha      | Bolsas | Tarifa | Subtotal  | Estado          │   │
│  │ ────────────────────────────────────────────────────────────│   │
│  │ 02/01/2026 |   65   | S/ 2.00| S/ 130.00 | Aprobado        │   │
│  │ 03/01/2026 |   70   | S/ 2.00| S/ 140.00 | Aprobado        │   │
│  │ 04/01/2026 |   68   | S/ 2.00| S/ 136.00 | Aprobado        │   │
│  │ ...                                                          │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                                                                      │
│  [EXPORTAR PDF]  [EXPORTAR EXCEL]  [MARCAR PAGADO]  [CERRAR]        │
└──────────────────────────────────────────────────────────────────────┘
```

---

## 4. MATRIZ DE TRAZABILIDAD

| Caso de Uso | Requisitos Relacionados | Reglas de Negocio | Prioridad |
|-------------|------------------------|-------------------|-----------|
| CU-AUTH-01 | RF-AUTH-01, RF-AUTH-02 | RN-SEG-02, RN-GEN-02 | CRÍTICA |
| CU-COMP-01 | RF-COMP-01 | RN-COMP-01 a RN-COMP-05 | ALTA |
| CU-PROD-01 | RF-PROD-01 | RN-PROD-01, RN-PROD-05, RN-INSU-04 | CRÍTICA |
| CU-PROD-02 | RF-PROD-01 | RN-RRHH-02, RN-RRHH-03, RN-PROD-07 | CRÍTICA |
| CU-VENT-01 | RF-VENT-01, RF-VENT-02 | RN-VENT-01 a RN-VENT-05 | ALTA |
| CU-REP-01 | RF-REP-01 | RN-REP-01 | ALTA |
| CU-REP-02 | RF-REP-02 | RN-REP-02 | ALTA |
| CU-RRHH-03 | RF-RRHH-02 | RN-RRHH-01, RN-RRHH-04 | ALTA |

---

## 5. ESCENARIOS DE PRUEBA

### Escenario 1: Flujo Completo de Producción

1. **SETUP**: Comprar lote de fibra (CU-COMP-01)
2. **PASO 1**: Operario registra producción (CU-PROD-01)
3. **PASO 2**: Supervisor valida y aprueba (CU-PROD-02)
4. **VERIFICAR**: 
   - Inventarios descontados correctamente
   - Producción suma para pago
   - Lote actualizado

### Escenario 2: Detección de Merma

1. **SETUP**: Lote con estimado = 70 bolsas
2. **PASO 1**: Registrar producción de 60 bolsas (< 95%)
3. **VERIFICAR**:
   - Flag merma_excesiva = TRUE
   - Alerta visible en dashboard
   - Observaciones obligatorias

### Escenario 3: Control de Stock

1. **SETUP**: Inventario bolsas = 1 kg
2. **PASO 1**: Intentar registrar producción de 100 bolsas (requiere 2 kg)
3. **VERIFICAR**:
   - Sistema bloquea el registro
   - Mensaje de error claro

---

**FIN DEL DOCUMENTO**
