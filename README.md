# Sistema de Gestión de Producción - Taller de Napa

Sistema web completo para la gestión de producción de un taller familiar de fabricación de Napa, enfocado en el control de mermas y gestión estricta de inventario.

## 📋 Descripción del Proyecto

Este sistema MRP/ERP simplificado permite gestionar:
- **Compras de materia prima** (fibra) con trazabilidad por lotes
- **Insumos secundarios** (bolsas plásticas) con conversión automática
- **Producción** con detección automática de mermas excesivas
- **RRHH** con pago por destajo y validación de calidad
- **Ventas** con visualización de costos y márgenes
- **Logística** con guías de entrega
- **Reportes** y dashboard ejecutivo

## 🎯 Características Principales

### ✅ Control de Mermas
- Detección automática cuando la producción real < 95% del estimado
- Alertas visuales y reportes de lotes problemáticos
- Trazabilidad completa por lote de materia prima

### ✅ Gestión de Inventario
- Control automático de fibra (kg)
- Control de bolsas plásticas (kg) con conversión a unidades
- Producto terminado (bolsas de napa)
- Kardex completo de movimientos
- Alertas de stock mínimo

### ✅ Recursos Humanos
- Pago por destajo (por bolsa producida)
- Validación de calidad obligatoria
- Cálculo automático de nómina
- Solo se paga producción aprobada

### ✅ Roles y Seguridad
- **Administrador**: Acceso total
- **Supervisor**: Validación de producción
- **Trabajador**: Registro de producción propia

## 📁 Estructura del Proyecto

```
Napa/
│
├── docs/                                 # Documentación técnica
│   ├── 01_ESPECIFICACION_REQUISITOS.md  # Requisitos funcionales
│   ├── 02_REGLAS_NEGOCIO.md             # Reglas de negocio detalladas
│   ├── 03_DIAGRAMA_ER.md                # Modelo de datos
│   └── 04_CASOS_DE_USO.md               # Casos de uso detallados
│
├── database/                             # Base de datos
│   ├── schema.sql                        # Script de creación completo
│   ├── migrations/                       # Migraciones (futuro)
│   └── seeds/                            # Datos de prueba (futuro)
│
├── config/                               # Configuración
│   ├── database.php                      # Conexión a BD
│   ├── app.php                           # Configuración general
│   └── constants.php                     # Constantes del sistema
│
├── src/                                  # Código fuente
│   ├── controllers/                      # Controladores
│   │   ├── AuthController.php
│   │   ├── CompraController.php
│   │   ├── ProduccionController.php
│   │   ├── VentaController.php
│   │   └── ReporteController.php
│   │
│   ├── models/                           # Modelos de datos
│   │   ├── Usuario.php
│   │   ├── Lote.php
│   │   ├── Produccion.php
│   │   ├── Venta.php
│   │   └── Inventario.php
│   │
│   ├── views/                            # Vistas (HTML/PHP)
│   │   ├── layout/
│   │   │   ├── header.php
│   │   │   ├── footer.php
│   │   │   └── sidebar.php
│   │   │
│   │   ├── auth/
│   │   │   └── login.php
│   │   │
│   │   ├── dashboard/
│   │   │   └── index.php
│   │   │
│   │   ├── compras/
│   │   │   ├── fibra_nueva.php
│   │   │   ├── bolsas_nueva.php
│   │   │   └── lotes_lista.php
│   │   │
│   │   ├── produccion/
│   │   │   ├── nueva.php
│   │   │   ├── validar.php
│   │   │   └── lista.php
│   │   │
│   │   ├── ventas/
│   │   │   ├── nueva.php
│   │   │   └── lista.php
│   │   │
│   │   └── reportes/
│   │       ├── mermas.php
│   │       ├── produccion.php
│   │       └── nomina.php
│   │
│   ├── services/                         # Lógica de negocio
│   │   ├── InventarioService.php
│   │   ├── MermaService.php
│   │   ├── NominaService.php
│   │   └── ReporteService.php
│   │
│   └── helpers/                          # Funciones auxiliares
│       ├── functions.php
│       ├── validation.php
│       └── session.php
│
├── public/                               # Recursos públicos
│   ├── index.php                         # Punto de entrada
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   └── custom.css
│   ├── js/
│   │   ├── jquery.min.js
│   │   ├── bootstrap.min.js
│   │   └── app.js
│   └── assets/
│       └── images/
│
├── vendor/                               # Dependencias (composer)
│
├── .htaccess                             # Configuración Apache
├── composer.json                         # Dependencias PHP
└── README.md                             # Este archivo
```

## 🛠️ Tecnologías

- **Backend**: PHP 7.4+ (compatible con XAMPP)
- **Base de Datos**: MySQL 5.7+
- **Frontend**: HTML5, CSS3 (Bootstrap 5), JavaScript (jQuery)
- **Servidor**: Apache (XAMPP)
- **Arquitectura**: MVC (Model-View-Controller)

## 📦 Instalación

### Requisitos Previos

- XAMPP (PHP 7.4+, MySQL 5.7+)
- Navegador web moderno (Chrome, Firefox, Edge)

### Pasos de Instalación

1. **Clonar/Copiar el proyecto** en `c:\xampp\htdocs\Napa`

2. **Crear la base de datos**
   ```bash
   # Abrir MySQL desde XAMPP
   # Ejecutar el script: database/schema.sql
   ```
   
   O desde línea de comandos:
   ```bash
   mysql -u root -p < database/schema.sql
   ```

