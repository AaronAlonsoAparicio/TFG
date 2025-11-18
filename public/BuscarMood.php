<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Filtrar por Emociones</title>

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
  <!-- FILTROS -->
  <div class="container text-center my-4 ">
    <div class="btn-group flex-wrap filtros" role="group">
      <button class="btn btn-outline-primary filter-btn active" data-filter="all" title="Todas">🌈</button>
      <button class="btn btn-outline-primary filter-btn" data-filter="alegria" title="Feliz">😊</button>
      <button class="btn btn-outline-primary filter-btn" data-filter="tristeza" title="Tristeza">😢</button>
      <button class="btn btn-outline-primary filter-btn" data-filter="ira" title="enfadado">😡</button>
      <button class="btn btn-outline-primary filter-btn" data-filter="sorpresa" title="relajado">😲</button>
      <button class="btn btn-outline-primary filter-btn" data-filter="amor" title="nervioso">😍</button>
    </div>
  </div>

  <!-- GRID DE TARJETAS -->
  <div class="container">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">

      <!-- Alegría -->
      <div class="col emotion-item alegria">
        <div class="card plan-card border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal">
          <div class="position-relative">
            <img src="./assets/images/parque.jpg" class="card-img-top" alt="Plan image">

            <div class="rating-badge">
              <i class="bi bi-star-fill"></i> 4.8
            </div>

            <div class="card-overlay p-3">
              <h5 class="card-title mb-1">Festival de la Risa</h5>
              <div class="d-flex justify-content-between align-items-center">
                <div class="text-light small"><i class="bi bi-geo-alt"></i> Brasil</div>
                <div><span class="emoji">😄</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- MODAL -->
      <div class="modal fade" id="planModal" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dimensiones">
          <div class="modal-content border-0 rounded-4 overflow-hidden">
            <img src="./assets/images/parque.jpg" class="img-fluid" alt="plan" />
            <div class="modal-body p-4">
              <h3 class="fw-bold mb-3" id="planModalLabel">Explore Culture</h3>
              <div class="d-flex align-items-center text-muted mb-3">
                <i class="bi bi-geo-alt me-2 text-primary"></i> Ubicación
              </div>
              <p class="text-secondary mb-4">
                Aquí aparecerá la descripción del plan seleccionado hola.
              </p>
              <div class="d-flex justify-content-start">
                <button class="btn btn-outline-primary px-4 me-2" type="button">Editar</button>
                <button class="btn btn-outline-danger" type="button">Eliminar</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col emotion-item alegria">
        <div class="card plan-card border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal1">
          <div class="position-relative">
            <img src="./assets/images/parque.jpg" class="card-img-top" alt="Plan image">

            <div class="rating-badge">
              <i class="bi bi-star-fill"></i> 4.8
            </div>

            <div class="card-overlay p-3">
              <h5 class="card-title mb-1">Festival de la Risa</h5>
              <div class="d-flex justify-content-between align-items-center">
                <div class="text-light small"><i class="bi bi-geo-alt"></i> Brasil</div>
                <div><span class="emoji">😄</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- MODAL -->
      <div class="modal fade" id="planModal1" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content border-0 rounded-4 overflow-hidden">
            <img src="./assets/images/parque.jpg" class="img-fluid" alt="plan" />
            <div class="modal-body p-4">
              <h3 class="fw-bold mb-3" id="planModalLabel">Explore Culture</h3>
              <div class="d-flex align-items-center text-muted mb-3">
                <i class="bi bi-geo-alt me-2 text-primary"></i> Ubicación
              </div>
              <p class="text-secondary mb-4">
                Aquí aparecerá la descripción del plan seleccionado adios.
              </p>
              <div class="d-flex justify-content-start">
                <button class="btn btn-outline-primary px-4 me-2" type="button">Editar</button>
                <button class="btn btn-outline-danger" type="button">Eliminar</button>
              </div>
            </div>
          </div>
        </div>
      </div>

       <div class="col emotion-item tristeza">
        <div class="card plan-card border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal">
          <div class="position-relative">
            <img src="./assets/images/parque.jpg" class="card-img-top" alt="Plan image">

            <div class="rating-badge">
              <i class="bi bi-star-fill"></i> 4.8
            </div>

            <div class="card-overlay p-3">
              <h5 class="card-title mb-1">Festival de la Risa</h5>
              <div class="d-flex justify-content-between align-items-center">
                <div class="text-light small"><i class="bi bi-geo-alt"></i> Brasil</div>
                <div><span class="emoji">😄</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- MODAL -->
      <div class="modal fade" id="planModal" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content border-0 rounded-4 overflow-hidden">
            <img src="./assets/images/parque.jpg" class="img-fluid" alt="plan" />
            <div class="modal-body p-4">
              <h3 class="fw-bold mb-3" id="planModalLabel">Explore Culture</h3>
              <div class="d-flex align-items-center text-muted mb-3">
                <i class="bi bi-geo-alt me-2 text-primary"></i> Ubicación
              </div>
              <p class="text-secondary mb-4">
                Aquí aparecerá la descripción del plan seleccionado hola.
              </p>
              <div class="d-flex justify-content-start">
                <button class="btn btn-outline-primary px-4 me-2" type="button">Editar</button>
                <button class="btn btn-outline-danger" type="button">Eliminar</button>
              </div>
            </div>
          </div>
        </div>
      </div>

       <div class="col emotion-item alegria">
        <div class="card plan-card border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal">
          <div class="position-relative">
            <img src="./assets/images/parque.jpg" class="card-img-top" alt="Plan image">

            <div class="rating-badge">
              <i class="bi bi-star-fill"></i> 4.8
            </div>

            <div class="card-overlay p-3">
              <h5 class="card-title mb-1">Festival de la Risa</h5>
              <div class="d-flex justify-content-between align-items-center">
                <div class="text-light small"><i class="bi bi-geo-alt"></i> Brasil</div>
                <div><span class="emoji">😄</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- MODAL -->
      <div class="modal fade" id="planModal" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content border-0 rounded-4 overflow-hidden">
            <img src="./assets/images/parque.jpg" class="img-fluid" alt="plan" />
            <div class="modal-body p-4">
              <h3 class="fw-bold mb-3" id="planModalLabel">Explore Culture</h3>
              <div class="d-flex align-items-center text-muted mb-3">
                <i class="bi bi-geo-alt me-2 text-primary"></i> Ubicación
              </div>
              <p class="text-secondary mb-4">
                Aquí aparecerá la descripción del plan seleccionado hola.
              </p>
              <div class="d-flex justify-content-start">
                <button class="btn btn-outline-primary px-4 me-2" type="button">Editar</button>
                <button class="btn btn-outline-danger" type="button">Eliminar</button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
<?php include 'include-footer.php'; ?>

  <!-- FILTRO JS -->
  <script>
    const filterButtons = document.querySelectorAll('.filter-btn');
    const emotionItems = document.querySelectorAll('.emotion-item');

    filterButtons.forEach(button => {
      button.addEventListener('click', () => {
        filterButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        const filter = button.getAttribute('data-filter');

        emotionItems.forEach(item => {
          if (filter === 'all' || item.classList.contains(filter)) {
            item.style.display = 'block';
          } else {
            item.style.display = 'none';
          }
        });
      });
    });
  </script>
  <script>
    const tooltipTriggerList = document.querySelectorAll('[title]')
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el))
  </script>

</body>

</html>