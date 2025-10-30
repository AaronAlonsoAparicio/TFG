<?php
require_once __DIR__ . '/../src/auth.php';
require_login();
require_once __DIR__ . '/../src/plans.php';
require_once __DIR__ . '/../src/favorites.php';
require_once __DIR__ . '/../src/reviews.php';

$user = current_user($pdo);
$plan_id = $_GET['id'] ?? null;
$plan = get_plan($pdo, $plan_id);
if (!$plan) die("Plan no encontrado");

$avg = get_average_rating($pdo, $plan_id);
$reviews = get_reviews($pdo, $plan_id);
$isFav = is_favorite($pdo, $user['id'], $plan_id);

// Manejar envío de reseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    add_review($pdo, $user['id'], $plan_id, $_POST['rating'], $_POST['comment']);
    header("Location:./plan.php?id=" . $plan_id);
    exit;
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($plan['title']) ?> - MoodPlanned</title>
</head>

<body class="bg-light">

    <div class="container py-4">
        <h2><?= htmlspecialchars($plan['title']) ?></h2>
        <p class="text-muted"><?= htmlspecialchars($plan['category']) ?></p>
        <p><?= nl2br(htmlspecialchars($plan['description'])) ?></p>

        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-outline-danger" id="favBtn">
                <?= $isFav ? '❤️ Quitar de favoritos' : '🤍 Añadir a favoritos' ?>
            </button>
            <span class="badge bg-warning text-dark">⭐ <?= $avg ?: 'Sin valoraciones' ?></span>
        </div>

        <hr>
        <h5>Deja tu reseña</h5>
        <form method="POST">
            <div class="mb-2">
                <label class="form-label">Puntuación (1–5)</label>
                <input type="number" name="rating" min="1" max="5" class="form-control" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Comentario</label>
                <textarea name="comment" class="form-control" rows="3"></textarea>
            </div>
            <button class="btn btn-success">Enviar reseña</button>
        </form>

        <hr>
        <h5>Reseñas recientes</h5>
        <?php if ($reviews): ?>
            <?php foreach ($reviews as $r): ?>
                <div class="border p-3 mb-2 bg-white rounded">
                    <strong><?= htmlspecialchars($r['author']) ?></strong>
                    <span class="text-warning">⭐ <?= $r['rating'] ?></span>
                    <p><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
                    <small class="text-muted"><?= $r['created_at'] ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay reseñas todavía.</p>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('favBtn').addEventListener('click', () => {
            fetch('/api/favorite_toggle.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'plan_id=<?= $plan_id ?>'
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'added') {
                        document.getElementById('favBtn').textContent = '❤️ Quitar de favoritos';
                    } else {
                        document.getElementById('favBtn').textContent = '🤍 Añadir a favoritos';
                    }
                });
        });
    </script>

</body>

</html>