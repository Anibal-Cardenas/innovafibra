# ⚡ Guía de Inicio Rápido - Sistema de Roles y Comisiones

## 🚀 En 5 Minutos Tienes el Sistema Funcionando

### ✅ Checklist Pre-requisitos
- [ ] XAMPP instalado y corriendo
- [ ] Apache y MySQL activos
- [ ] Base de datos `sistema_napa` existente
- [ ] Sistema actual funcionando

---

## 📋 PASO 1: Ejecutar Migración (2 minutos)

### Opción A - phpMyAdmin (Recomendado)
1. Abre: http://localhost/phpmyadmin
2. Selecciona la base de datos: `sistema_napa`
3. Click en pestaña "SQL"
4. Abre el archivo: `database/migrations/implementar_sistema_roles.sql`
5. Copia TODO el contenido
6. Pégalo en el editor SQL
7. Click en "Continuar"
8. ✅ Verás mensaje: "Migración completada exitosamente"

### Opción B - Terminal
```bash
# Desde c:\xampp\htdocs\Napa
cd c:\xampp\htdocs\Napa
mysql -u root sistema_napa < database/migrations/implementar_sistema_roles.sql
```

---

## 🧪 PASO 2: Probar Inmediatamente (3 minutos)

### Test 1: Login como Operador
```
URL: http://localhost/Napa/auth/login
Usuario: operador1
Contraseña: admin123
```

**Verás:**
- Menú limitado (solo Producción y Comisiones)
- Opción "Mi Producción"
- Opción "Mis Comisiones"

**Prueba:**
1. Click en "Registrar Producción"
2. Registra una producción de prueba
3. Click en "Mis Comisiones"
4. Verás tu producción del día (pendiente de validación)

---

### Test 2: Login como Administrador
```
URL: http://localhost/Napa/auth/login
Usuario: admin
Contraseña: admin123
```

**Verás:**
- Menú completo
- Nueva opción: "Comisiones" en el menú principal

**Prueba:**
1. Ve a "Producción" → "Validar Producción"
2. Aprueba la producción del operador1
3. Ve a "Comisiones"
4. Selecciona "operador1"
5. Fecha inicio: hoy
6. Fecha fin: hoy
7. Click en "Calcular"
8. ✅ Verás la comisión calculada

**Registrar el pago:**
1. En la tabla de "Comisiones Pendientes"
2. Click en el botón "$" (verde)
3. Llena: fecha de hoy, método "efectivo"
4. Click en "Confirmar Pago"
5. ✅ Comisión pagada

---

### Test 3: Login como Vendedor
```
URL: http://localhost/Napa/auth/login
Usuario: vendedor1
Contraseña: admin123
```

**Verás:**
- Menú limitado (solo Ventas y Choferes)
- No tiene acceso a producción ni comisiones

**Prueba:**
1. Click en "Ventas" → "Nueva Venta"
2. Registra una venta de prueba
3. ✅ Sistema funciona correctamente

---

## 🔧 PASO 3: Configurar Operadores Reales (Opcional)

### Si tienes operadores existentes:

```sql
-- Ver todos los usuarios actuales
SELECT id_usuario, username, nombre_completo, rol, tarifa_por_bolsa 
FROM usuarios 
WHERE estado = 'activo';

-- Cambiar rol de un usuario a operador
UPDATE usuarios 
SET rol = 'operador', tarifa_por_bolsa = 0.50 
WHERE username = 'TU_USUARIO';

-- O crear un nuevo operador
INSERT INTO usuarios (username, password_hash, nombre_completo, rol, tarifa_por_bolsa, estado)
VALUES ('juan_operador', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
        'Juan Pérez', 'operador', 0.50, 'activo');
-- Contraseña: admin123
```

---

## 🎯 PASO 4: Flujo Completo de Prueba (5 minutos)

### Escenario Real:

**Día 1 - Como Operador:**
1. Login como `operador1`
2. Registrar producción: 100 bolsas
3. Ver "Mis Comisiones" → Verás 0 bolsas (pendiente de validación)
4. Logout

**Día 1 - Como Admin:**
1. Login como `admin`
2. "Producción" → "Validar Producción"
3. Aprobar la producción de 100 bolsas
4. Logout

**Día 2 - Como Operador:**
1. Login como `operador1`
2. Registrar producción: 150 bolsas
3. Ver "Mis Comisiones" → Verás 100 bolsas del día 1 (aprobadas)
4. Comisión estimada: 100 × S/ 0.50 = S/ 50.00
5. Logout

