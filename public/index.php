<?php
require_once __DIR__ . '/../src/config.php';
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
<link rel="stylesheet" href="assets/css/style.css?v=12">
</head>
<body class="hero-dark">

  <!-- Capa de imágenes animadas -->
  <div class="bg-stack" aria-hidden="true"></div>

  <!-- Título centrado -->
  <main class="hero-title">
    <h1 class="brand-title">
      <span>MOOD</span>
      <span>PLANNED</span>
    </h1>
    <p class="brand-sub">Planes · Amigos · Emociones</p>

    <div class="cta-row">
      <a href="register.php" class="btn-mood">Crear cuenta</a>
      <a href="login.php" class="btn-mood">Iniciar sesión</a>
    </div>
  </main>

  <script src="assets/js/hero.js?v=12"></script>
</body>
</html>
