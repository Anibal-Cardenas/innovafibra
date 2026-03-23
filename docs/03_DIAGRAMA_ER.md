# DIAGRAMA ENTIDAD-RELACIÓN (ER)
## Sistema de Gestión de Producción - Taller de Napa

**Versión:** 1.0  
**Fecha:** 09 de Enero, 2026

---

## 1. DIAGRAMA CONCEPTUAL

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    SISTEMA DE GESTIÓN DE PRODUCCIÓN NAPA                    │
└─────────────────────────────────────────────────────────────────────────────┘

┌──────────────────┐         ┌──────────────────┐         ┌──────────────────┐
│    USUARIOS      │         │   PROVEEDORES    │         │    CLIENTES      │
├──────────────────┤         ├──────────────────┤         ├──────────────────┤
│ PK id_usuario    │         │ PK id_proveedor  │         │ PK id_cliente    │
│    username      │         │    nombre        │         │    nombre        │
│    password_hash │         │    ruc           │         │    ruc           │
│    nombre_comp.. │         │    tipo_provee.. │         │    direccion     │
│    rol           │         │    estado        │         │    telefono      │
│    tarifa_por..  │         └──────────────────┘         │    estado        │
│    estado        │                 │                     └──────────────────┘
└──────────────────┘                 │                              │
         │                           │                              │
         │ crea/valida               │ provee                       │ compra
         │                           │                              │
         ▼                           ▼                              ▼
┌──────────────────┐         ┌──────────────────┐         ┌──────────────────┐
│   PRODUCCIONES   │◄────────│  LOTES_FIBRA     │         │     VENTAS       │
├──────────────────┤ usa     ├──────────────────┤         ├──────────────────┤
│ PK id_produccion │         │ PK id_lote       │         │ PK id_venta      │
│ FK id_lote_fibra │         │    codigo_lote   │         │ FK id_cliente    │
│ FK id_operario   │         │ FK id_proveedor  │         │    cantidad_vend │
│ FK id_supervisor │         │    peso_bruto    │         │    precio_unit.. │
│    cantidad_prod │         │    peso_neto     │         │    precio_total  │
│    peso_bolsas.. │         │    precio_total  │         │    estado_pago   │
│    eficiencia_%  │         │    cant_estima.. │         │    estado_entre. │
│    flag_merma_e. │         │    cant_produci. │         └──────────────────┘
│    estado_valid. │         │    estado        │                  │
└──────────────────┘         └──────────────────┘                  │
         │                           │                              │
         │                           │                              │ genera
         │                    ┌──────┴──────┐                      │
         │                    │             │                       ▼
         │                    ▼             ▼              ┌──────────────────┐
         │           ┌─────────────┐ ┌─────────────┐      │    ENTREGAS      │
         │           │ COMPRAS     │ │ COMPRAS     │      ├──────────────────┤
         │           │ BOLSAS      │ │ (otras)     │      │ PK id_entrega    │
         │           ├─────────────┤ └─────────────┘      │ FK id_venta      │
         │           │ PK id_comp..│                       │ FK id_chofer     │
         │           │ FK id_prov..│                       │    codigo_guia   │
         │           │    peso_kg  │                       │    fecha_entrega │
         │           │    precio_..│                       │    direccion_e.. │
         │           └─────────────┘                       └──────────────────┘
         │                    │                                     │
         │                    │                                     │
         │                    └──────┬──────────────────────────────┘
         │                           │                              
         ▼                           ▼                              
┌──────────────────────────────────────────────────────────────────┐
│                          INVENTARIO                               │
├──────────────────────────────────────────────────────────────────┤
│ PK id_inventario                                                  │
│    tipo_item (fibra | bolsas_plasticas | producto_terminado)     │
│    cantidad                                                       │
│    stock_minimo                                                   │
└──────────────────────────────────────────────────────────────────┘
                              │
                              │ registra movimientos
                              ▼
                     ┌─────────────────┐
                     │     KARDEX      │
                     ├─────────────────┤
                     │ PK id_kardex    │
                     │    tipo_item    │
                     │    tipo_movim.. │
                     │    cantidad     │
                     │    saldo_ante.. │
                     │    saldo_nuevo  │
                     │    referencia_..│
                     └─────────────────┘
