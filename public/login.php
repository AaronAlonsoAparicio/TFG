<?php
session_start();
require_once "../src/config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Buscar usuario
    $stmt = $pdo->prepare("SELECT id, name, email, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar contraseña
    if ($user && password_verify($password, $user["password_hash"])) {

        // Guardar datos en sesión
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_name"] = $user["name"];
        // Redirigir al dashboard
        header("Location:./dashboard.php");
        exit;
    } else {
        // Mensaje de error
        $_SESSION["login_error"] = "Credenciales incorrectas.";
        header("Location:./index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/register.css">
    <title>MoodPlanned</title>
</head>

<body class="bg-light">
    <div class="container-fluid py-5">
        <div class="row align-items-center">

            <!-- ===== CONTENEDOR IZQUIERDO: HERO, TEXTO Y FOTOS ===== -->
            <div class="col-md-6 mb-4 mb-md-0">
                <section class="hero">
                    <div class="collage">
                        <img src="https://images.unsplash.com/photo-1658893804494-b5c43a641c55?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1170" alt="">
                        <img src="https://images.unsplash.com/photo-1735761013351-9eecd120e305?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=687" alt="">
                        <img src="https://plus.unsplash.com/premium_photo-1673549535545-c30acf105478?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1170" alt="">
                        <img src="https://plus.unsplash.com/premium_photo-1679334171493-a5ed219782b1?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=687" alt="">
                    </div>

                    <h1 class="title-clip">Iniciar Sesion</h1>
                </section>
            </div>

            <!-- ===== CONTENEDOR DERECHO: FORMULARIO Y AVATARES ===== -->
            <div class="col-md-6">
                <div class="card shadow-lg border-0 rounded-4 mx-auto" style="max-width: 460px;">
                    <div class="card-body p-4">

                        <form method="POST" action="./login.php">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="password" required>
                                    <span class="input-group-text p-0" id="togglePassword">
                                        <img src="./assets/images/esconder.png" alt="Toggle Password" id="eyeIcon" style="width: 24px; height: 24px;">
                                    </span>
                                </div>
                            </div>


                            <button type="submit" class="btn btn-primary">Iniciar sesión</button>
                        </form>
                        <div class="text-center mt-3">
                            <small>¿No tienes una cuenta? <a href="./register.php" class="text-decoration-none">Registrarse</a></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para mostrar/ocultar contraseña -->
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const eyeIcon = document.querySelector('#eyeIcon');

        togglePassword.addEventListener('click', () => {
            const isPassword = password.type === 'password';
            password.type = isPassword ? 'text' : 'password';
            eyeIcon.src = isPassword ? './assets/images/ojo.png' : './assets/images/esconder.png';
        });
    </script>
</body>

</html>