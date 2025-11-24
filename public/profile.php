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
  <link rel="stylesheet" href="./assets/css/profile.css"> <!-- Tu CSS original -->
</head>
<body class="bg-light">

<div class="container-fluid py-5">
  <div class="row align-items-start">

    <!-- ===== CONTENEDOR IZQUIERDO: HERO, AVATAR Y PUNTOS ===== -->
    <div class="col-md-4 mb-4 mb-md-0">
      <section class="hero text-center">
        <?php if($user['avatar']): ?>
          <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="rounded-circle mb-3" style="width:120px;height:120px;">
        <?php else: ?>
          <img src="assets/icons/avatar-placeholder.svg" alt="Avatar" class="rounded-circle mb-3" style="width:120px;height:120px;">
        <?php endif; ?>
        <h2 class="title-clip"><?= htmlspecialchars($user['name']) ?></h2>
        <div class="mt-3">
          <p>Puntos: <?= $user['points'] ?> | Nivel: <?= $user['level'] ?></p>
        </div>
      </section>

      <!-- Estadísticas -->
      <div class="stats mt-4">
        <div class="stat-card shadow-sm p-3 mb-2 bg-white">
          <strong>Total Moods:</strong> <?= $totalMoods ?>
        </div>
        <div class="stat-card shadow-sm p-3 mb-2 bg-white">
          <strong>Total Planes:</strong> <?= $totalPlans ?>
        </div>
      </div>
    </div>

    <!-- ===== CONTENEDOR DERECHO: PLANES Y LOGROS ===== -->
    <div class="col-md-8">
      <!-- Planes creados -->
      <h3>Mis Planes</h3>
      <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
        <?php if(count($plans) > 0): ?>
          <?php foreach($plans as $plan): ?>
            <div class="col">
              <div class="plan-card shadow-sm p-3 bg-white">
                <h5><?= htmlspecialchars($plan['title']) ?></h5>
                <p><?= htmlspecialchars($plan['description'] ?? '') ?></p>
                <small><?= htmlspecialchars($plan['category'] ?? 'Sin categoría') ?></small>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col">
            <div class="plan-card shadow-sm p-3 bg-white text-center">
              No tienes planes creados.
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Planes favoritos -->
      <h3>Planes Favoritos</h3>
      <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
        <?php if(count($favorites) > 0): ?>
          <?php foreach($favorites as $fav): ?>
            <div class="col">
              <div class="plan-card shadow-sm p-3 bg-white">
                <h5><?= htmlspecialchars($fav['title']) ?></h5>
                <p><?= htmlspecialchars($fav['description'] ?? '') ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col">
            <div class="plan-card shadow-sm p-3 bg-white text-center">
              No tienes favoritos.
            </div>
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
              <div class="achievement-card unlocked shadow-sm p-3 text-center bg-white">
                <img src="<?= htmlspecialchars($icon) ?>" class="achievement-icon mb-2" alt="icon">
                <div><?= htmlspecialchars($ach['name']) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col">
            <div class="achievement-card text-center shadow-sm p-3 bg-white">
              No tienes logros aún.
            </div>
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
              <div class="badge-card shadow-sm p-3 text-center bg-white">
                <img src="<?= htmlspecialchars($icon) ?>" class="badge-icon mb-2" alt="badge">
                <div><?= htmlspecialchars($b['name']) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col">
            <div class="badge-card text-center shadow-sm p-3 bg-white">
              No tienes insignias aún.
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<script>
  // Tus scripts originales para collage de hero y selección de avatares
  document.querySelectorAll('.hero .collage img').forEach((img, i) => {
      const angle = [-8, 5, -10][i] || (Math.random() * 10 - 5);
      img.style.setProperty('--angle', angle + 'deg');
  });
  document.querySelectorAll('.avatar-gallery img').forEach(img => {
      img.addEventListener('click', () => {
          document.querySelectorAll('.avatar-gallery img').forEach(i => i.classList.remove('selected'));
          img.classList.add('selected');
      });
  });
</script>

</body>
</html>
