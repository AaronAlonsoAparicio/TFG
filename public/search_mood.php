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
    $lat  = $_POST['lat'] ?? 40.4168;
    $lng  = $_POST['lng'] ?? -3.7038;
    $places = search_places_by_mood($mood, $lat, $lng);
    $places = array_slice($places, 0, 5); // limitar a 5 planes
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Buscar por Mood</title>
  <link rel="stylesheet" href="assets/css/search_mood.css?v=4">
</head>
<body>
  <main class="mp-root">
    <div class="mp-wrap">
      <a href="dashboard.php" class="back-link">← Volver</a>
      <h2 class="page-title">¿Cómo te sientes?</h2>

      <?php if ($type): ?>
        <div class="alert <?= $type === 'success' ? 'alert-success' : 'alert-danger' ?>">
          <?= htmlspecialchars($msg) ?>
        </div>
      <?php endif; ?>

      <!-- UI de 5 emociones (solo CSS, sin JS) -->
      <section class="mp-mood-ui" aria-labelledby="mp-mood-title">
        <h3 id="mp-mood-title" class="visually-hidden">Selecciona tu estado</h3>

        <!-- Radios ocultos -->
        <input type="radio" name="mood" id="mp-sad"     value="triste" >
        <input type="radio" name="mood" id="mp-happy"   value="feliz">
        <input type="radio" name="mood" id="mp-angry"   value="enfadado">
        <input type="radio" name="mood" id="mp-calm"    value="relajado">
        <input type="radio" name="mood" id="mp-nervous" value="nervioso">

        <!-- Emoticonos -->
        <div class="mp-mood-picker" role="tablist">
          <label class="mp-mood" for="mp-sad"     role="tab" aria-controls="mp-p-sad"     title="Triste">😢</label>
          <label class="mp-mood" for="mp-happy"   role="tab" aria-controls="mp-p-happy"   title="Feliz">😊</label>
          <label class="mp-mood" for="mp-angry"   role="tab" aria-controls="mp-p-angry"   title="Enfadado">😠</label>
          <label class="mp-mood" for="mp-calm"    role="tab" aria-controls="mp-p-calm"    title="Relajado">😌</label>
          <label class="mp-mood" for="mp-nervous" role="tab" aria-controls="mp-p-nervous" title="Nervioso">😬</label>
        </div>

        <!-- Listas de 5 frases (edítalas tú después) -->
        <div class="mp-mood-panels">
          <ul class="mp-panel" id="mp-p-sad" data-mood="mp-sad">
            <li>Ver una película o serie reconfortante</li>
            <li>Cocinar una comida sencilla que guste mucho.</li>
            <li>Dar un paseo por la naturaleza o zona tranquila.</li>
            <li>Dibujar, pintar o escribir pensamientos.</li>
            <li>Tomar un baño largo o ducha caliente.</li>
          </ul>
          <ul class="mp-panel" id="mp-p-happy" data-mood="mp-happy">
            <li>Sacar fotos de cosas que llamen la atención</li>
            <li>Cocinar algo nuevo para disfrutarlo. </li>
            <li> Cantar o bailar </li>
            <li>Hacer deporte o actividad física ligera. </li>
            <li>Ir a jugar a los bolos  </li>
           
          </ul>
          <ul class="mp-panel" id="mp-p-angry" data-mood="mp-angry">

          <li>Salir a correr o caminar rápido por un parque. </li>
          <li> Escuchar musica</li>
          <li> Hacer deporte </li>
          <li> Dar una vuelta con la bici </li>
          </ul>
          <ul class="mp-panel" id="mp-p-calm" data-mood="mp-calm">
            <li>Leer un libro en una cafeteria</li>
            <li>Salir a merendar </li>
            <li>Ver el atardecer</li>
            <li>Meditar o hacer yoga</li>
           
          </ul>
          <ul class="mp-panel" id="mp-p-nervous" data-mood="mp-nervous">
            <li>Respirar profundamente durante cinco minutos.</li>
            <li>Ir al teatro o a un monólogo</li>
            <li>Asistir a una clase o taller corto DE cerámica, pintura, cocina</li>
            <li>Sentarse en un mirador o junto a un río</li>
            <li>Visitar un parque botánico o jardín urbano.</li>
          </ul>
        </div>
      </section>

      <!-- Render de resultados $places si existen (sin Bootstrap) -->
      <?php if ($places): ?>
        <section class="places">
          <h3 class="places-title">Planes cercanos</h3>
          <div class="places-grid">
            <?php foreach ($places as $p): ?>
              <article class="place-card">
                <?php if ($p['image']): ?>
                  <img src="<?= $p['image'] ?>" alt="" class="place-img">
                <?php endif; ?>
                <div class="place-body">
                  <h4 class="place-title"><?= htmlspecialchars($p['title']) ?></h4>
                  <p class="place-desc"><?= htmlspecialchars($p['description']) ?></p>
                  <form method="POST" action="import_mood_place.php">
                    <input type="hidden" name="place" value='<?= htmlspecialchars(json_encode($p)) ?>'>
                    <button class="btn-primary">Importar</button>
                  </form>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <!-- Geolocalización (opcional para tu POST futuro) -->
      <form method="POST" class="geo-form">
        <input type="hidden" name="mood" id="geo-mood" value="">
        <input type="hidden" name="lat"  id="lat" value="">
        <input type="hidden" name="lng"  id="lng" value="">
      </form>
    </div>
  </main>

  <script>
    // Geolocalización para tu envío posterior si lo necesitas
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(p => {
        document.getElementById('lat').value = p.coords.latitude;
        document.getElementById('lng').value = p.coords.longitude;
      });
    }
  </script>
</body>
</html>
