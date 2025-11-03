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

  <!-- Fondo arena + emojis grandes + capa global de imágenes flotantes -->
  <style>
    body{ background:#f4e7d0; color:#3b3b3b; font-family:"Inter",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif; }

    /* Capa flotante a pantalla completa */
    .mp-float-layer.full{
      position:fixed;
      inset:0;
      z-index:0;                /* por detrás del contenido */
      overflow:hidden;
      pointer-events:none;
      filter:saturate(.96) contrast(.98);
    }
    /* Asegurar contenido por delante de la capa */
    .mp-root, .mp-wrap, .mp-mood-picker, .mp-mood-panels, .places, .geo-form, .alert, .back-link, .page-title{
      position:relative;
      z-index:1;
    }

    /* Stage */
    .mp-mood-stage{ position:relative; isolation:isolate; }

    /* Picker contenedor claro con sombra */
    .mp-mood-picker{
      background:#f8f2e5;
      border-radius:18px;
      padding:18px 20px;
      box-shadow:0 12px 28px rgba(0,0,0,.18);
      gap:16px;
      display:flex; align-items:center; justify-content:center; flex-wrap:wrap;
    }

    /* Emojis más grandes */
    .mp-mood{ width:96px; height:96px; font-size:3rem; border-radius:20px; display:grid; place-items:center;
      background:#fff; color:#111; box-shadow:0 10px 22px rgba(0,0,0,.20); cursor:pointer; user-select:none;
      transition:transform .12s ease, box-shadow .18s ease, background .2s ease, color .2s ease, outline-color .2s ease;
    }
    .mp-mood:hover{ transform:scale(1.12); }
    #mp-sad:checked     ~ .mp-mood-picker label[for="mp-sad"],
    #mp-happy:checked   ~ .mp-mood-picker label[for="mp-happy"],
    #mp-angry:checked   ~ .mp-mood-picker label[for="mp-angry"],
    #mp-calm:checked    ~ .mp-mood-picker label[for="mp-calm"],
    #mp-nervous:checked ~ .mp-mood-picker label[for="mp-nervous"]{
      background:#d9c49b; color:#fff; outline:3px solid rgba(60,50,30,.18); outline-offset:3px; transform:scale(1.18);
    }

    /* Imágenes flotantes */
    .float-img{
      position:absolute; bottom:-20%;
      opacity:0; border-radius:12px; user-select:none; pointer-events:none;
      box-shadow:0 8px 24px rgba(0,0,0,.18);
      animation-name: mp-fly; animation-timing-function: linear; animation-fill-mode: forwards;
      will-change: transform, opacity; transform-origin:center; filter:blur(.2px);
    }
    @keyframes mp-fly{
      0%   { transform: translate(var(--x,0), 0) scale(var(--s,1)) rotate(var(--r,0deg)); opacity:0; }
      8%   { opacity:.35; }
      70%  { opacity:.35; }
      100% { transform: translate(calc(var(--x,0) + var(--drift, 40px)), -120vh) scale(var(--s,1)) rotate(calc(var(--r,0deg) + var(--spin, 10deg))); opacity:0; }
    }
  </style>
