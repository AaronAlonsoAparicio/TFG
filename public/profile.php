<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/user.php';
require_login();
$user = current_user($pdo);
$msg = '';

if ($_POST) {
    $name = trim($_POST['name']);
    $avatar = $_POST['avatar'] ?? '';
    if ($name && update_user_profile($pdo, $user['id'], $name, $avatar)) {
        $msg = '<div class="alert alert-success">Perfil actualizado</div>';
        $user['name'] = $name;
        $user['avatar'] = $avatar;
    }
}
?>

<!DOCTYPE html>
<html><head><title>Perfil</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><div class="container py-4">
    <a href="dashboard.php" class="btn btn-secondary mb-3">Volver</a>
    <h3>Mi Perfil</h3>
    <?= $msg ?>
    <form method="POST" class="card p-3">
        <div class="mb-3"><input type="text" name="name" class="form-control" value="<?= $user['name'] ?>" required></div>
        <div class="mb-3"><input type="url" name="avatar" class="form-control" value="<?= $user['avatar'] ?? '' ?>" placeholder="URL avatar"></div>
        <div class="mb-3"><input type="text" class="form-control" value="<?= $user['points'] ?> puntos" disabled></div>
        <button class="btn btn-primary">Guardar</button>
    </form>
</div></body></html>