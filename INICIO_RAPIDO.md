# 🎉 SISTEMA COMPLETO - GUÍA RÁPIDA DE INICIO

## ✅ Estado del Proyecto: FUNCIONAL

El sistema está **100% implementado** y listo para usar.

## 🚀 Inicio Rápido (5 minutos)

### 1. Base de Datos
```sql
-- En phpMyAdmin:
1. Crear base de datos: napa_produccion
2. Importar: database/schema.sql
```

### 2. Configuración
```
Archivo: config/database.php
- Host: localhost
- Usuario: root
- Contraseña: (vacía por defecto en XAMPP)
- Base de datos: napa_produccion
```

### 3. Acceso
```
URL: http://localhost/Napa/public
Usuario: admin
Contraseña: admin123
```

## 📋 Flujo de Trabajo Recomendado

### Primer Uso (Configuración Inicial)

1. **Login como Admin** (admin/admin123)

2. **Registrar Proveedores** (si no existen)
   - Ir a Configuración > Proveedores
   - Agregar proveedor de fibra
   - Agregar proveedor de bolsas

3. **Registrar Clientes**
   - Ir a Configuración > Clientes
   - Agregar al menos un cliente

4. **Crear Usuarios Trabajadores**
   - Ir a Configuración > Usuarios
   - Crear usuarios con rol "Trabajador"
   - Configurar tarifa por bolsa (ejemplo: S/ 0.50)

### Operación Diaria

#### Como Administrador:

**Paso 1: Comprar Materia Prima**
```
Compras > Nueva Compra Fibra
- Fecha: hoy
- Proveedor: Seleccionar
- Peso Bruto: 52 kg
- Peso Neto: 50 kg
- Precio Total: S/ 250.00
- Cantidad Estimada: 70 bolsas (default)
→ Sistema genera: LOTE-2024-12-0001
```

**Paso 2: Comprar Bolsas Plásticas**
```
Compras > Nueva Compra Bolsas
- Fecha: hoy
- Proveedor: Seleccionar
- Peso: 5 kg
- Precio Total: S/ 25.00
→ Sistema calcula: 5 kg = 250 bolsas (factor 0.02 kg/bolsa)
```

#### Como Trabajador:

**Paso 3: Registrar Producción**
```
Producción > Registrar Producción
- Fecha: hoy
- Lote: Seleccionar lote disponible
- Cantidad Producida: 65 bolsas
- Observaciones: (opcional)
→ Sistema detecta: 65/70 = 92.8% eficiencia → ALERTA MERMA
→ Estado: Pendiente de validación
```

#### Como Supervisor/Admin:

**Paso 4: Validar Producción**
```
Producción > Validar Producción
- Ver lista de pendientes
- Revisar eficiencia
- Clic en ✅ para aprobar o ❌ para rechazar
→ Si rechaza: Debe ingresar motivo
→ Si aprueba: Se actualiza inventario y se habilita pago
```

#### Como Administrador:

**Paso 5: Registrar Venta**
```
Ventas > Nueva Venta
- Fecha: hoy
- Cliente: Seleccionar
- Cantidad: 60 bolsas
- Precio Unitario: S/ 8.00
→ Sistema muestra:
  - Precio Total: S/ 480.00
  - Costo Unitario: S/ 5.50 (calculado automático)
  - Margen: S/ 150.00 (31.25%)
→ Sistema genera: GUIA-2024-12-0001
```

**Paso 6: Ver Reportes**
```
Reportes > Reporte de Mermas
- Ver lotes con eficiencia baja
- Identificar patrones

Reportes > Reporte de Nómina
- Seleccionar mes
- Ver monto a pagar por trabajador
- Solo cuenta producción aprobada
```

## 🎯 Casos de Uso Importantes

### Detección de Merma Excesiva

**Escenario**: Lote con baja eficiencia

```
Lote: LOTE-2024-12-0001
Estimado: 70 bolsas
Producido: 63 bolsas
Eficiencia: 90%

✅ Sistema detecta merma (< 95%)
✅ Marca lote como "merma_excesiva"
✅ Genera alerta en dashboard
✅ Exige observaciones al registrar
✅ Aparece en reporte de mermas
```

### Validación Rechazada

**Escenario**: Producción con problemas de calidad

