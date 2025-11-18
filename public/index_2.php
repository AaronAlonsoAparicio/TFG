<?php
// ===================================================================================
// CONFIGURACIÓN Y LÓGICA PHP PARA MYSQL
// ===================================================================================

// --- 1. CONFIGURACIÓN DE LA BASE DE DATOS ---
// **DEBES** cambiar estos valores por los de tu servidor MySQL
$db_host = 'localhost';
$db_user = 'tu_usuario_mysql';
$db_pass = 'tu_contraseña_mysql';
$db_name = 'tu_base_de_datos';

// Nombre de la tabla donde guardas el estado de ánimo.
// La tabla debe tener al menos las columnas: user_id (VARCHAR), mood (VARCHAR), last_check (DATETIME)
$mood_table = 'user_mood_tracker';

// La lógica asume que tienes una variable de sesión o un mecanismo
// para obtener el ID de usuario autenticado.
// **ADAPTA ESTA LÍNEA** para obtener el ID real del usuario.
$current_user_id = 'USER_12345'; // <-- Reemplaza esto con $_SESSION['user_id'] o similar

// Conexión a la base de datos
try {
  $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $db_connected = true;
} catch (PDOException $e) {
  // En un entorno de producción, esto debería ser un registro de error, no un 'echo'.
  // Para depuración, lo dejamos visible.
  // die("Error de conexión a la BBDD: " . $e->getMessage());
  $db_connected = false;
  // Si la conexión falla, asumimos que no hay registro de ánimo y se muestra el contenido principal.
}

// --- 2. FUNCIONES DE LÓGICA ---

/**
 * Verifica si han pasado 24 horas desde el último registro de ánimo.
 * @return array ['required' => bool, 'message' => string]
 */
function check_mood_required($pdo, $user_id, $table_name)
{
  if (!$pdo) {
    return ['required' => true, 'message' => 'Error de conexión a BBDD. Se mostrará el modal.'];
  }

  $sql = "SELECT last_check FROM $table_name WHERE user_id = :user_id ORDER BY last_check DESC LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['user_id' => $user_id]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$result) {
    // No hay registros. Se requiere el estado de ánimo.
    return ['required' => true, 'message' => 'Primer ingreso, estado de ánimo requerido.'];
  }

  $last_check_timestamp = strtotime($result['last_check']);
  $one_day_ago = time() - (24 * 60 * 60);

  if ($last_check_timestamp < $one_day_ago) {
    // Más de 24 horas. Se requiere el estado de ánimo.
    return ['required' => true, 'message' => 'Más de 24h, estado de ánimo requerido.'];
  } else {
    // Menos de 24 horas. No se requiere.
    return ['required' => false, 'message' => 'Menos de 24h, mostrando contenido principal.'];
  }
}

/**
 * Guarda el nuevo estado de ánimo con la marca de tiempo actual.
 * @param string $mood El estado de ánimo seleccionado (e.g., 'feliz', 'triste').
 * @return bool True si se guarda correctamente.
 */
function save_user_mood($pdo, $user_id, $table_name, $mood)
{
  if (!$pdo) return false;

  // Usamos INSERT ON DUPLICATE KEY UPDATE si quieres que solo haya una entrada por usuario,
  // pero para registrar el historial de ánimo, un simple INSERT es mejor:
  $sql = "INSERT INTO $table_name (user_id, mood, last_check) VALUES (:user_id, :mood, NOW())";
  $stmt = $pdo->prepare($sql);
  return $stmt->execute([
    'user_id' => $user_id,
    'mood' => $mood
  ]);
}


// --- 3. MANEJO DE LA PETICIÓN POST (Guardar Ánimo) ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mood_selection'])) {
  if ($db_connected && save_user_mood($pdo, $current_user_id, $mood_table, $_POST['mood_selection'])) {
    // Si se guarda correctamente, recargamos la página para que el check_mood_required se actualice
    header('Location: index.php');
    exit;
  } else {
    // Manejar error de guardado
    // Podrías redirigir a una página de error o mostrar un mensaje.
    echo "<script>alert('Error al guardar el estado de ánimo en la base de datos.');</script>";
  }
}


// --- 4. CHEQUEO INICIAL Y VISTA ---

