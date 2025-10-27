<?php
require_once __DIR__ . '/../src/auth.php';
require_login();
require_once __DIR__ . '/../src/plans.php';

$user = current_user($pdo);
$id = $_GET['id'] ?? null;
$plan = get_plan($pdo, $id);

if (!$plan || $plan['created_by'] != $user['id']) {
    die("No tienes permiso para editar este plan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ok = update_plan($pdo, [
        'id' => $id,
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'category' => $_POST['category'],
        'lat' => $_POST['lat'],
        'lng' => $_POST['lng'],
        'image' => $_POST['image'],
        'created_by' => $user['id']
    ]);
    if ($ok) {
        header("Location: dashboard.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar plan - MoodPlanned</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-4">
        <h2>Editar plan</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($plan['title']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="description" class="form-control"><?= htmlspecialchars($plan['description']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Categoría</label>
                <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($plan['category']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Imagen (URL)</label>
                <input type="text" name="image" class="form-control" value="<?= htmlspecialchars($plan['image']) ?>">
            </div>
            <button type="submit" class="btn btn-success">Guardar cambios</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>

</html>