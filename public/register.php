<?php
require_once __DIR__ . '/../src/config.php';

// Inicializar variables
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    // Validación básica
    if ($name === '' || $email === '' || $password === '' || $confirm === '') {
        $error = "Por favor, completa todos los campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El email no es válido.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } elseif ($password !== $confirm) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Comprobar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Ya existe una cuenta con este email.";
        } else {
            // Registrar usuario
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hash]);

            $_SESSION['user_id'] = $pdo->lastInsertId();
            $success = "Registro completado con éxito. Redirigiendo...";
            header("refresh:2;url=./dashboard.php");
        }
    }
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro - MoodPlanned</title>
<link rel="stylesheet" href="/assets/css/register.css?v=7">

</head>



<body class="bg-light">

<section class="hero">
  <div class="collage">
    <img src="/assets/img/register1.jpg" alt="">
    <img src="/assets/img/register2.jpg" alt="">
    <img src="/assets/img/register3.jpg" alt="">
  </div>

  <h1 class="title-clip">Tu mood manda-<br>crea tu cuenta aquí</h1>
</section>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-4">

          <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
          <?php endif; ?>

          <form method="POST" action="">
            <div class="mb-3">
              <label for="name" class="form-label">Nombre completo</label>
              <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">Correo electrónico</label>
              <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Contraseña</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="mb-3">
              <label for="confirm" class="form-label">Confirmar contraseña</label>
              <input type="password" class="form-control" id="confirm" name="confirm" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Registrarse</button>
          </form>

          <div class="text-center mt-3">
            <small>¿Ya tienes una cuenta? <a href="login.php" class="text-decoration-none">Inicia sesión</a></small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="avatar-gallery">
  <img src="/assets/img/avatars/1.png" alt="">
  <img src="/assets/img/avatars/2.png" alt="">
  <img src="/assets/img/avatars/3.png" alt="">
</div>

<script>
document.querySelectorAll('.hero .collage img').forEach((img,i)=>{
  const angle = [-8,5,-10][i] || (Math.random()*10-5);
  img.style.setProperty('--angle', angle+'deg');
});
document.querySelectorAll('.avatar-gallery img').forEach(img=>{
  img.addEventListener('click',()=>{
    document.querySelectorAll('.avatar-gallery img').forEach(i=>i.classList.remove('selected'));
    img.classList.add('selected');
  });
});
</script>

</body>
</html>