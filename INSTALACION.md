# GUÍA DE INSTALACIÓN
## Sistema de Gestión de Producción - Taller de Napa

---

## 📋 Requisitos del Sistema

### Hardware Mínimo
- Procesador: Intel Core i3 o equivalente
- RAM: 4 GB
- Disco Duro: 500 MB libres
- Resolución de pantalla: 1366x768 o superior

### Software Requerido
- **Windows 7** o superior
- **XAMPP 7.4** o superior (incluye Apache + MySQL + PHP)
- **Navegador web moderno**: Chrome 90+, Firefox 88+, Edge 90+

---

## 🚀 Instalación Paso a Paso

### Paso 1: Instalar XAMPP

1. Descargar XAMPP desde: https://www.apachefriends.org/
2. Ejecutar el instalador
3. Seleccionar componentes:
   - ✅ Apache
   - ✅ MySQL
   - ✅ PHP
   - ✅ phpMyAdmin
4. Instalar en la ruta por defecto: `C:\xampp`
5. Al finalizar, iniciar el Panel de Control de XAMPP

### Paso 2: Iniciar Servicios

1. Abrir el **Panel de Control de XAMPP**
2. Hacer clic en **Start** para:
   - Apache
   - MySQL

3. Verificar que ambos servicios muestren fondo verde y texto "Running"

### Paso 3: Copiar Archivos del Sistema

1. Copiar la carpeta completa **Napa** en:
   ```
   C:\xampp\htdocs\Napa
   ```

2. Verificar que la estructura sea:
   ```
   C:\xampp\htdocs\Napa\
   ├── config/
   ├── database/
   ├── docs/
   ├── public/
   ├── src/
   └── README.md
   ```

### Paso 4: Crear la Base de Datos

**Opción A: Usando phpMyAdmin (Recomendado para principiantes)**

1. Abrir navegador e ir a: `http://localhost/phpmyadmin`
2. Hacer clic en pestaña **"SQL"**
3. Copiar TODO el contenido del archivo: `C:\xampp\htdocs\Napa\database\schema.sql`
4. Pegar en el área de texto
5. Hacer clic en el botón **"Go"** o **"Continuar"**
6. Esperar a que termine (debería crear la base de datos y todas las tablas)
7. Verificar que en el panel izquierdo aparezca la base de datos **"sistema_napa"**

**Opción B: Usando línea de comandos**

1. Abrir **Símbolo del sistema** (CMD)
2. Navegar a la carpeta de MySQL:
   ```cmd
   cd C:\xampp\mysql\bin
   ```

3. Ejecutar:
   ```cmd
   mysql -u root -p < C:\xampp\htdocs\Napa\database\schema.sql
   ```

4. Presionar **Enter** (la contraseña está vacía por defecto)

### Paso 5: Verificar Configuración

1. Abrir el archivo: `C:\xampp\htdocs\Napa\config\database.php`
2. Verificar que los datos sean correctos:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sistema_napa');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Vacío por defecto en XAMPP
   ```

3. Si tu MySQL tiene contraseña, ingresarla en `DB_PASS`

### Paso 6: Probar el Sistema

1. Abrir navegador web
2. Ir a: `http://localhost/Napa/public`
3. Debería aparecer la pantalla de **Login**

### Paso 7: Primer Acceso

Usar las credenciales por defecto:

```
Usuario:     admin
Contraseña:  admin123
```

⚠️ **IMPORTANTE**: Cambiar la contraseña después del primer ingreso.

---

## ✅ Verificación de Instalación

### Checklist de Verificación

- [ ] XAMPP instalado correctamente
- [ ] Apache iniciado (puerto 80)
- [ ] MySQL iniciado (puerto 3306)
- [ ] Archivos copiados en `C:\xampp\htdocs\Napa`
- [ ] Base de datos `sistema_napa` creada
- [ ] Tablas creadas (17 tablas en total)
- [ ] Usuario admin creado
- [ ] Acceso al sistema exitoso: `http://localhost/Napa/public`

### Verificar Base de Datos

En phpMyAdmin, la base de datos debe tener estas tablas:

```
✓ auditoria
✓ choferes
✓ clientes
✓ compras_bolsas
✓ configuracion_sistema
✓ entregas
✓ historial_configuracion
✓ historial_tarifas
✓ inventario
✓ kardex
✓ lotes_fibra
✓ producciones
✓ proveedores
✓ usuarios
✓ ventas
✓ v_estado_inventario (vista)
✓ v_produccion_validada (vista)
✓ v_resumen_lotes (vista)
```

