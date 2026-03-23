<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#10b981">
    <title><?= isset($title) ? h($title) . ' - ' : '' ?><?= APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= CSS_URL ?>/custom.css?v=2.2" rel="stylesheet">
</head>
<body>
    <!-- jQuery (required for inline view scripts) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <?php if (isLoggedIn()): ?>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <?php
                // Enlace de la marca: dirigir según rol del usuario
                $homeUrl = BASE_URL . '/dashboard';
                if (isOperador()) {
                    $homeUrl = BASE_URL . '/produccion/misproducciones';
                } elseif (isVendedor()) {
                    $homeUrl = BASE_URL . '/ventas';
                }
            ?>
            <a class="navbar-brand" href="<?= $homeUrl ?>">
                <i class="fas fa-industry"></i> <?= APP_NAME ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/dashboard">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (isAdmin()): ?>
                    <!-- MENÚ COMPLETO PARA ADMINISTRADOR -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-shopping-cart"></i> Compras
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/compras/nueva-fibra">Nueva Compra Fibra</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/compras/nueva-bolsas">Nueva Compra Bolsas</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/compras/lotes">Ver Lotes</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/compras/bolsas">Ver Compras Bolsas</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/gastos"><i class="fas fa-file-invoice-dollar"></i> Gastos Operativos</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/proveedores"><i class="fas fa-truck"></i> Proveedores</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-industry"></i> Producción
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/produccion/nueva">Registrar Producción</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/produccion/validar">Validar Producción</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/produccion">Ver Todas</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/inventario">
                            <i class="fas fa-warehouse"></i> Inventario
                        </a>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-dollar-sign"></i> Ventas
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/ventas/nueva">Nueva Venta</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/ventas">Ver Ventas</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/otros_ingresos"><i class="fas fa-hand-holding-usd"></i> Otros Ingresos</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/choferes"><i class="fas fa-id-card"></i> Choferes</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/comisiones">
                            <i class="fas fa-money-bill-wave"></i> Comisiones
                        </a>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-bar"></i> Reportes
                        </a>
                        <ul class="dropdown-menu">
                            <!-- <li><a class="dropdown-item" href="<?= BASE_URL ?>/reportes/mermas">Reporte de Mermas</a></li> -->
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/reportes/produccion">Reporte de Producción</a></li>
                            <!-- <li><a class="dropdown-item" href="<?= BASE_URL ?>/reportes/nomina">Reporte de Nómina</a></li> -->
                        </ul>
                    </li>
                    
                    <?php elseif (isOperador()): ?>
                    <!-- MENÚ PARA OPERADOR -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-industry"></i> Mi Producción
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/produccion/nueva">Registrar Producción</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/produccion/misproducciones">Mis Producciones</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/comisiones">
                            <i class="fas fa-money-bill-wave"></i> Mis Comisiones
                        </a>
                    </li>
                    
                    <?php elseif (isVendedor()): ?>
                    <!-- MENÚ PARA VENDEDOR -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-dollar-sign"></i> Ventas
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/ventas/nueva">Nueva Venta</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/ventas">Ver Ventas</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/choferes">
                            <i class="fas fa-truck"></i> Choferes
                        </a>
                    </li>
                    
                    <?php elseif (hasAnyRole([ROL_SUPERVISOR, ROL_TRABAJADOR])): ?>
                    <!-- MENÚ LEGACY PARA SUPERVISOR/TRABAJADOR -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-industry"></i> Producción
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/produccion/nueva">Registrar Producción</a></li>
                            <?php if (hasAnyRole([ROL_ADMINISTRADOR, ROL_SUPERVISOR])): ?>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/produccion/validar">Validar Producción</a></li>
                            <?php endif; ?>
                            <?php if (isTrabajador()): ?>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/produccion/misproducciones">Mis Producciones</a></li>
                            <?php else: ?>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/produccion">Ver Todas</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= h(getCurrentUserName()) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text"><small>Rol: <?= h(getCurrentUserRole()) ?></small></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if (isAdmin()): ?>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/usuarios"><i class="fas fa-users"></i> Gestión de Usuarios</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/configuracion"><i class="fas fa-cog"></i> Configuración</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/calidades-fibra"><i class="fas fa-award"></i> Calidades de Fibra</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/auth/logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Mensajes Flash -->
    <?php 
    $flashMessage = getFlashMessage();
    if ($flashMessage): 
    ?>
    <div class="container mt-3">
        <div class="alert alert-<?= $flashMessage['type'] === MSG_SUCCESS ? 'success' : 
                                    ($flashMessage['type'] === MSG_ERROR ? 'danger' : 
                                    ($flashMessage['type'] === MSG_WARNING ? 'warning' : 'info')) ?> alert-dismissible fade show" role="alert">
            <?= h($flashMessage['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Contenido Principal -->
    <main class="py-4">
