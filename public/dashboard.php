<?php
require_once __DIR__ . '/../src/auth.php';
require_login();
require_once __DIR__ . '/../src/plans.php';

$user = current_user($pdo);
$plans = get_plans($pdo);
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis planes - MoodPlanned</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Hola, <?= htmlspecialchars($user['name']) ?> 👋</h3>
            <a href="create_plan.php" class="btn btn-primary">+ Nuevo plan</a>
        </div>

        <div class="row g-3">
            <?php foreach ($plans as $p): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm">
                        <?php if ($p['image']): ?>
                            <img src="<?= htmlspecialchars($p['image']) ?>" class="card-img-top" alt="Imagen del plan">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($p['title']) ?></h5>
                            <p class="card-text small text-muted"><?= htmlspecialchars($p['category']) ?></p>
                            <p><?= htmlspecialchars(substr($p['description'], 0, 100)) ?>...</p>
                            <a href="edit_plan.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                            <a href="delete_plan.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que quieres eliminar este plan?')">Eliminar</a>
                        </div>
                        <div class="card-footer small text-muted">
                            Creado por <?= htmlspecialchars($p['author'] ?? 'Desconocido') ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>