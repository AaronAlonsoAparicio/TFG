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
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container py-4">
        <!-- NAVEGACIÓN PRINCIPAL -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Hola, <?= htmlspecialchars($user['name']) ?> 👋</h3>
            <div class="btn-group">
                <a href="create_plan.php" class="btn btn-primary">+ Nuevo plan</a>
                <a href="search_mood.php" class="btn btn-info">Buscar por Mood</a>
                <a href="log_mood.php" class="btn btn-warning">Registrar Ánimo</a>
                <a href="profile.php" class="btn btn-outline-secondary">Mi Perfil</a>
            </div>
        </div>

        <!-- LISTA DE PLANES -->
        <div class="row g-3">
            <?php if (empty($plans)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        No tienes planes aún. <a href="create_plan.php">Crea uno</a> o <a href="search_mood.php">busca por tu mood</a>.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($plans as $p): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card shadow-sm h-100">
                            <?php if ($p['image']): ?>
                                <img src="<?= htmlspecialchars($p['image']) ?>" class="card-img-top" style="height:180px; object-fit:cover;" alt="Imagen del plan">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($p['title']) ?></h5>
                                <p class="card-text small text-muted mb-1"><?= htmlspecialchars($p['category']) ?></p>
                                <p class="flex-grow-1"><?= htmlspecialchars(substr($p['description'], 0, 120)) ?>...</p>
                                <div class="mt-auto">
                                    <a href="plan.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Ver detalles</a>
                                    <a href="edit_plan.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                    <a href="delete_plan.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este plan?')">Eliminar</a>
                                </div>
                            </div>
                            <div class="card-footer small text-muted">
                                Creado por <?= htmlspecialchars($p['author'] ?? 'Tú') ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>