```

---

## 2. TABLAS DEL SISTEMA

### 2.1 Módulo de Seguridad

#### USUARIOS
| Campo | Tipo | Constraints | Descripción |
|-------|------|-------------|-------------|
| **id_usuario** | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| username | VARCHAR(50) | UNIQUE, NOT NULL | Usuario para login |
| password_hash | VARCHAR(255) | NOT NULL | Contraseña encriptada |
| nombre_completo | VARCHAR(150) | NOT NULL | Nombre del usuario |
| dni | VARCHAR(20) | NULL | Documento de identidad |
| email | VARCHAR(100) | NULL | Correo electrónico |
| rol | ENUM | NOT NULL | administrador, trabajador, supervisor |
| tarifa_por_bolsa | DECIMAL(10,2) | NULL | Para operarios |
| fecha_ingreso | DATE | NULL | Fecha de ingreso |
| estado | ENUM | NOT NULL | activo, inactivo |

**Relaciones:**
- 1:N con `producciones` (como operario)
- 1:N con `producciones` (como supervisor)
- 1:N con todas las tablas (como usuario_creacion)

---

#### HISTORIAL_TARIFAS
| Campo | Tipo | Constraints | Descripción |
|-------|------|-------------|-------------|
| **id_historial_tarifa** | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| id_usuario | INT UNSIGNED | FK → usuarios | Usuario afectado |
| tarifa_anterior | DECIMAL(10,2) | NOT NULL | Tarifa previa |
| tarifa_nueva | DECIMAL(10,2) | NOT NULL | Nueva tarifa |
| fecha_cambio | TIMESTAMP | NOT NULL | Cuándo se cambió |
| usuario_autorizo | INT UNSIGNED | FK → usuarios | Quien autorizó |
| motivo | VARCHAR(255) | NULL | Razón del cambio |

---

#### AUDITORIA
| Campo | Tipo | Constraints | Descripción |
|-------|------|-------------|-------------|
| **id_auditoria** | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| id_usuario | INT UNSIGNED | FK → usuarios | Usuario que ejecutó |
| tabla_afectada | VARCHAR(100) | NOT NULL | Tabla modificada |
| id_registro | INT UNSIGNED | NULL | ID del registro |
| accion | ENUM | NOT NULL | INSERT, UPDATE, DELETE, LOGIN, LOGOUT |
| descripcion | TEXT | NULL | Descripción de la acción |
| datos_anteriores | JSON | NULL | Estado previo |
| datos_nuevos | JSON | NULL | Estado nuevo |
| ip_address | VARCHAR(45) | NULL | IP del usuario |
| fecha_accion | TIMESTAMP | NOT NULL | Fecha y hora |

---

### 2.2 Módulo de Proveedores

#### PROVEEDORES
| Campo | Tipo | Constraints | Descripción |
|-------|------|-------------|-------------|
| **id_proveedor** | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| nombre | VARCHAR(200) | NOT NULL | Nombre del proveedor |
| ruc | VARCHAR(20) | NULL | RUC/Identificación fiscal |
| direccion | VARCHAR(255) | NULL | Dirección |
| telefono | VARCHAR(20) | NULL | Teléfono de contacto |
| email | VARCHAR(100) | NULL | Correo electrónico |
| contacto_principal | VARCHAR(150) | NULL | Persona de contacto |
| tipo_proveedor | ENUM | NOT NULL | fibra, bolsas, otros |
| estado | ENUM | NOT NULL | activo, inactivo |

**Relaciones:**
- 1:N con `lotes_fibra`
- 1:N con `compras_bolsas`

---

### 2.3 Módulo de Compras (Materia Prima)

#### LOTES_FIBRA
| Campo | Tipo | Constraints | Descripción |
|-------|------|-------------|-------------|
| **id_lote** | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| codigo_lote | VARCHAR(50) | UNIQUE, NOT NULL | LOTE-YYYY-MM-NNNN |
| fecha_compra | DATE | NOT NULL | Fecha de compra |
| id_proveedor | INT UNSIGNED | FK → proveedores | Proveedor |
| peso_bruto | DECIMAL(10,2) | NOT NULL | Peso bruto en kg |
| peso_neto | DECIMAL(10,2) | NOT NULL | Peso neto en kg |
| precio_total | DECIMAL(12,2) | NOT NULL | Costo total |
| precio_por_kg | DECIMAL(10,2) | NOT NULL | Calculado |
| cantidad_estimada_bolsas | INT UNSIGNED | NOT NULL | Default: 70 |
| rendimiento_estimado | DECIMAL(10,4) | NOT NULL | Bolsas por kg |
| cantidad_producida_real | INT UNSIGNED | NOT NULL | Acumulado |
| estado | ENUM | NOT NULL | disponible, en_proceso, agotado, merma_excesiva |
| observaciones | TEXT | NULL | Notas |

**Relaciones:**
- N:1 con `proveedores`
- 1:N con `producciones`

**Constraint:** `peso_neto <= peso_bruto`

---

### 2.4 Módulo de Insumos Secundarios

#### COMPRAS_BOLSAS
| Campo | Tipo | Constraints | Descripción |
|-------|------|-------------|-------------|
| **id_compra_bolsa** | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| fecha_compra | DATE | NOT NULL | Fecha de compra |
| id_proveedor | INT UNSIGNED | FK → proveedores | Proveedor |
| peso_kg | DECIMAL(10,2) | NOT NULL | Peso en kilogramos |
| precio_total | DECIMAL(12,2) | NOT NULL | Costo total |
| precio_por_kg | DECIMAL(10,2) | NOT NULL | Precio unitario |
| tipo_bolsa | VARCHAR(100) | NULL | Descripción |
| observaciones | TEXT | NULL | Notas |

**Relaciones:**
- N:1 con `proveedores`

---

### 2.5 Módulo de Producción

#### PRODUCCIONES
| Campo | Tipo | Constraints | Descripción |
|-------|------|-------------|-------------|
| **id_produccion** | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| fecha_produccion | DATE | NOT NULL | Fecha de producción |
| id_lote_fibra | INT UNSIGNED | FK → lotes_fibra | Lote usado |
| id_operario | INT UNSIGNED | FK → usuarios | Operario |
| cantidad_producida | INT UNSIGNED | NOT NULL | Bolsas producidas |
| peso_bolsas_consumido | DECIMAL(10,2) | NOT NULL | Kg de bolsas usadas |
| eficiencia_porcentual | DECIMAL(5,2) | NULL | % eficiencia |
| flag_merma_excesiva | BOOLEAN | NOT NULL | Indicador de merma |
| estado_validacion | ENUM | NOT NULL | pendiente, aprobado, rechazado |
| id_supervisor | INT UNSIGNED | FK → usuarios | Quien valida |
| fecha_validacion | DATETIME | NULL | Cuándo se validó |
| observaciones_validacion | TEXT | NULL | Comentarios |
| observaciones | TEXT | NULL | Notas generales |

**Relaciones:**
- N:1 con `lotes_fibra`
- N:1 con `usuarios` (operario)
- N:1 con `usuarios` (supervisor)

**Triggers:**
- Descuenta inventarios al aprobar
- Actualiza producción acumulada del lote
- Calcula eficiencia automáticamente

---

### 2.6 Módulo de Clientes

#### CLIENTES
| Campo | Tipo | Constraints | Descripción |
|-------|------|-------------|-------------|
| **id_cliente** | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| nombre | VARCHAR(200) | NOT NULL | Nombre del cliente |
| ruc | VARCHAR(20) | NULL | RUC/Identificación |
| direccion | VARCHAR(255) | NULL | Dirección |
| telefono | VARCHAR(20) | NULL | Teléfono |
| email | VARCHAR(100) | NULL | Correo |
| contacto_principal | VARCHAR(150) | NULL | Persona contacto |
| limite_credito | DECIMAL(12,2) | NULL | Límite de crédito |
| estado | ENUM | NOT NULL | activo, inactivo |

**Relaciones:**
- 1:N con `ventas`

---

### 2.7 Módulo de Ventas

#### VENTAS
| Campo | Tipo | Constraints | Descripción |
|-------|------|-------------|-------------|
| **id_venta** | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| fecha_venta | DATE | NOT NULL | Fecha de venta |
| id_cliente | INT UNSIGNED | FK → clientes | Cliente |
| cantidad_vendida | INT UNSIGNED | NOT NULL | Bolsas vendidas |
| precio_unitario | DECIMAL(10,2) | NOT NULL | Precio por bolsa |
| precio_total | DECIMAL(12,2) | NOT NULL | Total calculado |
| costo_unitario_referencia | DECIMAL(10,2) | NULL | Costo del sistema |
| margen_porcentual | DECIMAL(5,2) | NULL | % margen |
| forma_pago | ENUM | NOT NULL | efectivo, transferencia, cheque, credito |
| estado_pago | ENUM | NOT NULL | pendiente, pagado, credito, cancelado |
| estado_entrega | ENUM | NOT NULL | pendiente, entregado |
| fecha_pago | DATE | NULL | Cuándo pagó |
| observaciones | TEXT | NULL | Notas |

**Relaciones:**
- N:1 con `clientes`
- 1:1 con `entregas`

**Triggers:**
- Descuenta inventario de producto terminado
- Registra en kardex

---

### 2.8 Módulo de Logística

#### CHOFERES
| Campo | Tipo | Constraints | Descripción |
|-------|------|-------------|-------------|
| **id_chofer** | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| nombre_completo | VARCHAR(150) | NOT NULL | Nombre del chofer |
| dni | VARCHAR(20) | NULL | DNI |
| licencia | VARCHAR(50) | NULL | Licencia de conducir |
| telefono | VARCHAR(20) | NULL | Teléfono |
| vehiculo | VARCHAR(100) | NULL | Placa/vehículo |
| estado | ENUM | NOT NULL | activo, inactivo |

**Relaciones:**
- 1:N con `entregas`

---

#### ENTREGAS
| Campo | Tipo | Constraints | Descripción |
|-------|------|-------------|-------------|
| **id_entrega** | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| codigo_guia | VARCHAR(50) | UNIQUE, NOT NULL | GUIA-YYYY-NNNN |
| id_venta | INT UNSIGNED | FK → ventas | Venta asociada |
| id_chofer | INT UNSIGNED | FK → choferes | Chofer |
| fecha_entrega | DATE | NOT NULL | Fecha de entrega |
| hora_salida | TIME | NULL | Hora de salida |
| hora_llegada | TIME | NULL | Hora de llegada |
| direccion_entrega | VARCHAR(255) | NOT NULL | Dirección |
| nombre_receptor | VARCHAR(150) | NULL | Quien recibe |
| dni_receptor | VARCHAR(20) | NULL | DNI receptor |
| firma_recibido | BOOLEAN | NOT NULL | Confirmación |
| observaciones | TEXT | NULL | Notas |

**Relaciones:**
- N:1 con `ventas`
- N:1 con `choferes`

**Triggers:**
- Actualiza estado_entrega de venta

---

### 2.9 Módulo de Inventario

#### INVENTARIO
| Campo | Tipo | Constraints | Descripción |
|-------|------|-------------|-------------|
| **id_inventario** | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| tipo_item | ENUM | UNIQUE, NOT NULL | fibra, bolsas_plasticas, producto_terminado |
| cantidad | DECIMAL(12,2) | NOT NULL | Cantidad actual |
| unidad_medida | VARCHAR(20) | NOT NULL | kg, unidades |
| stock_minimo | DECIMAL(12,2) | NULL | Nivel mínimo |
| fecha_ultima_actualizacion | TIMESTAMP | NOT NULL | Última actualización |

**Registros Fijos:**
- fibra (kg)
- bolsas_plasticas (kg)
- producto_terminado (unidades)

---

#### KARDEX
| Campo | Tipo | Constraints | Descripción |
|-------|------|-------------|-------------|
| **id_kardex** | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| fecha_movimiento | DATETIME | NOT NULL | Fecha y hora |
| tipo_item | ENUM | NOT NULL | fibra, bolsas_plasticas, producto_terminado |
| tipo_movimiento | ENUM | NOT NULL | entrada, salida, ajuste, merma |
| cantidad | DECIMAL(12,2) | NOT NULL | Cantidad movida |
| unidad_medida | VARCHAR(20) | NOT NULL | kg, unidades |
| saldo_anterior | DECIMAL(12,2) | NOT NULL | Saldo previo |
| saldo_nuevo | DECIMAL(12,2) | NOT NULL | Nuevo saldo |
| referencia_tipo | VARCHAR(50) | NULL | Tipo de operación |
| referencia_id | INT UNSIGNED | NULL | ID del registro |
| observaciones | VARCHAR(255) | NULL | Notas |
| usuario_registro | INT UNSIGNED | FK → usuarios | Usuario |

**Tipos de Referencia:**
- compra_fibra
- compra_bolsa
- produccion
- venta
- ajuste

---

### 2.10 Módulo de Configuración

#### CONFIGURACION_SISTEMA
| Campo | Tipo | Constraints | Descripción |
|-------|------|-------------|-------------|
| **id_config** | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| parametro | VARCHAR(100) | UNIQUE, NOT NULL | Nombre del parámetro |
| valor | VARCHAR(255) | NOT NULL | Valor actual |
| tipo_dato | ENUM | NOT NULL | entero, decimal, texto, boolean |
| descripcion | TEXT | NULL | Descripción |
| fecha_modificacion | TIMESTAMP | NULL | Última modificación |
| usuario_modificacion | INT UNSIGNED | FK → usuarios | Quien modificó |

**Parámetros Configurables:**
- cantidad_estimada_default: 70
- factor_conversion_bolsas: 0.02
- tolerancia_merma: 5
- stock_minimo_bolsas: 10
- stock_minimo_fibra: 100
- margen_minimo_venta: 10
- timeout_sesion: 30

---

#### HISTORIAL_CONFIGURACION
| Campo | Tipo | Constraints | Descripción |
|-------|------|-------------|-------------|
| **id_historial_config** | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| parametro | VARCHAR(100) | NOT NULL | Parámetro modificado |
| valor_anterior | VARCHAR(255) | NOT NULL | Valor previo |
| valor_nuevo | VARCHAR(255) | NOT NULL | Nuevo valor |
| fecha_cambio | TIMESTAMP | NOT NULL | Cuándo |
| usuario_cambio | INT UNSIGNED | FK → usuarios | Quién |
| motivo | VARCHAR(255) | NULL | Por qué |

---

## 3. RELACIONES PRINCIPALES

### 3.1 Diagrama de Relaciones por Cardinalidad

```
USUARIOS (1) ──────────── (N) PRODUCCIONES
    │                           │
    │ operario                  │ usa
    │                           │
    └── (1) ─────────────── (N) ┘
         supervisor              │
                                 │
