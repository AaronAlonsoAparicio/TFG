<?php
// public/search_mood.php
require_once __DIR__ . '/../src/api_places.php';

$places = [];
$mood_selected = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['mood'])) {
    $mood_selected = $_POST['mood'];
    $lat = isset($_POST['lat']) && $_POST['lat'] !== '' ? (float)$_POST['lat'] : null;
    $lng = isset($_POST['lng']) && $_POST['lng'] !== '' ? (float)$_POST['lng'] : null;

    // Llamada a la función que consulta la BBDD
    $places = search_places_by_mood($mood_selected, $lat, $lng, 5);
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Buscar por Mood</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{ font-family:system-ui,-apple-system,Segoe UI,Roboto; background:#f4f0ea; color:#222; padding:24px; }
    .picker{ display:flex; gap:12px; margin-bottom:18px; }
    .mood{ width:72px; height:72px; display:grid; place-items:center; font-size:28px; background:#fff; border-radius:12px; cursor:pointer; box-shadow:0 6px 18px rgba(0,0,0,.08); }
    .mood:hover{ transform:scale(1.06); }
    .results{ margin-top:18px; display:grid; gap:12px; }
    .card{ background:#fff; padding:12px 14px; border-radius:10px; box-shadow:0 8px 20px rgba(0,0,0,.06); }
    .card h4{ margin:0 0 6px; }
    .meta{ font-size:12px; color:#666; margin-bottom:6px; }
    .noresults{ color:#666; }
  </style>
</head>
<body>
  <h1>¿Cómo te sientes?</h1>

  <!-- Formulario que se enviará automáticamente -->
  <form id="geo-form" method="POST" action="search_mood.php">
    <input type="hidden" name="mood" id="geo-mood" value="<?= htmlspecialchars($mood_selected) ?>">
    <input type="hidden" name="lat" id="lat" value="">
    <input type="hidden" name="lng" id="lng" value="">
  </form>

  <div class="picker" role="tablist" aria-label="Estados">
    <div class="mood" data-mood="feliz" title="Feliz">😊</div>
    <div class="mood" data-mood="triste" title="Triste">😢</div>
    <div class="mood" data-mood="enfadado" title="Enfadado">😠</div>
    <div class="mood" data-mood="relajado" title="Relajado">😌</div>
    <div class="mood" data-mood="nervioso" title="Nervioso">😬</div>
  </div>

  <?php if ($mood_selected): ?>
    <p>Has seleccionado: <strong><?= htmlspecialchars($mood_selected) ?></strong></p>
  <?php endif; ?>

  <div class="results">
    <?php if ($places): ?>
      <?php foreach ($places as $p): ?>
        <article class="card">
          <div class="meta"><?= htmlspecialchars($p['category']) ?></div>
          <h4><?= htmlspecialchars($p['title']) ?></h4>
          <p><?= nl2br(htmlspecialchars($p['description'])) ?></p>
          <?php if (!empty($p['image'])): ?>
            <img src="<?= htmlspecialchars($p['image']) ?>" alt="" style="max-width:160px;margin-top:8px;border-radius:8px;">
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <?php if ($mood_selected): ?>
        <div class="noresults">No hay planes para "<?= htmlspecialchars($mood_selected) ?>" — prueba otra emoción.</div>
      <?php else: ?>
        <div class="noresults">Haz clic en una carita para buscar planes.</div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

<script>
  // rellenar coordenadas si el navegador permite geolocalización
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(p => {
      document.getElementById('lat').value = p.coords.latitude;
      document.getElementById('lng').value = p.coords.longitude;
    }, ()=>{/* permiso denegado o error: se mantienen los valores vacíos */});
  }

  // comportamiento: clic en emoji -> poner mood en input y submit del formulario
  document.querySelectorAll('.mood').forEach(el => {
    el.addEventListener('click', () => {
      const mood = el.getAttribute('data-mood');
      document.getElementById('geo-mood').value = mood;
      // Opcional: feedback visual inmediato
      el.animate([{ transform: 'scale(1.08)' }, { transform:'scale(1)' }], { duration:180 });

      // enviar el formulario
      document.getElementById('geo-form').submit();
    });
  });
</script>
</body>
</html>
