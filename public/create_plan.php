<?php
require_once __DIR__ . '/../src/auth.php';
require_login();
require_once __DIR__ . '/../src/plans.php';

$user = current_user($pdo);
$error = '';
$success = '';

// Procesar formulario al enviar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $desc  = trim($_POST['description']);
    $cat   = trim($_POST['category']);

    if ($title === '' || $desc === '') {
        $error = "El título y la descripción son obligatorios.";
    } else {
        create_plan($pdo, [
            'title'       => $title,
            'description' => $desc,
            'category'    => $cat,
            'lat'         => $_POST['lat'] ?? null,
            'lng'         => $_POST['lng'] ?? null,
            'image'       => $_POST['image'] ?? null,
            'created_by'  => $user['id']
        ]);

        $success = "Plan creado correctamente 🎉";
        header("refresh:2;url=./dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crear plan</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Iconos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="./assets/css/style.css" />
</head>

<body>
  <?php include 'include-header.php'; ?>

  <div class="container mt-5 mb-5">
    <div class="card shadow p-4">
      <h1 class="mb-4">Crear plan</h1>

      <!-- Mensajes -->
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <!-- FORMULARIO FUNCIONAL -->
      <form method="POST">

        <div class="mb-3">
          <label for="titulo" class="form-label">Título del plan</label>
          <input type="text" class="form-control" name="title" id="titulo" required>
        </div>

        <div class="mb-3">
          <label for="descripcion" class="form-label">Descripción</label>
          <textarea class="form-control" name="description" id="descripcion" rows="3" required></textarea>
        </div>

        <div class="mb-3">
          <label for="categoria" class="form-label">Categoría</label>
          <select class="form-control" name="category" id="categoria">
            <option value="feliz">Felicidad</option>
            <option value="triste">Tristeza</option>
            <option value="ira">Ira</option>
            <option value="miedo">Miedo</option>
            <option value="raiva">Raiva</option>
            <option value="sorpresa">Sorpresa</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Latitud</label>
          <input type="text" name="lat" class="form-control">
        </div>

        <div class="mb-3">
          <label class="form-label">Longitud</label>
          <input type="text" name="lng" class="form-control">
        </div>

        <div class="mb-3">
          <label for="imagen" class="form-label">Imagen (URL)</label>
          <input type="text" class="form-control" name="image" id="imagen" placeholder="https://ejemplo.com/foto.jpg">
        </div>

        <button type="submit" class="btn btn-primary w-100">Crear plan</button>

      </form>
    </div>
  </div>

  <?php include 'include-footer.php'; ?>

</body>
</html>
