// TODO: COMPROBAR QUE SE AÑADAN TODOS LOS CAMPOS
<?php
// ============================
//  CREAR PLAN (BACKEND + FRONT EN MISMO FICHERO)
// ============================

// --- Conexión a la BD ---
$host = "localhost";
$db   = "moodplanned";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

session_start();
$userId = $_SESSION['user_id'] ?? 1; // ← TEMPORAL: cambiar cuando tengas login

$mensaje = "";

// ============================
//  PROCESAR FORMULARIO
// ============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $titulo      = trim($_POST['titulo'] ?? "");
    $descripcion = trim($_POST['descripcion'] ?? "");
    $categoria   = trim($_POST['categoria'] ?? "");
    $direccion   = trim($_POST['direccion'] ?? "");
    $lat         = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
    $lng         = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;

    if ($titulo === "" || $descripcion === "" || $categoria === "" || $direccion === "" || $lat === null || $lng === null) {
        $mensaje = "Todos los campos son obligatorios, incluida la ubicación en el mapa";
    } else {
        // ============================
        //  SUBIR IMAGEN
        // ============================
        $rutaImagen = null;

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {

            $nombreTmp = $_FILES['imagen']['tmp_name'];
            $nombreOriginal = $_FILES['imagen']['name'];

            $carpetaDestino = "./assets/images/";

            if (!file_exists($carpetaDestino)) {
                mkdir($carpetaDestino, 0777, true);
            }

            $nombreNuevo = uniqid("plan_") . "_" . basename($nombreOriginal);
            $rutaFinal = $carpetaDestino . $nombreNuevo;

            if (move_uploaded_file($nombreTmp, $rutaFinal)) {
                $rutaImagen = $rutaFinal;
            } else {
                $mensaje = "Error al subir la imagen";
            }
        }

        if ($mensaje === "") {
            $sql = "INSERT INTO plans (title, description, category, direccion, lat, lng, image, created_by)
                    VALUES (:title, :description, :category, :direccion, :lat, :lng, :image, :created_by)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":title"      => $titulo,
                ":description"=> $descripcion,
                ":category"   => $categoria,
                ":direccion"  => $direccion,
                ":lat"        => $lat,
                ":lng"        => $lng,
                ":image"      => $rutaImagen,
                ":created_by" => $userId
            ]);

            $mensaje = "Plan creado correctamente";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crear plan</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="./assets/css/style.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<body>
<?php include './include-header.php'; ?>

<div class="form-wrapper">
  <div class="form-card">
    <h1>Crear plan</h1>

    <?php if ($mensaje): ?>
      <div class="alert alert-info"> <?= $mensaje ?> </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
      <div class="form-group smooth">
        <label for="titulo">Título del plan</label>
        <input type="text" name="titulo" class="input-field" id="titulo" placeholder="Ej: Pasear por el parque...">
      </div>

      <div class="form-group smooth">
        <label for="descripcion">Descripción</label>
        <textarea name="descripcion" class="input-field textarea" id="descripcion" rows="3" placeholder="Describe tu plan..."></textarea>
      </div>

      <div class="form-group smooth">
        <label for="categoria">Categoría</label>
        <select name="categoria" class="input-field select" id="categoria">
          <option>Feliz</option>
          <option>Triste</option>
          <option>Enfadado</option>
          <option>Sorprendido</option>
          <option>Enamorado</option>
        </select>
      </div>

      <!-- Campo de dirección -->
      <div class="form-group smooth">
        <label for="address">Dirección</label>
        <div class="input-group">
            <input type="text" id="address" placeholder="Escribe una dirección..." class="form-control">
            <button type="button" id="searchBtn" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
        </div>
        <input type="hidden" id="direccion" name="direccion">
      </div>

      <!-- Mapa -->
      <div class="form-group smooth">
        <label for="map">Ubicación del plan</label>
        <div id="map" style="height: 300px; width: 100%; border: 1px solid #ccc;"></div>
        <input type="hidden" id="latitude" name="latitude">
        <input type="hidden" id="longitude" name="longitude">
      </div>

      <div class="form-group smooth">
        <label for="imagen">Imagen</label><br>
        <input type="file" name="imagen" class="file-input" id="imagen">
      </div>

      <button type="submit" class="btn-submit">Crear plan</button>
    </form>
  </div>
</div>

<script>
const API_KEY = "6bf5e1e9fbae4d92aa61c8875ff6f006";

document.addEventListener("DOMContentLoaded", function() {
    var map = L.map('map').setView([0, 0], 2);

    L.tileLayer(`https://maps.geoapify.com/v1/tile/osm-carto/{z}/{x}/{y}.png?apiKey=${API_KEY}`, {
        attribution: '&copy; OpenStreetMap',
        maxZoom: 20
    }).addTo(map);

    var marker = L.marker([0,0], {draggable:true}).addTo(map);

    function updateInputs(lat, lon){
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lon;
    }

    marker.on('dragend', function(e){
        var latlng = marker.getLatLng();
        updateInputs(latlng.lat, latlng.lng);
    });

    map.on('click', function(e){
        marker.setLatLng(e.latlng);
        updateInputs(e.latlng.lat, e.latlng.lng);
    });

    // Botón de búsqueda
    document.getElementById('searchBtn').addEventListener('click', function(){
        const query = document.getElementById('address').value;
        if(query){
            fetch(`https://api.geoapify.com/v1/geocode/search?text=${encodeURIComponent(query)}&apiKey=${API_KEY}`)
            .then(res => res.json())
            .then(data => {
                if(data.features && data.features.length > 0){
                    const feat = data.features[0];
                    const lat = feat.properties.lat;
                    const lon = feat.properties.lon;
                    const formattedAddress = feat.properties.formatted;

                    marker.setLatLng([lat, lon]);
                    map.setView([lat, lon], 15);
                    updateInputs(lat, lon);

                    // Guarda la dirección exacta en el input hidden
                    document.getElementById('direccion').value = formattedAddress;
                } else {
                    alert("No se encontró la dirección");
                }
            })
            .catch(err => alert("Error al buscar la dirección: "+err));
        }
    });
});
</script>

<?php include './include-footer.php'; ?>
</body>
</html>
