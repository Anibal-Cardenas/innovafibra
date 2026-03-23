# REGLAS DE NEGOCIO
## Sistema de Gestión de Producción - Taller de Napa

**Versión:** 1.0  
**Fecha:** 09 de Enero, 2026

---

## 1. REGLAS GENERALES DEL SISTEMA (RN-GEN)

### RN-GEN-01: Unicidad de Identificadores
**Descripción:** Todos los lotes, usuarios, ventas y entregas deben tener identificadores únicos.

**Implementación:**
- Lotes: `LOTE-YYYY-MM-NNNN` (Ej: LOTE-2026-01-0001)
- Guías: `GUIA-YYYY-NNNN` (Ej: GUIA-2026-0001)
- Auto-incrementales en base de datos

### RN-GEN-02: Auditoría de Cambios
**Descripción:** Toda operación crítica debe registrar quién, cuándo y qué se modificó.

**Operaciones Auditables:**
- Creación/modificación de usuarios
- Registro de compras
- Registro de producción
- Registro de ventas
- Cambios en configuración del sistema
- Validación/rechazo de producción

### RN-GEN-03: Restricción de Eliminación
**Descripción:** Los registros no se eliminan físicamente, se marcan como inactivos.

**Campos Requeridos:**
- `estado`: activo/inactivo
- `fecha_eliminacion`
- `usuario_eliminacion`

---

## 2. REGLAS DEL MÓDULO DE COMPRAS (RN-COMP)

### RN-COMP-01: Validación de Pesos
**Descripción:** El peso neto nunca puede ser mayor al peso bruto.

**Validación:**
```
Si (Peso_Neto > Peso_Bruto)
    Entonces: Rechazar y mostrar error
```

**Mensaje de Error:** "El peso neto no puede ser mayor al peso bruto del fardo."

### RN-COMP-02: Valor por Defecto de Rendimiento
**Descripción:** El campo "Cantidad Estimada de Bolsas" debe inicializarse en 70.

**Condiciones:**
- Valor default: 70 bolsas
- Editable por el usuario
- Mínimo: 1 bolsa
- Máximo: 1000 bolsas

**Validación:**
```
Si (Cantidad_Estimada < 1 OR Cantidad_Estimada > 1000)
    Entonces: Rechazar con mensaje de error
```

### RN-COMP-03: Cálculo de Precio Unitario
**Descripción:** El sistema calcula automáticamente el precio por kilogramo.

**Fórmula:**
```
Precio_Por_Kg = Precio_Total_Compra / Peso_Neto
```

**Precisión:** 2 decimales

### RN-COMP-04: Generación de ID de Lote
**Descripción:** El ID de lote se genera automáticamente y no es editable.

**Formato:**
```
LOTE-[AÑO]-[MES]-[SECUENCIA]
Ejemplo: LOTE-2026-01-0001
```

**Secuencia:**
- Se reinicia cada mes
- 4 dígitos con ceros a la izquierda

### RN-COMP-05: Estado Inicial del Lote
**Descripción:** Todo lote nuevo se crea con estado "Disponible".

**Estados Posibles:**
- **Disponible**: Listo para ser usado en producción
- **En Proceso**: Se está usando actualmente
- **Agotado**: Completamente consumido
- **Merma Excesiva**: Marcado por bajo rendimiento

---

## 3. REGLAS DEL MÓDULO DE INSUMOS (RN-INSU)

### RN-INSU-01: Factor de Conversión Configurable
**Descripción:** El factor de conversión bolsa-peso debe ser configurable solo por el administrador.

**Configuración:**
- Valor por defecto: 0.02 kg/bolsa
- Rango permitido: 0.001 a 1.0 kg/bolsa
- Solo modificable por rol "Administrador"

**Validación:**
```
Si (Factor_Conversion < 0.001 OR Factor_Conversion > 1.0)
    Entonces: Rechazar cambio
```

### RN-INSU-02: Historial de Factor de Conversión
**Descripción:** Cada cambio del factor debe registrarse para auditoría.

**Registro:**
- Fecha del cambio
- Valor anterior
- Valor nuevo
- Usuario que realizó el cambio
- Motivo del cambio

### RN-INSU-03: Cálculo de Descuento de Inventario
**Descripción:** Al registrar producción, se descuenta automáticamente el peso equivalente de bolsas.

