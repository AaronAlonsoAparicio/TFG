<?php
require_once __DIR__ . '/../src/auth.php';
require_login();
require_once __DIR__ . '/../src/plans.php';

$user = current_user($pdo);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $cat = trim($_POST['category']);

    if ($title === '' || $desc === '') {
        $error = "El título y la descripción son obligatorios.";
    } else {
        $id = create_plan($pdo, [
            'title' => $title,
            'description' => $desc,
            'category' => $cat,
            'lat' => $_POST['lat'] ?? null,
            'lng' => $_POST['lng'] ?? null,
            'image' => $_POST['image'] ?? null,
            'created_by' => $user['id']
        ]);
        $success = "Plan creado correctamente 🎉";
        header("refresh:2;url=dashboard.php");
    }
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear plan - MoodPlanned</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-4">
        <h2>Nuevo Plan</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Título del plan</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="description" class="form-control" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Categoría</label>
                <input type="text" name="category" class="form-control" placeholder="ej. ocio, relax, deporte">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Latitud</label>
                    <input type="text" name="lat" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Longitud</label>
                    <input type="text" name="lng" class="form-control">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Imagen (URL)</label>
                <input type="text" name="image" class="form-control" placeholder="https://ejemplo.com/foto.jpg">
            </div>

            <button type="submit" class="btn btn-primary w-100">Guardar plan</button>
        </form>
    </div>
</body>

</html>