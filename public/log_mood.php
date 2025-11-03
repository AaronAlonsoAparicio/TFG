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
  <link rel="stylesheet" href="assets/css/mood_register.css?v=2">
</head>
<body class="mp-bg">
  <div class="container">
    <a href="dashboard.php" class="btn btn-secondary-ghost">Volver</a>
    <h2>¿Cómo te sientes hoy?</h2>

    <?php if ($msg ?? false): ?>
      <div class="notice success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="POST" class="card" id="moodForm">
      <div class="emojis">
        <button type="button" class="emoji" data-mood="feliz">😄</button>
        <button type="button" class="emoji" data-mood="triste">😢</button>
        <button type="button" class="emoji" data-mood="relajado">😌</button>
        <button type="button" class="emoji" data-mood="enfadado">😠</button>
        <button type="button" class="emoji" data-mood="nervioso">😬</button>
      </div>

      <input type="hidden" name="mood" id="moodInput">

      <textarea id="note" name="note" placeholder="Notas (opcional)"></textarea>

      <button class="btn btn-primary" type="submit">Registrar</button>
    </form>
  </div>

  <script>
    const emojis = document.querySelectorAll('.emoji');
    const moodInput = document.getElementById('moodInput');
    emojis.forEach(e => {
      e.addEventListener('click', () => {
        emojis.forEach(b => b.classList.remove('active'));
        e.classList.add('active');
        moodInput.value = e.dataset.mood;
      });
    });

    document.getElementById('moodForm').addEventListener('submit', (ev)=>{
      if(!moodInput.value){
        ev.preventDefault();
        alert("Selecciona un estado de ánimo antes de registrar.");
      }
    });
  </script>
</body>
</html>
