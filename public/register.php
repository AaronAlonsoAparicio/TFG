<?php
session_start();
require_once "../src/config.php";

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 1. Recibir datos
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm = trim($_POST["confirm"]);

    // 2. Validaciones
    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $errors[] = "Todos los campos son obligatorios.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El email no es válido.";
    }

    if ($password !== $confirm) {
        $errors[] = "Las contraseñas no coinciden.";
    }

    if (strlen($password) < 6) {
        $errors[] = "La contraseña debe tener al menos 6 caracteres.";
    }

    // 3. Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $errors[] = "Este correo ya está registrado.";
    }

    // 4. Si no hay errores → guardar en la BD
    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $insert = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
        $insert->execute([$name, $email, $passwordHash]);

        $success = "Cuenta creada correctamente. ¡Ahora puedes iniciar sesión!";
        header("Location: login.php?registered=1");
        exit;
    }
}
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro - MoodPlanned</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="./assets/css/register.css">
</head>

<body class="bg-light">

  <div class="container-fluid py-5">
    <div class="row align-items-center">

      <!-- ===== CONTENEDOR IZQUIERDO ===== -->
      <div class="col-md-6 mb-4 mb-md-0">
        <section class="hero">
          <div class="collage">
            <img src="https://plus.unsplash.com/premium_photo-1682097522178-894a85756007?auto=format&fit=crop&q=80&w=1170" alt="">
            <img src="https://plus.unsplash.com/premium_photo-1661762437859-c41fa943637c?auto=format&fit=crop&q=80&w=1170" alt="">
            <img src="https://plus.unsplash.com/premium_photo-1683121126477-17ef068309bc?auto=format&fit=crop&q=80&w=1170" alt="">
            <img src="https://plus.unsplash.com/premium_photo-1685366454862-7f1b2d957fb1?auto=format&fit=crop&q=80&w=1170" alt="">
          </div>

          <h1 class="title-clip">Tu mood manda-<br>crea tu cuenta aquí</h1>
        </section>
      </div>

      <!-- ===== FORMULARIO ===== -->
      <div class="col-md-6">
        <div class="card shadow-lg border-0 rounded-4 mx-auto" style="max-width: 460px;">
          <div class="card-body p-4">

            <!-- Mostrar errores -->
            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <form method="POST" action="">
              <div class="mb-3">
                <label for="name" class="form-label">Nombre completo</label>
                <input type="text" class="form-control" id="name" name="name" required 
                  value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
              </div>

              <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" id="email" name="email" required 
                  value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
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
              <!-- Galería de avatares -->
              <div class="avatar-gallery mt-4">
                <img src="https://plus.unsplash.com/premium_photo-1738550163830-07bccfea3805?auto=format&fit=crop&q=80&w=1295" alt="">
                <img src="https://plus.unsplash.com/premium_photo-1738449258803-ffd12c905fd6?auto=format&fit=crop&q=80&w=1332" alt="">
                <img src="https://plus.unsplash.com/premium_photo-1738449258706-74c1dc94b988?auto=format&fit=crop&q=80&w=1332" alt="">
              </div>
            </form>


            <div class="text-center mt-3">
              <small>¿Ya tienes una cuenta? <a href="login.php" class="text-decoration-none">Inicia sesión</a></small>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>

  <script>
    document.querySelectorAll('.hero .collage img').forEach((img, i) => {
      const angle = [-8, 5, -10][i] || (Math.random() * 10 - 5);
      img.style.setProperty('--angle', angle + 'deg');
    });

    document.querySelectorAll('.avatar-gallery img').forEach(img => {
      img.addEventListener('click', () => {
        document.querySelectorAll('.avatar-gallery img').forEach(i => i.classList.remove('selected'));
        img.classList.add('selected');
      });
    });
  </script>

</body>
</html>