---

## 🔧 Solución de Problemas Comunes

### Problema 1: Apache no inicia

**Error**: "Port 80 in use by..."

**Solución**:
1. Otro programa está usando el puerto 80 (ej: Skype, IIS)
2. Opciones:
   - Cerrar el programa que usa el puerto 80
   - O cambiar el puerto de Apache:
     - Abrir: `C:\xampp\apache\conf\httpd.conf`
     - Buscar: `Listen 80`
     - Cambiar a: `Listen 8080`
     - Guardar y reiniciar Apache
     - Acceder al sistema en: `http://localhost:8080/Napa/public`

### Problema 2: MySQL no inicia

**Error**: "Port 3306 in use by..."

**Solución**:
1. Otro servicio MySQL está corriendo
2. Abrir "Servicios" de Windows
3. Buscar "MySQL" y detener el servicio
4. Reiniciar MySQL desde XAMPP

### Problema 3: Página en blanco

**Posibles causas**:

**A. Error de PHP**
- Abrir: `C:\xampp\php\php.ini`
- Buscar: `display_errors = Off`
- Cambiar a: `display_errors = On`
- Guardar y reiniciar Apache

**B. Permisos de carpeta**
- Click derecho en carpeta `Napa`
- Propiedades > Seguridad
- Dar permisos de lectura/escritura

**C. Revisar logs**
- Apache: `C:\xampp\apache\logs\error.log`
- PHP: `C:\xampp\php\logs\php_error_log`

### Problema 4: Error de conexión a base de datos

**Error**: "SQLSTATE[HY000] [1045] Access denied..."

**Solución**:
1. Verificar usuario y contraseña en `config/database.php`
2. Por defecto en XAMPP:
   - Usuario: `root`
   - Contraseña: (vacía)
3. Si cambiaste la contraseña de MySQL, actualízala en el config

### Problema 5: No aparece el login

**Error**: 404 Not Found

**Solución**:
1. Verificar que los archivos estén en: `C:\xampp\htdocs\Napa`
2. Acceder exactamente a: `http://localhost/Napa/public`
3. Verificar que Apache esté corriendo
4. Revisar el archivo `.htaccess` en la carpeta `public`

### Problema 6: Error "Class 'Database' not found"

**Solución**:
1. Verificar que existe: `C:\xampp\htdocs\Napa\config\database.php`
2. Verificar que existe: `C:\xampp\htdocs\Napa\public\index.php`
3. Limpiar caché del navegador (Ctrl + F5)

---

## 🔐 Seguridad Post-Instalación

### 1. Cambiar Contraseña de Admin

1. Iniciar sesión con `admin` / `admin123`
2. Ir a **Configuración > Usuarios**
3. Editar usuario `admin`
4. Cambiar la contraseña
5. Guardar

### 2. Cambiar Contraseña de MySQL (Opcional)

1. Abrir phpMyAdmin: `http://localhost/phpmyadmin`
2. Click en pestaña **"User accounts"**
3. Click en **"Edit privileges"** para usuario `root`
4. Click en **"Change password"**
5. Ingresar nueva contraseña
6. Actualizar `config/database.php` con la nueva contraseña

### 3. Restringir Acceso a phpMyAdmin (Producción)

1. Editar: `C:\xampp\phpMyAdmin\config.inc.php`
2. Cambiar:
   ```php
   $cfg['Servers'][$i]['auth_type'] = 'http';
   ```

---

## 📊 Datos de Prueba (Opcional)

Para cargar datos de prueba y probar el sistema:

### Crear Proveedor de Prueba

```sql
INSERT INTO proveedores (nombre, tipo_proveedor) 
VALUES ('Proveedor de Prueba', 'fibra');
```

### Crear Cliente de Prueba

```sql
INSERT INTO clientes (nombre) 
VALUES ('Cliente de Prueba');
```

Ejecutar desde phpMyAdmin > SQL

---

## 📞 Soporte Técnico

Si encuentras problemas que no están en esta guía:

1. Revisar los logs de error
2. Verificar la configuración en `config/`
3. Consultar la documentación en `docs/`

---

## ✨ ¡Listo!

El sistema ya debería estar funcionando correctamente.

**Próximos pasos:**
1. Cambiar contraseña del administrador
2. Crear usuarios adicionales (operarios, supervisores)
3. Configurar proveedores
4. Configurar clientes
5. Realizar primera compra de fibra
6. ¡Comenzar a usar el sistema!

---

**Última actualización**: Enero 2026