PROVEEDORES (1) ──────── (N) LOTES_FIBRA
    │                           │
    │                           │ se produce en
    │                           │
    └── (1) ─────────────── (N) PRODUCCIONES
         
PROVEEDORES (1) ──────── (N) COMPRAS_BOLSAS

CLIENTES (1) ───────────── (N) VENTAS
                                │
                                │ genera
                                │
                           (1) ENTREGAS (N) ──── (1) CHOFERES

LOTES_FIBRA (1) ────────── (N) PRODUCCIONES

PRODUCCIONES ──┬──► INVENTARIO (actualiza)
COMPRAS ───────┤
VENTAS ────────┘

KARDEX ◄────────── TODAS LAS TRANSACCIONES
```

### 3.2 Claves Foráneas (Foreign Keys)

| Tabla Hijo | Campo FK | Tabla Padre | Campo PK | ON DELETE | ON UPDATE |
|------------|----------|-------------|----------|-----------|-----------|
| lotes_fibra | id_proveedor | proveedores | id_proveedor | RESTRICT | CASCADE |
| compras_bolsas | id_proveedor | proveedores | id_proveedor | RESTRICT | CASCADE |
| producciones | id_lote_fibra | lotes_fibra | id_lote | RESTRICT | CASCADE |
| producciones | id_operario | usuarios | id_usuario | RESTRICT | CASCADE |
| producciones | id_supervisor | usuarios | id_usuario | SET NULL | CASCADE |
| ventas | id_cliente | clientes | id_cliente | RESTRICT | CASCADE |
| entregas | id_venta | ventas | id_venta | RESTRICT | CASCADE |
| entregas | id_chofer | choferes | id_chofer | RESTRICT | CASCADE |

---

## 4. ÍNDICES PARA OPTIMIZACIÓN

### 4.1 Índices Principales

```sql
-- Búsquedas frecuentes
idx_lotes_fecha_estado ON lotes_fibra(fecha_compra, estado)
idx_prod_fecha_operario ON producciones(fecha_produccion, id_operario)
idx_ventas_fecha_cliente ON ventas(fecha_venta, id_cliente)
idx_kardex_fecha_item ON kardex(fecha_movimiento, tipo_item)

