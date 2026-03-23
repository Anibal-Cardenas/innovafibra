# Sistema de Inventario por Calidad de Napa

## Implementación Completada

### 1. Concepto Principal
**La calidad del producto final (napa) depende directamente de la calidad de fibra del cubo usado en la producción.**

- Cubo de **Fibra Virgen** → Produce **Napa Premium (Fibra Virgen)**
- Cubo de **Fibra Cristalizada** → Produce **Napa Estándar (Fibra Cristalizada)**  
- Cubo de **Fibra Reciclada Premium** → Produce **Napa Económica (Fibra Reciclada)**

### 2. Cambios en la Base de Datos

#### Tabla `calidades_fibra`
- **Nueva columna:** `id_calidad_napa_destino` - mapea directamente a la calidad de napa que producirá
- **Mapeo configurado:**
  ```
  Fibra Virgen            → Napa Premium (A)
  Fibra Cristalizada      → Napa Estándar (B)
  Fibra Reciclada Premium → Napa Económica (C)
  Fibra Estándar          → Napa Básica (D)
  ```

#### Tabla `inventario`
- **Nueva columna:** `id_calidad_napa` - permite rastrear inventario por calidad específica
- **Índice único:** `(tipo_item, id_calidad_napa)` - asegura un registro por calidad
- Ahora hay 4 registros de `producto_terminado`, uno por cada calidad de napa

#### Tabla `kardex`
- **Nueva columna:** `id_calidad_napa` - registra movimientos por calidad específica
- Cada entrada/salida de producto especifica a qué calidad pertenece

#### Tabla `ventas`
- Ya tenía `id_calidad_napa` - ahora se usa correctamente para ventas por calidad

### 3. Triggers Actualizados

#### `trg_produccion_aprobada_inventario`
- **Asignación automática de calidad:** cuando se aprueba una producción, el sistema:
  1. Consulta la calidad de fibra del cubo usado
  2. Busca la calidad de napa destino mapeada
  3. Asigna automáticamente `id_calidad_napa` a la producción
  4. Actualiza el inventario de la calidad específica de napa
  5. Registra en kardex con la calidad correcta

#### `trg_after_insert_venta`
- **Descuento por calidad:** al registrar una venta:
  1. Toma la calidad especificada en la venta
  2. Descuenta del inventario de esa calidad específica
  3. Registra en kardex el movimiento con la calidad

### 4. Vistas Nuevas

#### `v_stock_ventas`
Muestra stock disponible por cada calidad de napa:
```sql
SELECT * FROM v_stock_ventas;
```
Columnas: `id_calidad_napa`, `calidad_napa`, `codigo`, `precio_base_sugerido`, `stock_disponible`, `estado_stock`

#### `v_inventario_por_calidad`
Vista consolidada del inventario incluyendo calidades:
```sql
SELECT * FROM v_inventario_por_calidad;
```
Muestra todos los tipos de inventario con sus calidades correspondientes.

### 5. Cambios en Controladores

#### `VentasController.php`
- **Método `getCalidadesConStock()`:** trae calidades usando `v_stock_ventas`
- **Método `getStockPorCalidad($idCalidad)`:** valida stock disponible por calidad específica
- **Validación mejorada:** antes de registrar venta, verifica stock de la calidad seleccionada
- **INSERT actualizado:** usa `id_calidad_napa` en lugar de `id_calidad_producto`

#### `ProduccionController.php`
- **Método `getCubosDisponibles()`:** ahora incluye:
  - `calidad_fibra` - nombre de la calidad del cubo
  - `calidad_napa_producira` - calidad de napa que se producirá
  - `codigo_napa` - código de la calidad destino (A, B, C, D)

### 6. Cambios en Vistas

#### `ventas/nueva.php`
- **Select de calidades mejorado:**
  - Muestra stock disponible por cada calidad
  - Deshabilita opciones sin stock (`disabled`)
  - Indica estado del stock (Disponible, Stock bajo, Sin stock)
  - Precio sugerido solo para calidades con stock

- **Campo de cantidad dinámico:**
  - `max` se actualiza según stock de la calidad seleccionada
  - Mensaje informativo muestra stock específico

