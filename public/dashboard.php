<?php
require_once __DIR__ . '/../src/auth.php';

// Proteger la página
require_login();

// Obtener datos del usuario
$user = current_user($pdo);
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel - MoodPlanned</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-4">
        <h2>Bienvenido, <?= htmlspecialchars($user['name']) ?> 👋</h2>
        <p>Tus puntos: <?= (int)$user['points'] ?></p>
        <a href="logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
    </div>
</body>

</html>