-- Índices UNIQUE para integridad
codigo_lote_UNIQUE ON lotes_fibra(codigo_lote)
codigo_guia_UNIQUE ON entregas(codigo_guia)
username_UNIQUE ON usuarios(username)
parametro_UNIQUE ON configuracion_sistema(parametro)
```

---

## 5. VISTAS DEL SISTEMA

### 5.1 v_resumen_lotes
Resumen de producción y eficiencia por lote.

**Campos:**
- codigo_lote
- proveedor
- peso_neto
- cantidad_estimada
- cantidad_producida_real
- eficiencia_porcentual
- tiene_merma_excesiva

---

### 5.2 v_produccion_validada
Producción validada por operario para cálculo de nómina.

**Campos:**
- operario
- fecha
- bolsas_aprobadas
- tarifa_por_bolsa
- monto_pagar

---

### 5.3 v_estado_inventario
Estado actual del inventario con alertas.

**Campos:**
- tipo_item
- cantidad
- stock_minimo
- estado_alerta (CRÍTICO, BAJO, NORMAL)

---

## 6. TRIGGERS AUTOMATIZADOS

### 6.1 Triggers de Inventario

| Trigger | Tabla | Evento | Acción |
|---------|-------|--------|--------|
| trg_after_insert_lote_fibra | lotes_fibra | AFTER INSERT | Incrementa inventario fibra |
| trg_after_insert_compra_bolsas | compras_bolsas | AFTER INSERT | Incrementa inventario bolsas |
| trg_after_insert_produccion | producciones | AFTER INSERT | Descuenta fibra y bolsas, incrementa producto |
| trg_after_insert_venta | ventas | AFTER INSERT | Descuenta producto terminado |

### 6.2 Triggers de Actualización

| Trigger | Tabla | Evento | Acción |
|---------|-------|--------|--------|
| trg_after_update_lote_produccion | lotes_fibra | AFTER UPDATE | Actualiza estado del lote |
| trg_after_insert_entrega | entregas | AFTER INSERT | Marca venta como entregada |

---

## 7. STORED PROCEDURES

### 7.1 sp_generar_codigo_lote()
Genera código único para lote: `LOTE-YYYY-MM-NNNN`

### 7.2 sp_generar_codigo_guia()
Genera código único para guía: `GUIA-YYYY-NNNN`

### 7.3 sp_calcular_costo_unitario(id_produccion)
Calcula el costo unitario de producción incluyendo:
- Costo de fibra
- Costo de bolsas plásticas
- Costo de mano de obra

---

## 8. NORMALIZACIÓN

**Forma Normal:** 3NF (Tercera Forma Normal)

**Características:**
- ✅ Todos los campos son atómicos (1NF)
- ✅ No hay dependencias parciales (2NF)
- ✅ No hay dependencias transitivas (3NF)
- ✅ Claves primarias en todas las tablas
- ✅ Claves foráneas con integridad referencial
- ✅ Índices en campos de búsqueda frecuente

---

## 9. DICCIONARIO DE DATOS RESUMIDO

| Tabla | Registros Estimados | Tipo | Criticidad |
|-------|---------------------|------|------------|
| usuarios | 10-50 | Maestro | ALTA |
| proveedores | 5-20 | Maestro | MEDIA |
| clientes | 20-100 | Maestro | MEDIA |
| lotes_fibra | 100-500/año | Transaccional | ALTA |
| compras_bolsas | 50-200/año | Transaccional | MEDIA |
| producciones | 300-1000/año | Transaccional | CRÍTICA |
| ventas | 200-800/año | Transaccional | ALTA |
| entregas | 200-800/año | Transaccional | MEDIA |
| kardex | 1000-5000/año | Histórico | ALTA |
| inventario | 3 (fijo) | Maestro | CRÍTICA |
| configuracion_sistema | 10-20 | Configuración | ALTA |

---

**FIN DEL DOCUMENTO**
