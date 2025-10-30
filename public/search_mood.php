<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/api_places.php';
require_login();

$user = current_user($pdo);
$places = [];
$msg = $_GET['msg'] ?? '';
$type = $_GET['type'] ?? '';

if ($_POST['mood'] ?? false) {
    $mood = $_POST['mood'];
    $lat = $_POST['lat'] ?? 40.4168;
    $lng = $_POST['lng'] ?? -3.7038;
    $places = search_places_by_mood($mood, $lat, $lng);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar por Mood</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <a href="dashboard.php" class="btn btn-secondary mb-3">Volver</a>
    <h2>¿Cómo te sientes?</h2>

    <?php if ($type): ?>
        <div class="alert alert-<?= $type === 'success' ? 'success' : 'danger' ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="card p-3 mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <select name="mood" class="form-select" required>
                    <option value="">Elige tu mood</option>
                    <option value="feliz">Feliz</option>
                    <option value="triste">Triste</option>
                    <option value="relajado">Relajado</option>
                    <option value="enérgico">Enérgico</option>
                    <option value="estresado">Estresado</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" step="any" name="lat" class="form-control" placeholder="Latitud" value="<?= $_POST['lat'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <input type="number" step="any" name="lng" class="form-control" placeholder="Longitud" value="<?= $_POST['lng'] ?? '' ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Buscar</button>
            </div>
        </div>
    </form>

    <?php if ($places): ?>
        <div class="row g-3">
            <?php foreach ($places as $p): ?>
                <div class="col-md-6">
                    <div class="card h-100">
                        <?php if ($p['image']): ?>
                            <img src="<?= $p['image'] ?>" class="card-img-top" style="height:150px; object-fit:cover;">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5><?= htmlspecialchars($p['title']) ?></h5>
                            <p class="text-muted flex-grow-1"><?= htmlspecialchars($p['description']) ?></p>
                            <form method="POST" action="import_mood_place.php" class="mt-auto">
                                <input type="hidden" name="place" value="<?= htmlspecialchars(json_encode($p)) ?>">
                                <button class="btn btn-success btn-sm w-100">Importar</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(p => {
        document.querySelector('[name="lat"]').value = p.coords.latitude;
        document.querySelector('[name="lng"]').value = p.coords.longitude;
    });
}
</script>
</body>
</html>