```
Supervisor revisa producción
→ Calidad no aceptable
→ Clic en ❌ Rechazar
→ Ingresa motivo: "Bolsas con defectos en el sellado"

✅ Estado: Rechazado
✅ NO se paga al trabajador
✅ NO se suma a inventario
✅ Trabajador puede ver motivo
```

### Cálculo de Nómina

**Escenario**: Fin de mes

```
Reportes > Reporte de Nómina
Seleccionar: Diciembre 2024

Trabajador: Juan Pérez
- Tarifa: S/ 0.50 por bolsa
- Producción aprobada: 1,200 bolsas
- Producción rechazada: 50 bolsas
- TOTAL A PAGAR: S/ 600.00 (solo aprobadas)
```

## 🔧 Configuración Avanzada

### Cambiar Parámetros del Sistema

Editar: `config/constants.php`

```php
// Rendimiento por fardo
define('DEFAULT_CANTIDAD_ESTIMADA', 70);  // Cambiar según experiencia

// Peso de bolsa plástica
define('DEFAULT_FACTOR_CONVERSION', 0.02); // 20 gramos por bolsa

// Tolerancia de merma
define('DEFAULT_TOLERANCIA_MERMA', 5);     // 5% = alerta si < 95%

// Stock mínimo para alertas
define('DEFAULT_STOCK_MINIMO_BOLSAS', 10); // kg
define('DEFAULT_STOCK_MINIMO_FIBRA', 100); // kg
```

### Crear Usuarios Adicionales

**Administrador**:
```sql
INSERT INTO usuarios (username, password, nombre_completo, rol, estado) 
VALUES ('nuevo_admin', '$2y$10$hasheado', 'Nombre Completo', 'administrador', 'activo');
```

**Supervisor**:
```sql
INSERT INTO usuarios (username, password, nombre_completo, rol, estado) 
VALUES ('supervisor1', '$2y$10$hasheado', 'Nombre Supervisor', 'supervisor', 'activo');
```

**Trabajador**:
```sql
INSERT INTO usuarios (username, password, nombre_completo, rol, tarifa_por_bolsa, estado) 
VALUES ('trabajador1', '$2y$10$hasheado', 'Nombre Trabajador', 'trabajador', 0.50, 'activo');
```

## 📊 Indicadores Clave (KPIs)

### Dashboard Muestra:

1. **Producción Hoy**: Total de bolsas producidas hoy
2. **Producción Mes**: Total del mes actual
3. **Ventas Hoy**: Ingresos del día
4. **Eficiencia Promedio**: % de eficiencia del mes

### Alertas Automáticas:

- 🔴 **Lotes con merma excesiva**
- 🟡 **Producciones pendientes de validación**
- 🟠 **Stock bajo de materiales**

## 🐛 Solución de Problemas Comunes

### Error: "No se puede conectar a la base de datos"
```
Solución:
1. Verificar que XAMPP esté corriendo
2. MySQL debe estar iniciado (luz verde)
3. Verificar config/database.php
```

### Error: "Página no encontrada"
```
Solución:
1. URL correcta: http://localhost/Napa/public
2. Verificar que .htaccess existe en /public
3. Verificar mod_rewrite activado en Apache
```

### Los triggers no funcionan
```
Solución:
1. Ejecutar schema.sql completo
2. Verificar en phpMyAdmin que los triggers existan
3. Tabla lotes_fibra > pestaña "Triggers"
```

### No se actualizan los inventarios
```
Posible causa: Triggers no ejecutados
Solución:
1. Revisar en tabla 'kardex' si hay movimientos
2. Si no hay, ejecutar de nuevo los triggers en schema.sql
```

## 📞 Soporte

Para problemas técnicos o consultas:
- Revisar documentación en carpeta `/docs`
- Ver casos de uso en `04_CASOS_DE_USO.md`
- Revisar reglas de negocio en `02_REGLAS_NEGOCIO.md`

---

## ✨ Características Destacadas

✅ **Detección automática de mermas**
✅ **Validación de calidad obligatoria**
✅ **Pago solo por producción aprobada**
✅ **Trazabilidad completa por lote**
✅ **Visualización de costos en ventas**
✅ **Reportes ejecutivos listos para imprimir**
✅ **Inventario automático con triggers**
✅ **Sistema de roles y permisos**
✅ **Auditoría de todas las acciones**

¡El sistema está listo para producción! 🚀
