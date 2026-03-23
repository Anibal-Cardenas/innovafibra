<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?= APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #10b981;
            --primary-hover: #059669;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            background-image: 
                radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(5, 150, 105, 0.15) 0px, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 15px;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .brand-logo {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 32px;
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
            font-size: 0.95rem;
        }

        .form-control:focus {
            background-color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .input-group-text {
            background-color: #f9fafb;
            border-color: #e5e7eb;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="brand-logo">
                        <i class="fas fa-industry"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1"><?= APP_NAME ?></h4>
                    <p class="text-muted small">Sistema de Gestión de Producción</p>
                </div>
                
                <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger d-flex align-items-center small py-2" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div><?= h($error) ?></div>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="<?= BASE_URL ?>/auth/login">
                    <div class="mb-3">
                        <label for="username" class="form-label small fw-bold text-muted">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   required 
                                   autofocus
                                   placeholder="Ej: admin">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label small fw-bold text-muted">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   required
                                   placeholder="••••••••">
                        </div>
                    </div>
                    
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary">
                            Acceder al Sistema
                        </button>
                    </div>
                </form>
                
                <div class="text-center">
                    <small class="text-muted d-block mb-1">
                        <i class="fas fa-info-circle me-1"></i> 
                        Credenciales por defecto:
                    </small>
                    <span class="badge bg-light text-dark border">admin / admin123</span>
                </div>
            </div>
            <div class="card-footer bg-light text-center py-3 border-top-0">
                <small class="text-muted">
                    &copy; <?= date('Y') ?> <?= APP_NAME ?>
                </small>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <small class="text-muted opacity-75">
                <i class="fas fa-shield-alt me-1"></i> 
                Acceso seguro y monitoreado
            </small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
