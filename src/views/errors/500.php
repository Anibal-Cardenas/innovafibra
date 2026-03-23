<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Error del servidor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container text-center">
        <h1 class="display-1">500</h1>
        <h2>Error del Servidor</h2>
        <p class="lead">Ha ocurrido un error inesperado. Por favor, intente más tarde.</p>
        <a href="<?= BASE_URL ?? '/' ?>" class="btn btn-light btn-lg mt-3">
            Volver al Inicio
        </a>
    </div>
</body>
</html>
