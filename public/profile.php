<?php
// profile.php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/gamification.php';
session_start();

$userId = $_SESSION['user_id'] ?? null;

if ($userId) {
    // Datos del usuario
    $stmt = $pdo->prepare("SELECT name, avatar, points, level FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    // Estadísticas
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_moods FROM moods WHERE user_id = ?");
    $stmt->execute([$userId]);
    $totalMoods = $stmt->fetch()['total_moods'] ?? 0;

    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_plans FROM plans WHERE created_by = ?");
    $stmt->execute([$userId]);
    $totalPlans = $stmt->fetch()['total_plans'] ?? 0;

    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_favorites FROM favorites WHERE user_id = ?");
    $stmt->execute([$userId]);
    $totalFavorites = $stmt->fetch()['total_favorites'] ?? 0;

    // Planes del usuario
    $stmt = $pdo->prepare("SELECT * FROM plans WHERE created_by = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $userPlans = $stmt->fetchAll();

    // Planes favoritos
    $stmt = $pdo->prepare("
        SELECT p.*
        FROM plans p
        JOIN favorites f ON p.id = f.plan_id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$userId]);
    $favorites = $stmt->fetchAll();

    // Logros del usuario
    $stmt = $pdo->prepare("
        SELECT a.*, ua.earned_at
        FROM achievements a
        LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
        ORDER BY a.id ASC
    ");
    $stmt->execute([$userId]);
    $achievements = $stmt->fetchAll();

    // Insignias del usuario
    $stmt = $pdo->prepare("
        SELECT b.*, ub.earned_at
        FROM badges b
        LEFT JOIN user_badges ub ON b.id = ub.badge_id AND ub.user_id = ?
        ORDER BY b.id ASC
    ");
    $stmt->execute([$userId]);
    $badges = $stmt->fetchAll();
} else {
    $user = null;
    $totalMoods = $totalPlans = $totalFavorites = 0;
    $userPlans = $favorites = $achievements = $badges = [];
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Perfil de Usuario - MoodPlaned</title>

    <!--====== Bootstrap css ======-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="./assets/css/profile.css" />
    <link rel="stylesheet" href="./assets/css/style.css" />
    <!--====== Grafico ======-->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!--==== Estilos Modal Perfil ====-->
    <style>
        /* ===== Aquí va todo tu CSS del modal que ya tenías ===== */
        /* No se cambia nada */
    </style>
</head>

<body>

    <?php include 'include-header.php'; ?>

    <div class="profile-header">
        <div class="profile-overlay">
            <img src="./assets/images/parque.jpg" alt="Foto de perfil">
        </div>
    </div>

    <div class="profile-info">
        <h3><?= htmlspecialchars($user['name'] ?? 'Usuario'); ?></h3>
        <p>· Amante de los viajes y las emociones ·</p>
        <div class="mt-3">
            <button type="button" class="btn btn-edit-perfil me-2" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                <i class="bi bi-pencil-square"></i> Editar perfil
            </button>
            <a href="./index.php"><button class="btn btn-outline-danger btn-logout-perfil"><i class="bi bi-box-arrow-right"></i> Cerrar
                sesión</button></a>
        </div>
    </div>

    <!-- Modal de edición de perfil (sin cambios en CSS ni estructura) -->
    <?php include 'include-profile-modal.php'; ?>

    <main class="container mt-5 pt-5">

        <!-- ESTADÍSTICAS -->
        <section class="mb-5">
            <div class="row g-3 justify-content-center text-center">
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="stats-card">
                        <h4><?= $totalPlans; ?></h4>
                        <p>Planes realizados</p>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="stats-card">
                        <h4><?= $totalMoods; ?></h4>
                        <p>Emociones más vividas</p>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="stats-card">
                        <h4><?= $totalFavorites; ?></h4>
                        <p>Destinos favoritos</p>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="stats-card">
                        <h4><?= $user['points'] ?? 0; ?></h4>
                        <p>Puntos</p>
                    </div>
                </div>
            </div>

            <!-- Aquí van tus gráficos (sin cambios) -->
        </section>

        <!-- PLANES GUARDADOS -->
        <section>
            <h4 class="section-title">Tus planes guardados</h4>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                <?php foreach($favorites as $plan): ?>
                <div class="col">
                    <div class="card plan-card-perfil border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal">
                        <div class="position-relative">
                            <img src="<?= htmlspecialchars($plan['image'] ?: './assets/images/parque.jpg'); ?>" class="card-img-top" alt="Plan image">

                            <div class="rating-badge">
                                <i class="bi bi-star-fill"></i> 4.5
                            </div>

                            <div class="card-overlay-perfil">
                                <h5 class="card-title mb-1"><?= htmlspecialchars($plan['title']); ?></h5>
                                <div class="d-flex justify-content-between align-items-center small">
                                    <span><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($plan['category']); ?></span>
                                    <span class="emoji">❤️</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- LOGROS DEL USUARIO -->
        <section class="mt-5">
            <h4 class="section-title">Tus logros</h4>

            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3 mt-3">
                <?php foreach($achievements as $ach): 
                    $locked = !$ach['earned_at'];
                    $icon = $ach['icon'] ?: 'assets/icons/ach-placeholder.svg';
                ?>
                    <div class="col">
                        <div class="achievement-card <?= $locked ? 'locked' : 'unlocked'; ?> text-center p-3 shadow-sm">
                            <img src="<?= htmlspecialchars($icon); ?>" class="achievement-icon mb-2" alt="icon">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($ach['name']); ?></h6>
                            <p class="small text-muted m-0">
                                <?= $locked ? 'Bloqueado' : ('Conseguido: ' . date('d M Y', strtotime($ach['earned_at']))); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-4">
                <h5 class="mb-3">Insignias</h5>
                <div class="row g-2">
                    <?php foreach($badges as $b): 
                        $has = !is_null($b['earned_at']);
                        $icon = $b['icon'] ?: 'assets/icons/badge-placeholder.svg';
                    ?>
                        <div class="col-auto">
                            <div class="d-flex flex-column align-items-center" style="width:90px;">
                                <img src="<?= htmlspecialchars($icon); ?>" style="width:64px;height:64px;<?= $has ? '' : 'filter:grayscale(100%);opacity:0.45;'; ?>" alt="badge">
                                <small class="mt-1 text-center"><?= htmlspecialchars($b['name']); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <!-- MODAL PLAN (sin cambios en CSS) -->
    <?php include 'include-plan-modal.php'; ?>

    <?php include 'include-footer.php'; ?>
</body>
</html>