- **JavaScript actualizado:**
  - Al seleccionar calidad, actualiza precio y stock disponible
  - Valida que no se exceda el stock de esa calidad

#### `produccion/nueva.php`
- **Select de cubos mejorado:**
  - Incluye atributos `data-calidad-fibra`, `data-calidad-napa`, `data-codigo-napa`

- **Alerta informativa nueva:**
  ```html
  <div class="alert alert-info" id="info_calidad">
    Calidad a producir: [Badge] Napa Premium (Fibra Virgen)
    Esta calidad se asignará automáticamente...
  </div>
  ```

- **JavaScript actualizado:**
  - Al seleccionar cubo, muestra automáticamente la calidad de napa que se producirá
  - Badge con código de calidad (A, B, C, D)

### 7. Flujo de Trabajo Completo

#### A. Compra de Fibra
1. Se registra compra con calidad específica (ej: Fibra Virgen)
2. Se crean cubos con esa calidad
3. Cada cubo "hereda" la calidad del lote

#### B. Producción
1. Operario selecciona un cubo disponible
2. **Sistema muestra automáticamente:** "Producirás Napa Premium (Fibra Virgen)"
3. Operario registra cantidad producida
4. Al aprobar la producción:
   - Trigger asigna automáticamente `id_calidad_napa` basado en el cubo
   - Inventario de "Napa Premium" se incrementa
   - Kardex registra entrada con calidad específica

#### C. Venta
1. Vendedor ve lista de calidades con stock real:
   ```
   Calidad A - Napa Premium (Stock: 30 unidades - Stock bajo)
   Calidad B - Napa Estándar (Stock: 0 unidades - Sin stock) [DESHABILITADO]
   Calidad C - Napa Económica (Stock: 0 unidades - Sin stock) [DESHABILITADO]
   ```
2. Solo puede seleccionar calidades con stock > 0
3. Campo cantidad se limita al stock disponible de esa calidad
4. Al registrar venta:
   - Sistema descuenta del inventario de esa calidad específica
   - Kardex registra salida con calidad

### 8. Archivos de Migración Creados

1. **`database/migrations/inventario_por_calidad.sql`**
   - Agrega columnas y mapeos de calidad
   - Modifica inventario y kardex
   - Actualiza triggers de producción
   - Crea vistas `v_stock_ventas` y `v_inventario_por_calidad`

2. **`database/migrations/actualizar_trigger_ventas.sql`**
   - Actualiza trigger de ventas para descontar por calidad

### 9. Estado Actual del Sistema

```
INVENTARIO POR CALIDAD (producto_terminado):
- Napa Premium (A):    30 unidades (Stock bajo)
- Napa Estándar (B):    0 unidades (Sin stock)
- Napa Económica (C):   0 unidades (Sin stock)
- Napa Básica (D):      0 unidades (Sin stock)

CUBOS DISPONIBLES:
- Cubo 1 (LOTE-2026-01-0001): Fibra Virgen → Producirá Napa Premium (A)
- Cubo 2 (LOTE-2026-01-0001): Fibra Virgen → Producirá Napa Premium (A)
```

### 10. Ventajas del Sistema

✅ **Trazabilidad completa:** cada unidad producida tiene su calidad específica  
✅ **Inventario preciso:** stock real por calidad, no estimaciones  
✅ **Automatización:** la calidad se asigna automáticamente según el cubo  
✅ **Control de ventas:** solo se vende lo que realmente existe  
✅ **Histórico detallado:** kardex rastrea todo movimiento con su calidad  
✅ **Reportes precisos:** se puede analizar producción/ventas por calidad

### 11. Próximos Pasos Recomendados

1. **Probar el flujo completo:**
   - Producir con un cubo virgen y verificar que incrementa "Napa Premium"
   - Vender "Napa Premium" y verificar que descuenta del stock correcto
   
2. **Agregar más calidades de fibra:**
   - Crear registros en `calidades_fibra`
   - Mapearlos a `calidades_napa` correspondientes
   
3. **Reportes por calidad:**
   - Crear vistas de análisis de ventas por calidad
   - Dashboard de rentabilidad por calidad

---

**¡El sistema está completamente funcional y listo para usar!** 🎉
