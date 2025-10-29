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

  <!-- Enlazamos SOLO tu CSS -->
  <link rel="stylesheet" href="assets/css/style.css?v=1">
</head>

<body>
  <div class="hero">
    <h1>MoodPlanned</h1>
    <p class="lead">Encuentra planes cercanos según tu estado de ánimo 🌞</p>

    <a href="login.php" class="btn-brand">Iniciar sesión</a>
    <a href="register.php" class="btn-ghost">Crear cuenta</a>

    <div class="hr"></div>
    <small>Desarrollado como Proyecto de Fin de Grado © <?= date('Y') ?></small>
  </div>
</body>

</html>
