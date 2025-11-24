<?php
// profile.php
require_once __DIR__ . '/../src/config.php';
session_start();

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// ===== Datos del usuario =====
$stmt = $pdo->prepare("SELECT name, avatar, points, level FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// ===== Estadísticas =====
$stmt = $pdo->prepare("SELECT COUNT(*) AS total_moods FROM moods WHERE user_id = ?");
$stmt->execute([$userId]);
$totalMoods = $stmt->fetch()['total_moods'] ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) AS total_plans FROM plans WHERE created_by = ?");
$stmt->execute([$userId]);
$totalPlans = $stmt->fetch()['total_plans'] ?? 0;

// ===== Planes del usuario =====
$stmt = $pdo->prepare("SELECT * FROM plans WHERE created_by = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$plans = $stmt->fetchAll();

// ===== Planes favoritos =====
$stmt = $pdo->prepare("
    SELECT p.* 
    FROM plans p
    JOIN favorites f ON p.id = f.plan_id
    WHERE f.user_id = ?
");
$stmt->execute([$userId]);
$favorites = $stmt->fetchAll();

// ===== Logros del usuario =====
$stmt = $pdo->prepare("
    SELECT a.* 
    FROM achievements a
    JOIN user_achievements ua ON a.id = ua.achievement_id
    WHERE ua.user_id = ?
");
$stmt->execute([$userId]);
$achievements = $stmt->fetchAll();

// ===== Insignias =====
$stmt = $pdo->prepare("
    SELECT b.*
    FROM badges b
    JOIN user_badges ub ON b.id = ub.badge_id
    WHERE ub.user_id = ?
");
$stmt->execute([$userId]);
$badges = $stmt->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Perfil - MoodPlanned</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .stat-card, .plan-card, .achievement-card, .badge-card { border-radius: 10px; padding: 15px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); background: #fff; }
    .achievement-card.locked { opacity: 0.4; }
    .achievement-icon, .badge-icon { width:50px;height:50px; }
</style>
</head>
<body class="bg-light">

<div class="container py-5">
    <h1 class="mb-4">Hola, <?= htmlspecialchars($user['name']) ?></h1>

    <!-- Puntos y nivel -->
    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="stat-card">
                <h5>Puntos</h5>
                <p class="fs-4"><?= $user['points'] ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h5>Nivel</h5>
                <p class="fs-4"><?= $user['level'] ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h5>Total Moods</h5>
                <p class="fs-4"><?= $totalMoods ?></p>
            </div>
        </div>
    </div>

    <!-- Planes creados -->
    <h3>Mis Planes</h3>
    <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
        <?php if(count($plans) > 0): ?>
            <?php foreach($plans as $plan): ?>
                <div class="col">
                    <div class="plan-card">
                        <h5><?= htmlspecialchars($plan['title']) ?></h5>
                        <p><?= htmlspecialchars($plan['description'] ?? '') ?></p>
                        <small><?= htmlspecialchars($plan['category'] ?? 'Sin categoría') ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col">
                <div class="plan-card text-center">No tienes planes creados.</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Planes favoritos -->
    <h3>Planes Favoritos</h3>
    <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
        <?php if(count($favorites) > 0): ?>
            <?php foreach($favorites as $fav): ?>
                <div class="col">
                    <div class="plan-card">
                        <h5><?= htmlspecialchars($fav['title']) ?></h5>
                        <p><?= htmlspecialchars($fav['description'] ?? '') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col">
                <div class="plan-card text-center">No tienes favoritos.</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Logros -->
    <h3>Logros Desbloqueados</h3>
    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-3 mt-3">
        <?php if(count($achievements) > 0): ?>
            <?php foreach($achievements as $ach):
                $icon = $ach['icon'] ?: 'assets/icons/ach-placeholder.svg';
            ?>
                <div class="col">
                    <div class="achievement-card unlocked">
                        <img src="<?= htmlspecialchars($icon) ?>" class="achievement-icon mb-2" alt="icon">
                        <div><?= htmlspecialchars($ach['name']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col">
                <div class="achievement-card text-center">No tienes logros aún.</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Insignias -->
    <h3>Mis Insignias</h3>
    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-3 mt-3">
        <?php if(count($badges) > 0): ?>
            <?php foreach($badges as $b):
                $icon = $b['icon'] ?: 'assets/icons/ach-placeholder.svg';
            ?>
                <div class="col">
                    <div class="badge-card">
                        <img src="<?= htmlspecialchars($icon) ?>" class="badge-icon mb-2" alt="badge">
                        <div><?= htmlspecialchars($b['name']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col">
                <div class="badge-card text-center">No tienes insignias aún.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