**Día 2 - Como Admin:**
1. Login como `admin`
2. "Producción" → "Validar Producción"
3. Aprobar la producción de 150 bolsas
4. "Comisiones"
5. Calcular comisión: operador1, del día 1 al día 2
6. ✅ Sistema muestra: 250 bolsas × S/ 0.50 = S/ 125.00
7. Registrar pago: efectivo, fecha de hoy
8. Logout

**Día 3 - Como Operador:**
1. Login como `operador1`
2. "Mis Comisiones"
3. Ver historial → Verás la comisión pagada de S/ 125.00
4. Click en "Ver Detalle" → Verás las 2 producciones incluidas
5. ✅ Sistema completo funcionando

---

## 🎨 Interfaz Visual

### Menú del Operador:
```
┌─────────────────────────────────────────────┐
│ 🏠 Dashboard                                │
│ 🏭 Mi Producción                            │
│    ├─ Registrar Producción                  │
│    └─ Mis Producciones                      │
│ 💰 Mis Comisiones                           │
│ 👤 Juan Pérez (Operador) ▼                  │
│    └─ Cerrar Sesión                         │
└─────────────────────────────────────────────┘
```

### Menú del Vendedor:
```
┌─────────────────────────────────────────────┐
│ 🏠 Dashboard                                │
│ 💵 Ventas                                   │
│    ├─ Nueva Venta                           │
│    └─ Ver Ventas                            │
│ 🚚 Choferes                                 │
│ 👤 María González (Vendedor) ▼              │
│    └─ Cerrar Sesión                         │
└─────────────────────────────────────────────┘
```

### Menú del Administrador:
```
┌─────────────────────────────────────────────┐
│ 🏠 Dashboard                                │
│ 🛒 Compras ▼                                │
│ 🏭 Producción ▼                             │
│ 📦 Inventario                               │
│ 💵 Ventas ▼                                 │
│ 💰 Comisiones                               │
│ 📊 Reportes ▼                               │
│ 👤 Admin (Administrador) ▼                  │
│    ├─ Configuración                         │
│    ├─ Calidades de Fibra                    │
│    └─ Cerrar Sesión                         │
└─────────────────────────────────────────────┘
```

---

## ❓ Troubleshooting Rápido

### ❌ "Error al ejecutar la migración"
**Solución:**
```sql
-- Verificar que la base de datos existe
SHOW DATABASES LIKE 'sistema_napa';

-- Si no existe, crearla primero
CREATE DATABASE sistema_napa;
```

### ❌ "No puedo iniciar sesión"
**Solución:**
```sql
-- Verificar usuarios
SELECT username, rol FROM usuarios;

-- Resetear contraseña si es necesario
UPDATE usuarios 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE username = 'operador1';
-- Contraseña: admin123
```

### ❌ "El menú no cambia"
**Solución:**
1. Cerrar sesión completamente
2. Limpiar cookies del navegador (Ctrl + Shift + Supr)
3. Volver a iniciar sesión

### ❌ "No aparece el botón de Comisiones"
**Solución:**
```sql
-- Verificar que la migración se ejecutó
SHOW TABLES LIKE 'comisiones%';

-- Deberías ver:
-- comisiones
-- comisiones_detalle
```

---

## 📊 Verificación Final

### Checklist de Verificación:
- [ ] Login como admin funciona
- [ ] Login como operador1 funciona
- [ ] Login como vendedor1 funciona
- [ ] Cada rol ve su menú correspondiente
- [ ] Operador puede registrar producción
- [ ] Admin puede validar producción
- [ ] Admin puede calcular comisión
- [ ] Admin puede registrar pago
- [ ] Operador puede ver sus comisiones
- [ ] Vendedor puede registrar ventas

Si todos están ✅, ¡el sistema está 100% funcional!

---

## 🎓 Siguientes Pasos

1. **Asignar tarifas reales** a tus operadores
2. **Crear usuarios reales** para tu equipo
3. **Capacitar al personal** en el nuevo flujo
4. **Hacer backup** de la base de datos
5. **Monitorear** el primer ciclo de comisiones

---

## 📞 Ayuda Rápida

**Si algo no funciona:**
1. Revisa los errores en: `c:\xampp\php\logs\php_error_log`
2. Revisa logs de MySQL en phpMyAdmin
3. Consulta la guía completa: `SISTEMA_ROLES_COMISIONES.md`

---

## ✨ ¡Listo!

Tu sistema de roles y comisiones está funcionando. Ahora puedes:
- Registrar producciones como operador
- Validar y calcular comisiones como admin
- Registrar ventas como vendedor

**Todo el control que necesitabas, implementado y funcionando.**

---

**Fecha:** 14 de Enero, 2026  
**Tiempo estimado:** 5-10 minutos  
**Dificultad:** ⭐⭐☆☆☆ (Fácil)