</head>
<body>
  <!-- Capa global a pantalla completa -->
  <div class="mp-float-layer full" aria-hidden="true"></div>

  <main class="mp-root">
    <div class="mp-wrap">
      <a href="dashboard.php" class="back-link">← Volver</a>
      <h2 class="page-title">¿Cómo te sientes?</h2>

      <?php if ($type): ?>
        <div class="alert <?= $type === 'success' ? 'alert-success' : 'alert-danger' ?>">
          <?= htmlspecialchars($msg) ?>
        </div>
      <?php endif; ?>

      <!-- UI emociones -->
      <section class="mp-mood-ui" aria-labelledby="mp-mood-title">
        <h3 id="mp-mood-title" class="visually-hidden">Selecciona tu estado</h3>

        <!-- Radios ocultos -->
        <input type="radio" name="mood" id="mp-sad"     value="triste">
        <input type="radio" name="mood" id="mp-happy"   value="feliz">
        <input type="radio" name="mood" id="mp-angry"   value="enfadado">
        <input type="radio" name="mood" id="mp-calm"    value="relajado">
        <input type="radio" name="mood" id="mp-nervous" value="nervioso">

        <div class="mp-mood-stage">
          <div class="mp-mood-picker" role="tablist">
            <label class="mp-mood" for="mp-sad"     role="tab" aria-controls="mp-p-sad"     title="Triste"    data-mood="triste">😢</label>
            <label class="mp-mood" for="mp-happy"   role="tab" aria-controls="mp-p-happy"   title="Feliz"     data-mood="feliz">😊</label>
            <label class="mp-mood" for="mp-angry"   role="tab" aria-controls="mp-p-angry"   title="Enfadado"  data-mood="enfadado">😠</label>
            <label class="mp-mood" for="mp-calm"    role="tab" aria-controls="mp-p-calm"    title="Relajado"  data-mood="relajado">😌</label>
            <label class="mp-mood" for="mp-nervous" role="tab" aria-controls="mp-p-nervous" title="Nervioso"  data-mood="nervioso">😬</label>
          </div>
        </div>

        <!-- Listas -->
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
            <li>Cocinar algo nuevo para disfrutarlo.</li>
            <li>Cantar o bailar</li>
            <li>Hacer deporte o actividad física ligera</li>
            <li>Ir a jugar a los bolos</li>
          </ul>
          <ul class="mp-panel" id="mp-p-angry" data-mood="mp-angry">
            <li>Salir a correr o caminar rápido por un parque</li>
            <li>Escuchar música</li>
            <li>Hacer deporte</li>
            <li>Dar una vuelta con la bici</li>
            <li>Subir a un mirador y observar</li>
          </ul>
          <ul class="mp-panel" id="mp-p-calm" data-mood="mp-calm">
            <li>Leer un libro en una cafetería</li>
            <li>Salir a merendar</li>
            <li>Ver el atardecer</li>
            <li>Meditar o hacer yoga</li>
            <li>Pasear sin prisa</li>
          </ul>
          <ul class="mp-panel" id="mp-p-nervous" data-mood="mp-nervous">
            <li>Respirar profundamente durante cinco minutos</li>
            <li>Ir al teatro o a un monólogo</li>
            <li>Taller corto de cerámica, pintura o cocina</li>
            <li>Sentarse en un mirador o junto a un río</li>
            <li>Visitar un parque botánico o jardín urbano</li>
          </ul>
        </div>
      </section>

      <?php if ($places): ?>
        <section class="places">
          <h3 class="places-title">Planes cercanos</h3>
          <div class="places-grid">
            <?php foreach ($places as $p): ?>
              <article class="place-card">
                <?php if (!empty($p['image'])): ?>
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

      <form method="POST" class="geo-form" id="geo-form">
        <input type="hidden" name="mood" id="geo-mood" value="">
        <input type="hidden" name="lat"  id="lat" value="">
        <input type="hidden" name="lng"  id="lng" value="">
      </form>
    </div>
  </main>

  <script>
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(p => {
        document.getElementById('lat').value = p.coords.latitude;
        document.getElementById('lng').value = p.coords.longitude;
      });
    }

    (function(){
      const layer = document.querySelector('.mp-float-layer.full');
      const labels = document.querySelectorAll('.mp-mood-picker .mp-mood');
      const inputMood = document.getElementById('geo-mood');

      const IMGSETS = {
        feliz: [
          "https://images.unsplash.com/photo-1590698933947-a202b069a861?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=735",
          "https://images.unsplash.com/photo-1542596594-649edbc13630?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1974",
          "https://images.unsplash.com/photo-1509909756405-be0199881695?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1170"
        ],
        triste: [
          "https://images.unsplash.com/photo-1701989182264-7f7e8e641bdf?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1171",
          "https://images.unsplash.com/photo-1622613618885-17a2ef76865e?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1171",
          "https://images.unsplash.com/photo-1729062188586-0406bfaf5b18?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1170"
        ],
        relajado: [
          "https://images.unsplash.com/photo-1577253313708-cab167d2c474?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1138",
          "https://images.unsplash.com/photo-1512438248247-f0f2a5a8b7f0?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=764",
          "https://plus.unsplash.com/premium_photo-1709993971374-a35f36863c04?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=735"
        ],
        enfadado: [
          "https://images.unsplash.com/photo-1503525537183-c84679c9147f?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1170",
          "https://plus.unsplash.com/premium_photo-1739123854182-339bb87b89fa?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1112",
          "https://images.unsplash.com/photo-1578973615737-d800fbb6dc10?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=745"
        ],
        nervioso: [
          "https://images.unsplash.com/photo-1506126613408-eca07ce68773?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=799",
          "https://images.unsplash.com/photo-1518708909080-704599b19972?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=687",
          "https://images.unsplash.com/photo-1689587156323-b3db91e25028?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=687"
        ]
      };

      function clearLayer(){ if(!layer) return; layer.querySelectorAll('.float-img').forEach(n=>n.remove()); }
      function rand(min, max){ return Math.random()*(max-min)+min; }
      function pick(arr){ return arr[Math.floor(Math.random()*arr.length)]; }

      function filterByMood(mood){
        switch(mood){
          case 'feliz':     return 'brightness(1.05) saturate(1.1)';
          case 'triste':    return 'grayscale(.25) brightness(.95)';
          case 'relajado':  return 'saturate(.95) hue-rotate(-8deg)';
          case 'enfadado':  return 'contrast(1.08) saturate(1.15)';
          case 'nervioso':  return 'grayscale(.15) contrast(1.02)';
          default:          return 'none';
        }
      }

      function spawnImagesForMood(mood){
        if(!layer) return;
        clearLayer();

        const set = IMGSETS[mood] || [];
        const COUNT = 18;
        const vw = window.innerWidth;

        for(let i=0;i<COUNT;i++){
          const img = new Image();
          img.className = 'float-img';
          img.src = pick(set);

          const left = rand(-0.1*vw, 0.9*vw);
          const size = rand(100, 180);
          const dur  = rand(9, 16);
          const del  = rand(0, 3);
          const drift= rand(-200, 200) + 'px';
          const rot  = rand(-12, 12) + 'deg';
          const spin = rand(-18, 18) + 'deg';
          const scale= rand(0.9, 1.15);

          img.style.left = left + 'px';
          img.style.width = size + 'px';
          img.style.animationDuration = dur + 's';
          img.style.animationDelay    = del + 's';
          img.style.setProperty('--x','0px');
          img.style.setProperty('--drift',drift);
          img.style.setProperty('--r',rot);
          img.style.setProperty('--spin',spin);
          img.style.setProperty('--s',scale);
          img.style.filter = filterByMood(mood);

          img.addEventListener('animationend',()=>img.remove());
          layer.appendChild(img);
        }
      }

      labels.forEach(l=>{
        l.addEventListener('click',()=>{
          const mood = l.dataset.mood;
          inputMood.value = mood;
          spawnImagesForMood(mood);
        });
      });
    })();
  </script>
</body>
</html>