// Ejecutar el chequeo al cargar la página (solo si la BBDD está conectada)
$mood_check = $db_connected ? check_mood_required($pdo, $current_user_id, $mood_table) : ['required' => false, 'message' => 'BBDD desconectada. Mostrando contenido.'];

// Variable para controlar si el contenido principal debe ser visible (si el ánimo NO es requerido)
$display_main_content = !$mood_check['required'];

// ===================================================================================
// FIN DE LA LÓGICA PHP
// ===================================================================================
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <!--====== Required meta tags ======-->
  <meta charset="utf-8" />
  <meta http-equiv="x-ua-compatible" content="ie=edge" />
  <meta name="description" content="" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

  <!--====== Title ======-->
  <title>Moodplaned</title>

  <!--====== Bootstrap css ======-->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    xintegrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    xintegrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


  <!--====== Line Icons css ======-->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">


  <!-- Estilos para el overlay de la tarjeta y el badge de rating -->
  <style>
    .plan-card {
      cursor: pointer;
    }

    .card-overlay {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(5px);
      border-top-left-radius: 0.5rem;
      border-top-right-radius: 0.5rem;
    }

    .rating-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      background-color: #ffc107;
      color: #000;
      padding: 5px 10px;
      border-radius: 1rem;
      font-size: 0.85rem;
      font-weight: bold;
    }

    /* Estilo para los botones de estado de ánimo */
    .mood-btn-option {
      transition: transform 0.2s, box-shadow 0.2s;
      font-size: 1.1rem;
      font-weight: 600;
      /* Asegura que los botones se adapten al ancho */
      flex-basis: 45%;
      min-width: 120px;
    }

    .mood-btn-option:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }
  </style>

  <link rel="stylesheet" href="./assets/css/style.css" />
</head>