3. **Configurar la conexión**
   
   Editar `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sistema_napa');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. **Iniciar XAMPP**
   - Iniciar Apache
   - Iniciar MySQL

5. **Acceder al sistema**
   ```
   http://localhost/Napa/public
   ```

### Credenciales por Defecto

- **Usuario**: `admin`
- **Contraseña**: `admin123`

⚠️ **IMPORTANTE**: Cambiar la contraseña después del primer login.

## 📊 Modelo de Datos

El sistema cuenta con las siguientes entidades principales:

- **usuarios**: Gestión de acceso y roles
- **proveedores**: Proveedores de fibra y bolsas
- **lotes_fibra**: Materia prima con trazabilidad
- **compras_bolsas**: Insumos secundarios
- **producciones**: Registro de producción diaria
- **ventas**: Registro de ventas
- **clientes**: Clientes del negocio
- **entregas**: Guías de entrega
- **inventario**: Stock actual
- **kardex**: Historial de movimientos
- **configuracion_sistema**: Parámetros configurables

Ver [03_DIAGRAMA_ER.md](docs/03_DIAGRAMA_ER.md) para el modelo completo.

## 🔄 Flujo de Trabajo Principal

### 1. Compra de Materia Prima
```
Admin → Registrar Compra de Fibra → Sistema genera Lote → Inventario actualizado
```

### 2. Producción
```
Operario → Registrar Producción → Sistema calcula merma → Estado: Pendiente
         ↓
Supervisor → Validar Calidad → Aprobar/Rechazar → Inventarios actualizados
```

### 3. Venta
```
Admin → Registrar Venta → Sistema muestra costo → Confirmar → Inventario descontado
```

### 4. Entrega
```
Admin → Registrar Entrega → Generar Guía → Actualizar estado de venta
```

## 📈 Reglas de Negocio Clave

### Detección de Merma Excesiva
```
Si (Producción Real < Cantidad Estimada × 0.95)
    Entonces: Flag "Merma Excesiva" = TRUE
```

### Factor de Conversión de Bolsas
```
Peso_Descuento = Cantidad_Producida × Factor_Conversión (0.02 kg/bolsa)
```

### Cálculo de Eficiencia
```
Eficiencia % = (Producción Real / Cantidad Estimada) × 100
```

### Cálculo de Pago por Destajo
```
Pago = Bolsas_Validadas_Aprobadas × Tarifa_Operario
```

Ver [02_REGLAS_NEGOCIO.md](docs/02_REGLAS_NEGOCIO.md) para todas las reglas.

## 📱 Módulos del Sistema

### 🔐 Autenticación
- Login/Logout
- Gestión de usuarios
- Roles: Administrador, Supervisor, Trabajador

### 🛒 Compras
- Registro de compra de fibra (con generación de lote)
- Registro de compra de bolsas plásticas
- Gestión de proveedores

### 🏭 Producción
- Registro de producción diaria
- Validación de calidad
- Detección automática de mermas
- Trazabilidad por lote

### 👥 RRHH
- Gestión de operarios
- Cálculo de nómina por destajo
- Historial de tarifas

### 💰 Ventas
- Registro de ventas con precio variable
- Visualización de costo y margen
- Gestión de clientes

### 🚚 Logística
- Registro de entregas
- Generación de guías (PDF)
- Gestión de choferes

### 📊 Reportes
- Dashboard ejecutivo
- Reporte de mermas
- Reporte de producción
- Reporte de nómina
- Reporte financiero

## 🔧 Configuración del Sistema

Parámetros configurables desde el panel de administración:

| Parámetro | Default | Descripción |
|-----------|---------|-------------|
| cantidad_estimada_default | 70 | Bolsas estimadas por fardo |
| factor_conversion_bolsas | 0.02 | kg por bolsa plástica |
| tolerancia_merma | 5 | % de tolerancia de merma |
| stock_minimo_bolsas | 10 | kg de stock mínimo de alerta |
| stock_minimo_fibra | 100 | kg de stock mínimo de alerta |
| margen_minimo_venta | 10 | % de margen mínimo sugerido |
| timeout_sesion | 30 | Minutos de inactividad |

## 🧪 Testing (Futuro)

```bash
# Ejecutar tests unitarios
php vendor/bin/phpunit tests/

# Ejecutar tests de integración
php vendor/bin/phpunit tests/Integration/
```

## 📝 Documentación Adicional

- [Especificación de Requisitos](docs/01_ESPECIFICACION_REQUISITOS.md)
- [Reglas de Negocio](docs/02_REGLAS_NEGOCIO.md)
- [Diagrama ER](docs/03_DIAGRAMA_ER.md)
- [Casos de Uso](docs/04_CASOS_DE_USO.md)

## 🚀 Roadmap

### Fase 1 (Actual) - MVP
- [x] Especificación de requisitos
- [x] Modelo de datos
- [x] Casos de uso
- [ ] Implementación del backend
- [ ] Implementación del frontend
- [ ] Testing básico

### Fase 2 - Mejoras
- [ ] Reportes avanzados con gráficos
- [ ] Exportación a Excel/PDF
- [ ] Notificaciones por email
- [ ] App móvil (Progressive Web App)

### Fase 3 - Optimización
- [ ] Análisis predictivo de mermas
- [ ] Integración con facturación electrónica
- [ ] API REST para integraciones

## 🤝 Contribución

Este es un proyecto privado para un negocio familiar. 

## 📄 Licencia

Propiedad privada - Todos los derechos reservados

## 👤 Autor

**Equipo de Desarrollo**  
Fecha: Enero 2026

## 📞 Soporte

Para soporte o consultas:
- Email: [email del negocio]
- Teléfono: [teléfono del negocio]

---

**Sistema de Gestión de Producción Napa** - Control total de tu producción 🏭
