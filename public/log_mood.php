<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/user.php';
require_login();

if ($_POST['mood'] ?? false) {
    $stmt = $pdo->prepare("INSERT INTO moods (user_id, mood, note) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_POST['mood'], $_POST['note'] ?? '']);
    add_user_points($pdo, $_SESSION['user_id'], 3);
    $msg = "¡Ánimo registrado! +3 puntos";
}
?>

<!DOCTYPE html>
<html><head><title>Registrar Ánimo</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><div class="container py-4">
    <a href="dashboard.php" class="btn btn-secondary mb-3">Volver</a>
    <h3>¿Cómo te sientes?</h3>
    <?php if ($msg ?? false): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <form method="POST" class="card p-3">
        <select name="mood" class="form-select mb-3" required>
            <option value="feliz">Feliz</option>
            <option value="triste">Triste</option>
            <option value="relajado">Relajado</option>
            <option value="enérgico">Enérgico</option>
            <option value="estresado">Estresado</option>
        </select>
        <textarea name="note" class="form-control mb-3" placeholder="Notas (opcional)"></textarea>
        <button class="btn btn-primary">Registrar</button>
    </form>
</div></body></html>