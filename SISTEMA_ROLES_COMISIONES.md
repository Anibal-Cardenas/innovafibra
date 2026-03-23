# Sistema de Roles y Comisiones - Guía de Implementación

## 📋 Resumen de la Implementación

Se ha implementado un sistema completo de roles y comisiones para el Sistema de Gestión de Producción - Taller de Napa. La implementación incluye:

### Roles Implementados:

1. **Administrador** (`administrador`): Acceso completo a todo el sistema
2. **Operador** (`operador`): Registra producción y consulta sus comisiones
3. **Vendedor** (`vendedor`): Gestiona ventas y entregas
4. **Supervisor** (`supervisor`): Valida producciones (rol legacy)
5. **Trabajador** (`trabajador`): Similar a operador (rol legacy)

---

## 🚀 Pasos para Activar el Sistema

### 1. Ejecutar la Migración de Base de Datos

Accede a phpMyAdmin o MySQL desde la terminal y ejecuta el archivo de migración:

```bash
cd c:\xampp\htdocs\Napa
```

**Opción A - Desde phpMyAdmin:**
1. Abre phpMyAdmin (http://localhost/phpmyadmin)
2. Selecciona la base de datos `sistema_napa`
3. Ve a la pestaña "SQL"
4. Copia y pega el contenido del archivo `database/migrations/implementar_sistema_roles.sql`
5. Haz clic en "Continuar"

**Opción B - Desde terminal MySQL:**
```bash
mysql -u root -p sistema_napa < database/migrations/implementar_sistema_roles.sql
```

### 2. Verificar las Tablas Creadas

Verifica que se crearon correctamente las nuevas tablas:
- `comisiones`
- `comisiones_detalle`

Y que se actualizaron:
- `usuarios` (campo `rol` con nuevos valores)
- `producciones` (nuevo campo `id_comision`)

### 3. Crear Usuarios de Prueba

La migración ya crea dos usuarios de ejemplo:
- **Usuario:** `operador1` / **Contraseña:** `admin123` / **Rol:** Operador
- **Usuario:** `vendedor1` / **Contraseña:** `admin123` / **Rol:** Vendedor

El usuario admin existente mantiene sus credenciales:
- **Usuario:** `admin` / **Contraseña:** `admin123` / **Rol:** Administrador

---

## 📁 Archivos Creados/Modificados

### Archivos Nuevos:

1. **Migración:**
   - `database/migrations/implementar_sistema_roles.sql`

2. **Controlador:**
   - `src/controllers/ComisionesController.php`

3. **Vistas:**
   - `src/views/comisiones/mis_comisiones.php` (Vista del operador)
   - `src/views/comisiones/admin.php` (Vista del administrador)
   - `src/views/comisiones/detalle.php` (Detalle de comisión)

### Archivos Modificados:

1. **Configuración:**
   - `config/constants.php` (Añadidas constantes ROL_OPERADOR y ROL_VENDEDOR)

2. **Helpers:**
   - `src/helpers/session.php` (Añadidas funciones isOperador() e isVendedor())

3. **Vistas:**
   - `src/views/layout/header.php` (Menús dinámicos según rol)

4. **Controladores:**
   - `src/controllers/VentasController.php` (Permite acceso a vendedores)
   - `src/controllers/ProduccionController.php` (Permite acceso a operadores)

---

## 🎯 Funcionalidades por Rol

### 👨‍💼 Administrador
- Acceso completo a todas las funcionalidades
- Gestiona compras, producción, inventario, ventas
- **Gestión de Comisiones:**
  - Calcular comisiones por periodo
  - Registrar pagos de comisiones
  - Anular comisiones
  - Ver historial completo
  - Actualizar tarifas de operadores

### 👷 Operador
- Registrar su producción diaria
- Ver sus producciones históricas
- **Ver sus Comisiones:**
  - Consultar producción diaria del mes
  - Ver comisiones estimadas
  - Ver historial de comisiones pagadas
  - Filtrar por mes y año
  - Ver detalle de cada comisión liquidada

### 🛒 Vendedor
- Registrar nuevas ventas
- Ver listado de ventas
- Gestionar guías de entrega
- Gestionar información de choferes

---

## 💰 Sistema de Comisiones

### Características:

1. **Cálculo Automático:**
   - Se calcula por periodo (fecha inicio - fecha fin)
   - Solo incluye producciones aprobadas
   - Aplica la tarifa configurada para cada operador
   - No incluye producciones ya liquidadas

2. **Tarifa por Operador:**
   - Cada operador tiene su propia tarifa por bolsa
   - El administrador puede actualizarla en cualquier momento
   - Se registra historial de cambios de tarifa

3. **Estados de Comisión:**
   - **Pendiente:** Creada pero no calculada
   - **Calculado:** Ya se calculó el monto, pendiente de pago
   - **Pagado:** Ya se registró el pago
   - **Anulado:** Comisión anulada (libera las producciones)

4. **Detalle Completo:**
   - Cada comisión muestra todas las producciones incluidas
   - Se puede ver fecha, cantidad de bolsas y subtotal por día
   - Incluye información del pago (fecha, método, número de operación)

### Flujo de Trabajo:

```
1. Operador → Registra producción diaria
2. Supervisor/Admin → Valida la producción (aprueba/rechaza)
3. Admin → Calcula comisión por periodo (ej: semanal, quincenal)
4. Sistema → Genera comisión con detalle de producciones
5. Admin → Registra el pago de la comisión
6. Operador → Puede consultar su comisión pagada
```

---

## 🔧 Uso del Sistema de Comisiones

### Para el Administrador:

1. **Calcular una comisión:**
   - Ir a "Comisiones" en el menú
   - Seleccionar operador
   - Elegir fecha inicio y fin
   - Clic en "Calcular"
   - El sistema muestra el total calculado

2. **Registrar un pago:**
   - En la lista de comisiones pendientes
   - Clic en el botón "$" (Registrar pago)
   - Llenar: fecha de pago, método, número de operación
   - Confirmar

3. **Ver detalle:**
   - Clic en el ícono de ojo en cualquier comisión
   - Ver todas las producciones incluidas

### Para el Operador:

1. **Consultar producción del mes:**
   - Ir a "Mis Comisiones" en el menú
   - Seleccionar mes y año
   - Ver producción diaria con estado

2. **Ver comisiones pagadas:**
   - En la misma vista, ver historial de comisiones
   - Clic en "Ver detalle" para ver el desglose

---

## 🔒 Seguridad y Permisos

### Control de Acceso:
- Cada controlador verifica el rol del usuario
- Las vistas se adaptan dinámicamente al rol
- Los operadores solo ven su información
- Los vendedores solo acceden a ventas
- El administrador tiene acceso completo

### Protecciones Implementadas:
- Verificación de roles en cada método
- Tokens CSRF en formularios críticos
- Validación de permisos en consultas AJAX
- Auditoría de operaciones críticas

---

## 📊 Base de Datos - Nuevas Tablas

### Tabla: `comisiones`
Almacena las comisiones calculadas por periodo:
- `id_comision`: ID único
- `id_operario`: Referencia al usuario operador
- `fecha_inicio`, `fecha_fin`: Periodo de la comisión
- `total_bolsas_producidas`: Suma de bolsas del periodo
- `tarifa_aplicada`: Tarifa que se usó
- `monto_comision`, `monto_bonificacion`, `monto_descuento`, `monto_total`
- `estado`: pendiente, calculado, pagado, anulado
- `fecha_pago`, `metodo_pago`, `numero_operacion`

### Tabla: `comisiones_detalle`
Detalla las producciones incluidas en cada comisión:
- `id_comision_detalle`: ID único
- `id_comision`: Referencia a la comisión
- `id_produccion`: Referencia a la producción
- `fecha_produccion`, `cantidad_bolsas`, `tarifa_por_bolsa`, `subtotal`

### Vistas Creadas:
- `v_produccion_operador`: Resumen de producción por operador
- `v_comisiones_pendientes`: Comisiones pendientes de pago
- `v_historial_comisiones`: Comisiones pagadas

### Stored Procedures:
- `sp_calcular_comision_operario()`: Calcula automáticamente una comisión
- `sp_registrar_pago_comision()`: Registra el pago de una comisión
- `sp_resumen_comisiones_operario()`: Obtiene resumen de producción

---

## 🧪 Pruebas Recomendadas

1. **Login con diferentes roles:**
   - Verificar que cada rol vea solo su menú
   - Probar acceso a rutas restringidas

2. **Operador:**
   - Registrar una producción
   - Ver "Mis Producciones"
   - Consultar "Mis Comisiones"

3. **Vendedor:**
   - Registrar una venta
   - Ver lista de ventas

4. **Administrador:**
   - Validar una producción
   - Calcular comisión para un operador
   - Registrar pago de comisión
   - Ver detalle de comisión

5. **Flujo completo:**
   ```
   a. Operador registra 100 bolsas (día 1)
   b. Admin valida y aprueba
   c. Operador registra 150 bolsas (día 2)
   d. Admin valida y aprueba
   e. Admin calcula comisión del periodo
   f. Operador consulta su comisión estimada
   g. Admin registra el pago
   h. Operador ve su comisión pagada
   ```

---

## 🐛 Solución de Problemas

### Error: "No tiene permisos para acceder"
- Verificar que el usuario tenga el rol correcto en la BD
- Cerrar sesión y volver a iniciar

### Error al calcular comisión: "Ya existe una comisión"
- Verificar que no haya una comisión para el mismo periodo
- Revisar en la tabla `comisiones` si ya existe

### Las comisiones no se calculan correctamente
- Verificar que las producciones estén en estado "aprobado"
- Verificar que la tarifa del operador esté configurada
- Revisar que las fechas del periodo sean correctas

### El menú no cambia según el rol
- Limpiar caché del navegador
- Verificar que se actualizó correctamente `header.php`

---

## 📝 Notas Importantes

1. **Migración de Usuarios Existentes:**
   - Los usuarios con rol "trabajador" se actualizan automáticamente a "operador"
   - Los roles "trabajador" y "supervisor" quedan como legacy

2. **Tarifas:**
   - Asignar tarifa a cada operador desde la vista de usuarios o configuración
   - La tarifa puede ser diferente para cada operador
   - Se registra historial de cambios

3. **Comisiones Retroactivas:**
   - Se pueden calcular comisiones de periodos pasados
   - Solo se incluyen producciones que no estén en otra comisión

4. **Bonificaciones y Descuentos:**
   - Los campos están preparados para futuras implementaciones
   - Actualmente el monto_total = monto_comision

5. **Extensibilidad:**
   - El sistema está preparado para agregar más funcionalidades
   - Se pueden añadir bonos por metas, descuentos por rechazos, etc.

---

## 📧 Contacto y Soporte

Si encuentras algún problema o necesitas ayuda adicional con la implementación, por favor documenta:
- El error exacto que aparece
- Los pasos que seguiste
- El rol del usuario con el que estás probando

---

**Fecha de Implementación:** 14 de Enero, 2026  
**Versión del Sistema:** 1.1  
**Desarrollado por:** GitHub Copilot
