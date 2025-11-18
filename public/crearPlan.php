<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crear plan</title>
  <!--====== Bootstrap css ======-->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


  <!--====== Line Icons css ======-->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">



  <link rel="stylesheet" href="./assets/css/style.css" />

</head>

<body>
  <?php include 'include-header.php'; ?>
  <div class="form-wrapper">
    <div class="form-card">
      <h1>Crear plan </h1>

      <form>
        <div class="form-group smooth">
          <label for="titulo">Título del plan</label>
          <input type="text" class="input-field" id="titulo" placeholder="Ej: Pasear por el parque...">
        </div>

        <div class="form-group smooth">
          <label for="descripcion">Descripción</label>
          <textarea class="input-field textarea" id="descripcion" rows="3" placeholder="Describe tu plan..."></textarea>
        </div>

        <div class="form-group smooth">
          <label for="categoria">Categoría</label>
          <select class="input-field select" id="categoria">
            <option>Felicidad</option>
            <option>Tristeza</option>
            <option>Ira</option>
            <option>Miedo</option>
            <option>Raiva</option>
            <option>Sorpresa</option>
          </select>
        </div>

        <div class="form-group smooth">
          <label for="imagen">Imagen</label><br>
          <input type="file" class="file-input" id="imagen">
        </div>

        <button type="submit" class="btn-submit">Crear plan</button>
      </form>
    </div>
  </div>
<?php include 'include-footer.php'; ?>
</body>

</html>