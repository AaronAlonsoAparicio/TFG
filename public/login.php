<?php
session_start(); // INICIAR LA SESIÓN
require_once __DIR__ . '/../src/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Buscar usuario en la base de datos
    $stmt = $pdo->prepare("SELECT id, name, email, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);

        // Guardar datos del usuario en la sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        // REDIRECCIÓN AL DASHBOARD
        header('Location: ./dashboard.php'); // <- Archivo de destino
        exit;
    } else {
        $error = "Credenciales incorrectas.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoodPlanned</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilo propio -->
    <link rel="stylesheet" href="assets/css/login.css?v=1">
</head>
<body class="bg-light">

<section class="hero">
  <div class="collage">
    <img src="https://images.unsplash.com/photo-1658893804494-b5c43a641c55?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=1170" alt="">
    <img src="https://images.unsplash.com/photo-1735761013351-9eecd120e305?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=687" alt="">
    <img src="https://plus.unsplash.com/premium_photo-1673549535545-c30acf105478?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=1170" alt="">
    <img src="https://plus.unsplash.com/premium_photo-1679334171493-a5ed219782b1?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=687" alt="">
  </div>
  <h1 class="title-clip text-center">Iniciar Sesión</h1>
</section>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4"> 
                    <!-- ALERTA DE ERROR -->
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <!-- FORMULARIO -->
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="password" id="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