**Fórmula:**
```
Peso_A_Descontar = Cantidad_Bolsas_Producidas × Factor_Conversion_Vigente
Nuevo_Inventario_Bolsas = Inventario_Actual - Peso_A_Descontar
```

**Ejemplo:**
- Producción: 100 bolsas
- Factor: 0.02 kg/bolsa
- Descuento: 100 × 0.02 = 2 kg
- Si había 50 kg, quedan 48 kg

### RN-INSU-04: Validación de Stock Suficiente
**Descripción:** No se puede registrar producción si no hay suficiente inventario de bolsas.

**Validación:**
```
Peso_Requerido = Cantidad_A_Producir × Factor_Conversion

Si (Inventario_Actual_Bolsas < Peso_Requerido)
    Entonces: Bloquear registro
    Mostrar: "Stock insuficiente. Se requieren X kg, solo hay Y kg disponibles."
```

### RN-INSU-05: Alerta de Stock Mínimo
**Descripción:** El sistema debe alertar cuando el inventario de bolsas está bajo.

**Configuración:**
- Stock mínimo configurable (default: 10 kg)
- Alerta visual en dashboard
- Color de alerta: amarillo (por debajo del mínimo), rojo (crítico < 5kg)

---

## 4. REGLAS DEL MÓDULO DE PRODUCCIÓN (RN-PROD)

### RN-PROD-01: Uso de Lote Disponible
**Descripción:** Solo se pueden usar lotes en estado "Disponible" o "En Proceso".

**Validación:**
```
Si (Estado_Lote != "Disponible" AND Estado_Lote != "En Proceso")
    Entonces: No permitir selección
```

### RN-PROD-02: Cambio de Estado de Lote
**Descripción:** El estado del lote cambia según el consumo.

**Lógica:**
```
Producción_Acumulada = Suma de todas las producciones del lote

Si (Producción_Acumulada = 0)
    Estado = "Disponible"
Sino Si (Producción_Acumulada < Cantidad_Estimada)
    Estado = "En Proceso"
Sino Si (Producción_Acumulada >= Cantidad_Estimada)
    Estado = "Agotado"
```

### RN-PROD-03: Cálculo de Merma en Peso
**Descripción:** El sistema calcula la merma en kilogramos.

**Fórmula Simplificada:**
```
Peso_Teorico_Salida = Cantidad_Producida × Peso_Promedio_Por_Bolsa
Merma_Kg = Peso_Lote_Usado - Peso_Teorico_Salida
```

**Nota:** En una implementación más precisa, se debe pesar la producción real.

### RN-PROD-04: Cálculo de Eficiencia
**Descripción:** El sistema calcula la eficiencia comparando producción real vs estimada.

**Fórmula:**
```
Eficiencia_Porcentual = (Produccion_Real / Cantidad_Estimada_Lote) × 100
```

**Interpretación:**
- >= 95%: Eficiencia excelente (verde)
- 85% - 94%: Eficiencia normal (amarillo)
- < 85%: Eficiencia baja (rojo)

### RN-PROD-05: Detección de Merma Excesiva
**Descripción:** Alertar cuando la producción real es significativamente menor a la estimada.

**Configuración:**
- Tolerancia configurable (default: 5%)
- Editable solo por administrador

**Lógica:**
```
Umbral_Minimo = Cantidad_Estimada × (1 - Tolerancia_Porcentaje/100)

Si (Produccion_Real < Umbral_Minimo)
    Entonces: 
        Marcar registro con flag "Merma_Excesiva"
        Cambiar estado de lote a "Merma Excesiva"
        Enviar notificación al administrador
        Requerir observaciones obligatorias
```

**Ejemplo:**
- Estimado: 70 bolsas
- Tolerancia: 5%
- Umbral: 70 × 0.95 = 66.5 bolsas
- Si se producen < 66.5 bolsas → Alerta de merma excesiva

### RN-PROD-06: Producción Múltiple del Mismo Lote
**Descripción:** Un lote puede ser usado en múltiples registros de producción.

