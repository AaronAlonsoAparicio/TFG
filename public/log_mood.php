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
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Registrar Ánimo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/css/mood_register.css?v=1">
</head>
<body class="mp-bg">
  <div class="container">
    <a href="dashboard.php" class="btn btn-secondary-ghost">Volver</a>
    <h2>¿Cómo te sientes?</h2>

    <?php if ($msg ?? false): ?>
      <div class="notice success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="POST" class="card">
      <label class="label" for="mood">Selecciona tu estado</label>
      <div class="select">
        <select id="mood" name="mood" required>
          <option value="feliz">Feliz</option>
          <option value="triste">Triste</option>
          <option value="relajado">Relajado</option>
          <option value="enérgico">Enérgico</option>
          <option value="estresado">Estresado</option>
        </select>
        <span class="chevron" aria-hidden="true">▾</span>
      </div>

      <label class="label" for="note">Notas (opcional)</label>
      <textarea id="note" name="note" placeholder="Escribe algo si quieres..."></textarea>

      <button class="btn btn-primary" type="submit">Registrar</button>
    </form>
  </div>
</body>
</html>
