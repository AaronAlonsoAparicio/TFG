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

    if ($titulo === "" || $descripcion === "" || $categoria === "") {
        $mensaje = "Todos los campos son obligatorios";
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
            $sql = "INSERT INTO plans (title, description, category, image, created_by)
                    VALUES (:title, :description, :category, :image, :created_by)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":title"       => $titulo,
                ":description" => $descripcion,
                ":category"    => $categoria,
                ":image"       => $rutaImagen,
                ":created_by"  => $userId
            ]);

            $mensaje = "Plan creado correctamente";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crear plan</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="./assets/css/style.css" />
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

        <div class="form-group smooth">
          <label for="imagen">Imagen</label><br>
          <input type="file" name="imagen" class="file-input" id="imagen">
        </div>

        <button type="submit" class="btn-submit">Crear plan</button>
      </form>
    </div>
</div>
<?php include './include-footer.php'; ?>
</body>
</html>