**Validación:**
```
Produccion_Acumulada = Suma de producciones anteriores del lote
Nueva_Produccion_Total = Produccion_Acumulada + Produccion_Nueva

Si (Nueva_Produccion_Total > Cantidad_Estimada × 1.1)
    Entonces: Mostrar advertencia (pero no bloquear)
    Mensaje: "La producción acumulada (X) supera significativamente lo estimado (Y). ¿Desea continuar?"
```

### RN-PROD-07: Descuento de Inventarios
**Descripción:** Al confirmar producción, se descuentan los inventarios.

**Proceso:**
1. Descontar fibra del lote (actualizar cantidad usada)
2. Descontar bolsas plásticas (kg equivalentes)
3. Incrementar inventario de producto terminado
4. Registrar movimientos en kardex

---

## 5. REGLAS DEL MÓDULO DE RRHH (RN-RRHH)

### RN-RRHH-01: Pago por Destajo
**Descripción:** Los operarios cobran por bolsa producida y validada.

**Fórmula:**
```
Pago_Operario = Cantidad_Bolsas_Validadas × Tarifa_Por_Bolsa
```

**Condiciones:**
- Solo se cuentan bolsas con estado "Validado"
- La tarifa es individual por operario
- Se puede modificar la tarifa (con registro de cambio)

### RN-RRHH-02: Validación de Calidad Obligatoria
**Descripción:** Toda producción debe ser validada por un supervisor antes de sumar al pago.

**Estados de Validación:**
- **Pendiente**: Recién registrada, no validada
- **Aprobada**: Supervisor aprueba calidad
- **Rechazada**: No cumple estándares de calidad

**Reglas:**
- Solo supervisores o administradores pueden validar
- Solo producción "Aprobada" suma para pago
- Producción "Rechazada" no se paga

**Validación:**
```
Si (Usuario_Rol != "Supervisor" AND Usuario_Rol != "Administrador")
    Entonces: No permitir validación
```

### RN-RRHH-03: Producción Rechazada
**Descripción:** Cuando se rechaza producción, no se descuentan los inventarios.

**Lógica:**
```
Si (Estado_Validacion = "Rechazada")
    Entonces:
        - NO descontar inventarios
        - NO incrementar producto terminado
        - NO sumar a pago del operario
        - Registrar motivo de rechazo (obligatorio)
```

### RN-RRHH-04: Cálculo de Nómina
**Descripción:** El sistema calcula el total a pagar por periodo.

**Por Operario:**
```
Total_Periodo = Suma(Cantidad_Bolsas_Validadas × Tarifa) 
                WHERE Fecha BETWEEN Fecha_Inicio AND Fecha_Fin
                AND Estado_Validacion = "Aprobada"
```

**Reporte Incluye:**
- Nombre del operario
- Días trabajados
- Total de bolsas validadas
- Tarifa vigente
- Total a pagar
- Detalle diario

### RN-RRHH-05: Histórico de Tarifas
**Descripción:** Los cambios de tarifa deben registrarse para cálculos retroactivos correctos.

**Registro:**
- Fecha de vigencia
- Tarifa anterior
- Tarifa nueva
- Usuario que autorizó el cambio

**Cálculo:**
```
Se debe usar la tarifa vigente en la fecha de producción, no la actual.
```

---

## 6. REGLAS DEL MÓDULO DE VENTAS (RN-VENT)

### RN-VENT-01: Precio Variable por Venta
**Descripción:** Cada venta puede tener un precio diferente, definido manualmente.

**Condiciones:**
- Precio unitario es obligatorio
- Precio total se calcula automáticamente: `Cantidad × Precio_Unitario`
- Se muestra costo unitario de referencia (no bloquea la venta)

### RN-VENT-02: Visualización de Costo de Referencia
**Descripción:** Al registrar venta, el sistema muestra el costo unitario calculado.

**Información Mostrada:**
```
Costo Unitario Estimado: $X.XX
Precio de Venta: $Y.YY
Margen: (Y-X)/Y × 100 = Z%
```

**Alertas:**
- Si Precio_Venta < Costo_Unitario → Mostrar advertencia "Venta por debajo del costo"
- Si Margen < 10% → Advertencia "Margen bajo"

### RN-VENT-03: Cálculo de Costo Unitario
**Descripción:** El costo se calcula por lote producido.