<body>
  <?php include 'include-header.php'; ?>

  <!--====== MODAL PARA PREGUNTAR ESTADO DE ÁNIMO (Oculto por defecto) ======-->
  <!-- La clase "show" y "data-bs-show" se controlan con JavaScript al cargar la página -->
  <div class="modal fade" id="moodModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="moodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 shadow-lg p-3">
        <form method="POST" action="index.php">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold" id="moodModalLabel">¡Bienvenido! ¿Cómo te sientes hoy?</h5>
          </div>
          <div class="modal-body text-center pt-2">
            <p class="text-secondary mb-4">Selecciona el estado que mejor te represente:</p>
            <div id="mood-prompt-content">
              <!-- Botones de estado de ánimo se inyectan aquí -->
              <div class="d-flex justify-content-center flex-wrap">

                <button type="submit" name="mood_selection" value="feliz"
                  class="mood-btn-option btn btn-outline-success m-2 py-3 px-4 rounded-3 d-flex flex-column align-items-center">
                  <span style="font-size: 2rem;">😊</span>
                  Feliz
                </button>

                <button type="submit" name="mood_selection" value="relajado"
                  class="mood-btn-option btn btn-outline-info m-2 py-3 px-4 rounded-3 d-flex flex-column align-items-center">
                  <span style="font-size: 2rem;">😌</span>
                  Relajado
                </button>

                <button type="submit" name="mood_selection" value="estresado"
                  class="mood-btn-option btn btn-outline-warning m-2 py-3 px-4 rounded-3 d-flex flex-column align-items-center">
                  <span style="font-size: 2rem;">🤯</span>
                  Estresado
                </button>

                <button type="submit" name="mood_selection" value="aburrido"
                  class="mood-btn-option btn btn-outline-primary m-2 py-3 px-4 rounded-3 d-flex flex-column align-items-center">
                  <span style="font-size: 2rem;">😴</span>
                  Aburrido
                </button>

                <button type="submit" name="mood_selection" value="triste"
                  class="mood-btn-option btn btn-outline-danger m-2 py-3 px-4 rounded-3 d-flex flex-column align-items-center">
                  <span style="font-size: 2rem;">😥</span>
                  Triste
                </button>

                <button type="submit" name="mood_selection" value="cansado"
                  class="mood-btn-option btn btn-outline-secondary m-2 py-3 px-4 rounded-3 d-flex flex-column align-items-center">
                  <span style="font-size: 2rem;">ired</span>
                  Cansado
                </button>

              </div>
            </div>
            <p class="text-sm text-muted mt-3">Solo te preguntaremos una vez cada 24 horas.</p>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!--====== FIN MODAL ESTADO DE ÁNIMO ======-->


  <!--====== MAIN CONTENT (La visibilidad inicial se controla con PHP) ======-->
  <div id="main-content" style="display: <?php echo $display_main_content ? 'block' : 'none'; ?>;">
    <!--====== Planes ======-->

    <div class="container" style="margin-top: 100px;">
      <h1>mejor valorado</h1>
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
        <div class="col">
          <div class="card plan-card border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal1">
            <div class="position-relative">
              <img src="./assets/images/parque.jpg" class="card-img-top" alt="Plan image">

              <!-- Badge de puntuación -->
              <div class="rating-badge">
                <i class="bi bi-star-fill"></i> 4.5
              </div>

              <!-- Cuadro inferior -->
              <div class="card-overlay p-3">
                <h5 class="card-title mb-1">Explore Culture</h5>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="text-muted small">
                    <i class="bi bi-geo-alt"></i> India
                  </div>
                  <div><span class="emoji">😊</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="planModal1" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg modal-dimensiones">
            <div class="modal-content border-0 rounded-4 overflow-hidden">

              <!-- Imagen superior -->
              <img src="./assets/images/parque.jpg" class="img-fluid" alt="plan">

              <!-- Contenido -->
              <div class="modal-body p-4">
                <!-- Título y botones -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h3 class="fw-bold mb-0" id="planModalLabel">Explore Culture</h3>
                  <div class="d-flex gap-2">
                    <!-- Botón Favorito -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 favorite-btn" title="Favorito">
                      <i class="bi bi-heart text-danger"></i>
                    </button>
                    <!-- Botón Guardar -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 save-btn" title="Guardar">
                      <i class="bi bi-bookmark text-primary"></i>
                    </button>
                  </div>
                </div>

                <!-- Ubicación -->
                <div class="d-flex align-items-center text-muted mb-3">
                  <i class="bi bi-geo-alt me-2"></i> India
                </div>

                <!-- Descripción -->
                <p class="text-secondary mb-4">
                  Immerse yourself in the rich traditions and vibrant heritage of India. Visit historical temples,
                  local festivals, and enjoy authentic cuisine in an unforgettable cultural experience.
                </p>

                <!-- Botones Editar y Eliminar -->
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex justify-content-start">
                    <button class="btn btn-outline-primary px-4 me-2" type="button">Editar</button>
                    <button class="btn btn-outline-danger" type="button">Eliminar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col">
          <div class="card plan-card border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal1">
            <div class="position-relative">
              <img src="./assets/images/parque.jpg" class="card-img-top" alt="Plan image">

              <!-- Badge de puntuación -->
              <div class="rating-badge">
                <i class="bi bi-star-fill"></i> 4.5
              </div>

              <!-- Cuadro inferior -->
              <div class="card-overlay p-3">
                <h5 class="card-title mb-1">Explore Culture</h5>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="text-muted small">
                    <i class="bi bi-geo-alt"></i> India
                  </div>
                  <div><span class="emoji">😊</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="planModal1" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 overflow-hidden">

              <!-- Imagen superior -->
              <img src="./assets/images/parque.jpg" class="img-fluid" alt="plan">

              <!-- Contenido -->
              <div class="modal-body p-4">
                <!-- Título y botones -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h3 class="fw-bold mb-0" id="planModalLabel">Explore Culture</h3>
                  <div class="d-flex gap-2">
                    <!-- Botón Favorito -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 favorite-btn" title="Favorito">
                      <i class="bi bi-heart text-danger"></i>
                    </button>
                    <!-- Botón Guardar -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 save-btn" title="Guardar">
                      <i class="bi bi-bookmark text-primary"></i>
                    </button>
                  </div>
                </div>

                <!-- Ubicación -->
                <div class="d-flex align-items-center text-muted mb-3">
                  <i class="bi bi-geo-alt me-2"></i> India
                </div>

                <!-- Descripción -->
                <p class="text-secondary mb-4">
                  Immerse yourself in the rich traditions and vibrant heritage of India. Visit historical temples,
                  local festivals, and enjoy authentic cuisine in an unforgettable cultural experience.
                </p>

                <!-- Botones Editar y Eliminar -->
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex justify-content-start">
                    <button class="btn btn-outline-primary px-4 me-2" type="button">Editar</button>
                    <button class="btn btn-outline-danger" type="button">Eliminar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card plan-card border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal1">
            <div class="position-relative">
              <img src="./assets/images/parque.jpg" class="card-img-top" alt="Plan image">

              <!-- Badge de puntuación -->
              <div class="rating-badge">
                <i class="bi bi-star-fill"></i> 4.5
              </div>

              <!-- Cuadro inferior -->
              <div class="card-overlay p-3">
                <h5 class="card-title mb-1">Explore Culture</h5>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="text-muted small">
                    <i class="bi bi-geo-alt"></i> India
                  </div>
                  <div><span class="emoji">😊</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="planModal1" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 overflow-hidden">

              <!-- Imagen superior -->
              <img src="./assets/images/parque.jpg" class="img-fluid" alt="plan">

              <!-- Contenido -->
              <div class="modal-body p-4">
                <!-- Título y botones -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h3 class="fw-bold mb-0" id="planModalLabel">Explore Culture</h3>
                  <div class="d-flex gap-2">
                    <!-- Botón Favorito -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 favorite-btn" title="Favorito">
                      <i class="bi bi-heart text-danger"></i>
                    </button>
                    <!-- Botón Guardar -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 save-btn" title="Guardar">
                      <i class="bi bi-bookmark text-primary"></i>
                    </button>
                  </div>
                </div>

                <!-- Ubicación -->
                <div class="d-flex align-items-center text-muted mb-3">
                  <i class="bi bi-geo-alt me-2"></i> India
                </div>

                <!-- Descripción -->
                <p class="text-secondary mb-4">
                  Immerse yourself in the rich traditions and vibrant heritage of India. Visit historical temples,
                  local festivals, and enjoy authentic cuisine in an unforgettable cultural experience.
                </p>

                <!-- Botones Editar y Eliminar -->
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex justify-content-start">
                    <button class="btn btn-outline-primary px-4 me-2" type="button">Editar</button>
                    <button class="btn btn-outline-danger" type="button">Eliminar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card plan-card border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal1">
            <div class="position-relative">
              <img src="./assets/images/parque.jpg" class="card-img-top" alt="Plan image">

              <!-- Badge de puntuación -->
              <div class="rating-badge">
                <i class="bi bi-star-fill"></i> 4.5
              </div>

              <!-- Cuadro inferior -->
              <div class="card-overlay p-3">
                <h5 class="card-title mb-1">Explore Culture</h5>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="text-muted small">
                    <i class="bi bi-geo-alt"></i> India
                  </div>
                  <div><span class="emoji">😊</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="planModal1" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 overflow-hidden">

              <!-- Imagen superior -->
              <img src="./assets/images/parque.jpg" class="img-fluid" alt="plan">

              <!-- Contenido -->
              <div class="modal-body p-4">
                <!-- Título y botones -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h3 class="fw-bold mb-0" id="planModalLabel">Explore Culture</h3>
                  <div class="d-flex gap-2">
                    <!-- Botón Favorito -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 favorite-btn" title="Favorito">
                      <i class="bi bi-heart text-danger"></i>
                    </button>
                    <!-- Botón Guardar -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 save-btn" title="Guardar">
                      <i class="bi bi-bookmark text-primary"></i>
                    </button>
                  </div>
                </div>

                <!-- Ubicación -->
                <div class="d-flex align-items-center text-muted mb-3">
                  <i class="bi bi-geo-alt me-2"></i> India
                </div>

                <!-- Descripción -->
                <p class="text-secondary mb-4">
                  Immerse yourself in the rich traditions and vibrant heritage of India. Visit historical temples,
                  local festivals, and enjoy authentic cuisine in an unforgettable cultural experience.
                </p>

                <!-- Botones Editar y Eliminar -->
                <div class="d-flex justify-content-between align-items-center">
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
    </div>
    <div class="container" style="margin-top: 100px;">
      <h1>Categoria Feliz</h1>
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
        <div class="col">
          <div class="card plan-card border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal1">
            <div class="position-relative">
              <img src="./assets/images/parque.jpg" class="card-img-top" alt="Plan image">

              <!-- Badge de puntuación -->
              <div class="rating-badge">
                <i class="bi bi-star-fill"></i> 4.5
              </div>

              <!-- Cuadro inferior -->
              <div class="card-overlay p-3">
                <h5 class="card-title mb-1">Explore Culture</h5>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="text-muted small">
                    <i class="bi bi-geo-alt"></i> India
                  </div>
                  <div><span class="emoji">😊</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="planModal1" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 overflow-hidden">

              <!-- Imagen superior -->
              <img src="./assets/images/parque.jpg" class="img-fluid" alt="plan">

              <!-- Contenido -->
              <div class="modal-body p-4">
                <!-- Título y botones -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h3 class="fw-bold mb-0" id="planModalLabel">Explore Culture</h3>
                  <div class="d-flex gap-2">
                    <!-- Botón Favorito -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 favorite-btn" title="Favorito">
                      <i class="bi bi-heart text-danger"></i>
                    </button>
                    <!-- Botón Guardar -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 save-btn" title="Guardar">
                      <i class="bi bi-bookmark text-primary"></i>
                    </button>
                  </div>
                </div>

                <!-- Ubicación -->
                <div class="d-flex align-items-center text-muted mb-3">
                  <i class="bi bi-geo-alt me-2"></i> India
                </div>

                <!-- Descripción -->
                <p class="text-secondary mb-4">
                  Immerse yourself in the rich traditions and vibrant heritage of India. Visit historical temples,
                  local festivals, and enjoy authentic cuisine in an unforgettable cultural experience.
                </p>

                <!-- Botones Editar y Eliminar -->
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex justify-content-start">
                    <button class="btn btn-outline-primary px-4 me-2" type="button">Editar</button>
                    <button class="btn btn-outline-danger" type="button">Eliminar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card plan-card border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal1">
            <div class="position-relative">
              <img src="./assets/images/parque.jpg" class="card-img-top" alt="Plan image">

              <!-- Badge de puntuación -->
              <div class="rating-badge">
                <i class="bi bi-star-fill"></i> 4.5
              </div>

              <!-- Cuadro inferior -->
              <div class="card-overlay p-3">
                <h5 class="card-title mb-1">Explore Culture</h5>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="text-muted small">
                    <i class="bi bi-geo-alt"></i> India
                  </div>
                  <div><span class="emoji">😊</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="planModal1" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 overflow-hidden">

              <!-- Imagen superior -->
              <img src="./assets/images/parque.jpg" class="img-fluid" alt="plan">

              <!-- Contenido -->
              <div class="modal-body p-4">
                <!-- Título y botones -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h3 class="fw-bold mb-0" id="planModalLabel">Explore Culture</h3>
                  <div class="d-flex gap-2">
                    <!-- Botón Favorito -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 favorite-btn" title="Favorito">
                      <i class="bi bi-heart text-danger"></i>
                    </button>
                    <!-- Botón Guardar -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 save-btn" title="Guardar">
                      <i class="bi bi-bookmark text-primary"></i>
                    </button>
                  </div>
                </div>

                <!-- Ubicación -->
                <div class="d-flex align-items-center text-muted mb-3">
                  <i class="bi bi-geo-alt me-2"></i> India
                </div>

                <!-- Descripción -->
                <p class="text-secondary mb-4">
                  Immerse yourself in the rich traditions and vibrant heritage of India. Visit historical temples,
                  local festivals, and enjoy authentic cuisine in an unforgettable cultural experience.
                </p>

                <!-- Botones Editar y Eliminar -->
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex justify-content-start">
                    <button class="btn btn-outline-primary px-4 me-2" type="button">Editar</button>
                    <button class="btn btn-outline-danger" type="button">Eliminar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col">
          <div class="card plan-card border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal1">
            <div class="position-relative">
              <img src="./assets/images/parque.jpg" class="card-img-top" alt="Plan image">

              <!-- Badge de puntuación -->
              <div class="rating-badge">
                <i class="bi bi-star-fill"></i> 4.5
              </div>

              <!-- Cuadro inferior -->
              <div class="card-overlay p-3">
                <h5 class="card-title mb-1">Explore Culture</h5>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="text-muted small">
                    <i class="bi bi-geo-alt"></i> India
                  </div>
                  <div><span class="emoji">😊</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="planModal1" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 overflow-hidden">

              <!-- Imagen superior -->
              <img src="./assets/images/parque.jpg" class="img-fluid" alt="plan">

              <!-- Contenido -->
              <div class="modal-body p-4">
                <!-- Título y botones -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h3 class="fw-bold mb-0" id="planModalLabel">Explore Culture</h3>
                  <div class="d-flex gap-2">
                    <!-- Botón Favorito -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 favorite-btn" title="Favorito">
                      <i class="bi bi-heart text-danger"></i>
                    </button>
                    <!-- Botón Guardar -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 save-btn" title="Guardar">
                      <i class="bi bi-bookmark text-primary"></i>
                    </button>
                  </div>
                </div>

                <!-- Ubicación -->
                <div class="d-flex align-items-center text-muted mb-3">
                  <i class="bi bi-geo-alt me-2"></i> India
                </div>

                <!-- Descripción -->
                <p class="text-secondary mb-4">
                  Immerse yourself in the rich traditions and vibrant heritage of India. Visit historical temples,
                  local festivals, and enjoy authentic cuisine in an unforgettable cultural experience.
                </p>

                <!-- Botones Editar y Eliminar -->
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex justify-content-start">
                    <button class="btn btn-outline-primary px-4 me-2" type="button">Editar</button>
                    <button class="btn btn-outline-danger" type="button">Eliminar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card plan-card border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal1">
            <div class="position-relative">
              <img src="./assets/images/parque.jpg" class="card-img-top" alt="Plan image">

              <!-- Badge de puntuación -->
              <div class="rating-badge">
                <i class="bi bi-star-fill"></i> 4.5
              </div>

              <!-- Cuadro inferior -->
              <div class="card-overlay p-3">
                <h5 class="card-title mb-1">Explore Culture</h5>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="text-muted small">
                    <i class="bi bi-geo-alt"></i> India
                  </div>
                  <div><span class="emoji">😊</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="planModal1" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 overflow-hidden">

              <!-- Imagen superior -->
              <img src="./assets/images/parque.jpg" class="img-fluid" alt="plan">

              <!-- Contenido -->
              <div class="modal-body p-4">
                <!-- Título y botones -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h3 class="fw-bold mb-0" id="planModalLabel">Explore Culture</h3>
                  <div class="d-flex gap-2">
                    <!-- Botón Favorito -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 favorite-btn" title="Favorito">
                      <i class="bi bi-heart text-danger"></i>
                    </button>
                    <!-- Botón Guardar -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 save-btn" title="Guardar">
                      <i class="bi bi-bookmark text-primary"></i>
                    </button>
                  </div>
                </div>

                <!-- Ubicación -->
                <div class="d-flex align-items-center text-muted mb-3">
                  <i class="bi bi-geo-alt me-2"></i> India
                </div>

                <!-- Descripción -->
                <p class="text-secondary mb-4">
                  Immerse yourself in the rich traditions and vibrant heritage of India. Visit historical temples,
                  local festivals, and enjoy authentic cuisine in an unforgettable cultural experience.
                </p>

                <!-- Botones Editar y Eliminar -->
                <div class="d-flex justify-content-between align-items-center">
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
    </div>
    <div class="container" style="margin-top: 100px;">
      <h1>Categoria Triste</h1>
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
        <div class="col">
          <div class="card plan-card border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal1">
            <div class="position-relative">
              <img src="./assets/images/parque.jpg" class="card-img-top" alt="Plan image">

              <!-- Badge de puntuación -->
              <div class="rating-badge">
                <i class="bi bi-star-fill"></i> 4.5
              </div>

              <!-- Cuadro inferior -->
              <div class="card-overlay p-3">
                <h5 class="card-title mb-1">Explore Culture</h5>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="text-muted small">
                    <i class="bi bi-geo-alt"></i> India
                  </div>
                  <div><span class="emoji">😊</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="planModal1" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 overflow-hidden">

              <!-- Imagen superior -->
              <img src="./assets/images/parque.jpg" class="img-fluid" alt="plan">

              <!-- Contenido -->
              <div class="modal-body p-4">
                <!-- Título y botones -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h3 class="fw-bold mb-0" id="planModalLabel">Explore Culture</h3>
                  <div class="d-flex gap-2">
                    <!-- Botón Favorito -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 favorite-btn" title="Favorito">
                      <i class="bi bi-heart text-danger"></i>
                    </button>
                    <!-- Botón Guardar -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 save-btn" title="Guardar">
                      <i class="bi bi-bookmark text-primary"></i>
                    </button>
                  </div>
                </div>

                <!-- Ubicación -->
                <div class="d-flex align-items-center text-muted mb-3">
                  <i class="bi bi-geo-alt me-2"></i> India
                </div>

                <!-- Descripción -->
                <p class="text-secondary mb-4">
                  Immerse yourself in the rich traditions and vibrant heritage of India. Visit historical temples,
                  local festivals, and enjoy authentic cuisine in an unforgettable cultural experience.
                </p>

                <!-- Botones Editar y Eliminar -->
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex justify-content-start">
                    <button class="btn btn-outline-primary px-4 me-2" type="button">Editar</button>
                    <button class="btn btn-outline-danger" type="button">Eliminar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card plan-card border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal1">
            <div class="position-relative">
              <img src="./assets/images/parque.jpg" class="card-img-top" alt="Plan image">

              <!-- Badge de puntuación -->
              <div class="rating-badge">
                <i class="bi bi-star-fill"></i> 4.5
              </div>

              <!-- Cuadro inferior -->
              <div class="card-overlay p-3">
                <h5 class="card-title mb-1">Explore Culture</h5>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="text-muted small">
                    <i class="bi bi-geo-alt"></i> India
                  </div>
                  <div><span class="emoji">😊</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="planModal1" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 overflow-hidden">

              <!-- Imagen superior -->
              <img src="./assets/images/parque.jpg" class="img-fluid" alt="plan">

              <!-- Contenido -->
              <div class="modal-body p-4">
                <!-- Título y botones -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h3 class="fw-bold mb-0" id="planModalLabel">Explore Culture</h3>
                  <div class="d-flex gap-2">
                    <!-- Botón Favorito -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 favorite-btn" title="Favorito">
                      <i class="bi bi-heart text-danger"></i>
                    </button>
                    <!-- Botón Guardar -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 save-btn" title="Guardar">
                      <i class="bi bi-bookmark text-primary"></i>
                    </button>
                  </div>
                </div>

                <!-- Ubicación -->
                <div class="d-flex align-items-center text-muted mb-3">
                  <i class="bi bi-geo-alt me-2"></i> India
                </div>

                <!-- Descripción -->
                <p class="text-secondary mb-4">
                  Immerse yourself in the rich traditions and vibrant heritage of India. Visit historical temples,
                  local festivals, and enjoy authentic cuisine in an unforgettable cultural experience.
                </p>

                <!-- Botones Editar y Eliminar -->
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex justify-content-start">
                    <button class="btn btn-outline-primary px-4 me-2" type="button">Editar</button>
                    <button class="btn btn-outline-danger" type="button">Eliminar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col">
          <div class="card plan-card border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal1">
            <div class="position-relative">
              <img src="./assets/images/parque.jpg" class="card-img-top" alt="Plan image">

              <!-- Badge de puntuación -->
              <div class="rating-badge">
                <i class="bi bi-star-fill"></i> 4.5
              </div>

              <!-- Cuadro inferior -->
              <div class="card-overlay p-3">
                <h5 class="card-title mb-1">Explore Culture</h5>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="text-muted small">
                    <i class="bi bi-geo-alt"></i> India
                  </div>
                  <div><span class="emoji">😊</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="planModal1" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 overflow-hidden">

              <!-- Imagen superior -->
              <img src="./assets/images/parque.jpg" class="img-fluid" alt="plan">

              <!-- Contenido -->
              <div class="modal-body p-4">
                <!-- Título y botones -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h3 class="fw-bold mb-0" id="planModalLabel">Explore Culture</h3>
                  <div class="d-flex gap-2">
                    <!-- Botón Favorito -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 favorite-btn" title="Favorito">
                      <i class="bi bi-heart text-danger"></i>
                    </button>
                    <!-- Botón Guardar -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 save-btn" title="Guardar">
                      <i class="bi bi-bookmark text-primary"></i>
                    </button>
                  </div>
                </div>

                <!-- Ubicación -->
                <div class="d-flex align-items-center text-muted mb-3">
                  <i class="bi bi-geo-alt me-2"></i> India
                </div>

                <!-- Descripción -->
                <p class="text-secondary mb-4">
                  Immerse yourself in the rich traditions and vibrant heritage of India. Visit historical temples,
                  local festivals, and enjoy authentic cuisine in an unforgettable cultural experience.
                </p>

                <!-- Botones Editar y Eliminar -->
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex justify-content-start">
                    <button class="btn btn-outline-primary px-4 me-2" type="button">Editar</button>
                    <button class="btn btn-outline-danger" type="button">Eliminar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card plan-card border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal1">
            <div class="position-relative">
              <img src="./assets/images/parque.jpg" class="card-img-top" alt="Plan image">

              <!-- Badge de puntuación -->
              <div class="rating-badge">
                <i class="bi bi-star-fill"></i> 4.5
              </div>

              <!-- Cuadro inferior -->
              <div class="card-overlay p-3">
                <h5 class="card-title mb-1">Explore Culture</h5>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="text-muted small">
                    <i class="bi bi-geo-alt"></i> India
                  </div>
                  <div><span class="emoji">😊</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="planModal1" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 overflow-hidden">

              <!-- Imagen superior -->
              <img src="./assets/images/parque.jpg" class="img-fluid" alt="plan">

              <!-- Contenido -->
              <div class="modal-body p-4">
                <!-- Título y botones -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h3 class="fw-bold mb-0" id="planModalLabel">Explore Culture</h3>
                  <div class="d-flex gap-2">
                    <!-- Botón Favorito -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 favorite-btn" title="Favorito">
                      <i class="bi bi-heart text-danger"></i>
                    </button>
                    <!-- Botón Guardar -->
                    <button type="button" class="btn btn-light border rounded-circle p-2 save-btn" title="Guardar">
                      <i class="bi bi-bookmark text-primary"></i>
                    </button>
                  </div>
                </div>

                <!-- Ubicación -->
                <div class="d-flex align-items-center text-muted mb-3">
                  <i class="bi bi-geo-alt me-2"></i> India
                </div>

                <!-- Descripción -->
                <p class="text-secondary mb-4">
                  Immerse yourself in the rich traditions and vibrant heritage of India. Visit historical temples,
                  local festivals, and enjoy authentic cuisine in an unforgettable cultural experience.
                </p>

                <!-- Botones Editar y Eliminar -->
                <div class="d-flex justify-content-between align-items-center">
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
    </div>


    <!--====== END Planes ======-->
  </div>

  <?php include 'include-footer.php'; ?>
  <!-- Script para iniciar el modal y otros efectos visuales -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // 1. Mostrar el modal si PHP lo requiere
      const mainContent = document.getElementById('main-content');

      // Solo si el contenido principal está oculto, intentamos mostrar el modal.
      // Esto significa que $mood_check['required'] fue true en PHP.
      if (mainContent.style.display === 'none') {
        const moodModalEl = document.getElementById('moodModal');
        // Usamos el método show() de Bootstrap para mostrar el modal.
        const moodModal = new bootstrap.Modal(moodModalEl);
        moodModal.show();
      }

      // 2. Lógica de botones Favorito/Guardado (mantenida)
      document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const icon = btn.querySelector('i');
          icon.classList.toggle('bi-heart');
          icon.classList.toggle('bi-heart-fill');
        });
      });

      document.querySelectorAll('.save-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const icon = btn.querySelector('i');
          icon.classList.toggle('bi-bookmark');
          icon.classList.toggle('bi-bookmark-fill');
        });
      });
    });
  </script>
</body>

</html>