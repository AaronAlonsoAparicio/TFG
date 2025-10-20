<?php
require_once __DIR__ . '/../src/config.php';

// Si el usuario está logueado, redirige al dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MoodPlanned</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6dd5fa, #ffffff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero {
            max-width: 480px;
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <div class="hero text-center">
        <h1 class="fw-bold mb-3">MoodPlanned</h1>
        <p class="text-muted mb-4">
            Encuentra planes cercanos según tu estado de ánimo 🌞
        </p>

        <a href="login.php" class="btn btn-primary w-100 mb-2">Iniciar sesión</a>
        <a href="register.php" class="btn btn-outline-secondary w-100">Crear cuenta</a>

        <hr class="my-4">
        <small class="text-muted">
            Desarrollado como Proyecto de Fin de Grado © <?= date('Y') ?>
        </small>
    </div>

</body>

</html>