**Fórmula:**
```
Costo_Fibra_Por_Bolsa = (Precio_Lote / Peso_Neto_Lote) × (Peso_Neto_Lote / Produccion_Real)
Costo_Bolsa_Plastica = Factor_Conversion × Precio_Kg_Bolsas
Costo_Mano_Obra = Tarifa_Operario
Costo_Total_Unitario = Costo_Fibra + Costo_Bolsa_Plastica + Costo_Mano_Obra
```

**Ejemplo:**
- Lote costó $1000, pesó 100kg, produjo 65 bolsas
- Costo fibra/bolsa: (1000/100) × (100/65) = $15.38
- Bolsa plástica: 0.02 kg × $5/kg = $0.10
- Mano de obra: $2.00
- **Costo Total: $17.48 por bolsa**

### RN-VENT-04: Validación de Stock
**Descripción:** No se puede vender más de lo disponible en inventario.

**Validación:**
```
Si (Cantidad_Venta > Stock_Producto_Terminado)
    Entonces: Rechazar venta
    Mensaje: "Stock insuficiente. Disponible: X bolsas, Solicitado: Y bolsas"
```

### RN-VENT-05: Descuento de Inventario
**Descripción:** Al confirmar venta, se descuenta del inventario de producto terminado.

**Proceso:**
```
Nuevo_Stock = Stock_Actual - Cantidad_Vendida
Registrar movimiento en kardex
Actualizar fecha_ultima_venta
```

### RN-VENT-06: Estados de Venta
**Descripción:** Las ventas pueden tener diferentes estados de pago.

**Estados:**
- **Pendiente**: Venta registrada, no pagada
- **Pagado**: Cliente pagó completo
- **Crédito**: Venta a crédito (pendiente de cobro)
- **Cancelado**: Venta anulada

**Regla de Anulación:**
```
Si (Estado = "Cancelado")
    Entonces: Revertir descuento de inventario
```

---

## 7. REGLAS DEL MÓDULO DE LOGÍSTICA (RN-LOG)

### RN-LOG-01: Guía de Entrega
**Descripción:** Toda entrega debe tener una guía generada.

**Formato ID:**
```
GUIA-[AÑO]-[SECUENCIA]
Ejemplo: GUIA-2026-0001
```

**Campos Obligatorios:**
- Venta asociada
- Cliente
- Dirección
- Chofer
- Fecha de entrega

### RN-LOG-02: Cambio de Estado de Venta
**Descripción:** Al registrar entrega, se actualiza el estado de la venta.

**Lógica:**
```
Si (Guia_Entrega es generada y confirmada)
    Entonces: Estado_Venta = "Entregado"
    Registrar fecha_entrega
```

### RN-LOG-03: Firma de Recepción
**Descripción:** La guía debe incluir espacio para firma del cliente.

**Campos en Guía:**
- Nombre quien recibe
- DNI
- Firma
- Fecha y hora de recepción

---

## 8. REGLAS DE CONFIGURACIÓN DEL SISTEMA (RN-CONF)

### RN-CONF-01: Parámetros Configurables
**Descripción:** El administrador puede modificar parámetros del sistema.

**Parámetros:**
| Parámetro | Default | Descripción |
|-----------|---------|-------------|
| cantidad_estimada_default | 70 | Bolsas estimadas por fardo |
| factor_conversion_bolsas | 0.02 | kg por bolsa plástica |
| tolerancia_merma | 5 | % de tolerancia de merma |
| stock_minimo_bolsas | 10 | kg de stock mínimo de alerta |
| stock_minimo_fibra | 100 | kg de stock mínimo de alerta |
| margen_minimo_venta | 10 | % de margen mínimo sugerido |

### RN-CONF-02: Restricción de Modificación
**Descripción:** Solo el rol "Administrador" puede modificar configuraciones.

**Validación:**
```
Si (Usuario_Rol != "Administrador")
    Entonces: Ocultar opciones de configuración
```

### RN-CONF-03: Auditoría de Cambios
**Descripción:** Toda modificación de configuración se registra.

**Registro:**
- Parámetro modificado
- Valor anterior
- Valor nuevo
- Fecha y hora
- Usuario que realizó el cambio

---

## 9. REGLAS DE INVENTARIO Y KARDEX (RN-INV)

### RN-INV-01: Movimientos de Inventario
**Descripción:** Todo movimiento de inventario debe registrarse en kardex.

**Tipos de Movimiento:**
- **Entrada**: Compra de materia prima o insumos
- **Salida**: Consumo en producción o venta
- **Ajuste**: Corrección de inventario
- **Merma**: Pérdida identificada

**Campos:**
- Fecha y hora
- Tipo de movimiento
- Producto/Material
- Cantidad
- Unidad de medida
- Saldo anterior
- Saldo nuevo
- Referencia (ID de compra, producción, venta)
- Usuario

### RN-INV-02: Ajuste de Inventario
**Descripción:** Los ajustes requieren autorización y justificación.

**Validación:**
```
Si (Tipo_Movimiento = "Ajuste")
    Entonces: 
        Requiere autorización de Administrador
        Motivo es obligatorio
        Registrar en auditoría
```

### RN-INV-03: Inventario No Negativo
**Descripción:** El inventario nunca puede ser negativo.

**Validación:**
```
Si (Saldo_Nuevo < 0)
    Entonces: Rechazar transacción
    Mensaje: "Operación rechazada: inventario insuficiente"
```

### RN-INV-04: Valorización de Inventario
**Descripción:** El inventario se valoriza usando método PEPS (Primero en Entrar, Primero en Salir).

**Lógica:**
- Las compras más antiguas se consumen primero
- El costo se calcula basado en el lote consumido

---

## 10. REGLAS DE SEGURIDAD Y ACCESO (RN-SEG)

### RN-SEG-01: Roles y Permisos

**Rol Administrador:**
- Acceso total a todos los módulos
- Puede crear/editar/desactivar usuarios
- Puede modificar configuraciones
- Puede ver información financiera y costos
- Puede generar todos los reportes

**Rol Trabajador:**
- Puede registrar producción propia
- Puede consultar su producción y pagos
- NO puede ver costos ni información financiera
- NO puede acceder a configuraciones
- NO puede ver producción de otros operarios

### RN-SEG-02: Validación de Sesión
**Descripción:** La sesión expira por inactividad.

**Configuración:**
- Timeout: 30 minutos de inactividad
- Advertencia: 2 minutos antes del timeout
- Re-autenticación requerida

### RN-SEG-03: Integridad de Datos Críticos
**Descripción:** Ciertos datos no pueden ser modificados después de confirmados.

**Datos Inmutables:**
- ID de lote (después de creación)
- Fecha de transacciones confirmadas
- Registros validados de producción

**Excepciones:**
- El administrador puede hacer correcciones (con auditoría)

---

## 11. REGLAS DE REPORTES (RN-REP)

### RN-REP-01: Periodo de Consulta
**Descripción:** Los reportes deben permitir filtrar por rango de fechas.

**Validación:**
```
Si (Fecha_Fin < Fecha_Inicio)
    Entonces: Rechazar y mostrar error
```

**Rango Máximo:** 1 año (para evitar sobrecarga)

### RN-REP-02: Exportación de Reportes
**Descripción:** Los reportes pueden exportarse en diferentes formatos.

**Formatos Soportados:**
- PDF (para impresión)
- Excel (para análisis)
- CSV (para integración)

### RN-REP-03: Acceso a Reportes Financieros
**Descripción:** Solo el administrador puede ver reportes con información de costos.

**Reportes Restringidos:**
- Reporte de rentabilidad
- Análisis de costos
- Margen de ganancia por venta
- Costo unitario detallado

---

## 12. MATRIZ DE PRIORIDADES

| Código | Regla | Prioridad | Impacto en Negocio |
|--------|-------|-----------|-------------------|
| RN-PROD-05 | Detección de merma excesiva | CRÍTICA | Objetivo principal del sistema |
| RN-INSU-03 | Descuento automático de inventario | CRÍTICA | Control de inventario |
| RN-RRHH-02 | Validación de calidad obligatoria | ALTA | Control de calidad y pagos |
| RN-VENT-04 | Validación de stock | ALTA | Evitar sobreventa |
| RN-PROD-02 | Cambio de estado de lote | ALTA | Trazabilidad |
| RN-VENT-02 | Visualización de costo | MEDIA | Ayuda a decisiones |
| RN-LOG-01 | Guía de entrega | MEDIA | Control logístico |

---

**FIN DEL DOCUMENTO**
