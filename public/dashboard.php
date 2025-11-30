<?php
session_start();
// ===================================================================================
// CONFIGURACIÓN Y LÓGICA PHP PARA MYSQL ADAPTADA A TU BBDD
// ===================================================================================

// --- 1. CONFIGURACIÓN DE LA BASE DE DATOS ---
// Ajusta estos valores a tu servidor MySQL real
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'moodplanned';

// TABLA del estado de ánimo compatible con tu base de datos
$mood_table = 'user_mood_tracker';

// Obtener usuario actual (DEBES reemplazarlo con tu sistema real de login)
$current_user_id = $_SESSION["user_id"]; // <-- Valor temporal para pruebas. Debe ser INT.

// Conexión a la base de datos
try {
  $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $db_connected = true;
} catch (PDOException $e) {
  $db_connected = false;
}

// --- 2. FUNCIONES DE LÓGICA ---

// Verifica si han pasado 24 horas desde el último registro
function check_mood_required($pdo, $user_id, $table_name)
{
  if (!$pdo) return ['required' => true];

  $sql = "SELECT last_check FROM $table_name WHERE user_id = :user_id ORDER BY last_check DESC LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['user_id' => $user_id]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$result) return ['required' => true];

  $last_check = strtotime($result['last_check']);
  $one_day_ago = time() - (24 * 60 * 60);
  return [
    'required' => ($last_check < $one_day_ago)
  ];
}

// Guarda el estado de ánimo del usuario
function save_user_mood($pdo, $user_id, $table_name, $mood)
{
  if (!$pdo) return false;

  $sql = "INSERT INTO $table_name (user_id, mood, last_check) VALUES (:user_id, :mood, NOW())";
  $stmt = $pdo->prepare($sql);
  return $stmt->execute([
    'user_id' => $user_id,
    'mood' => $mood
  ]);
}
function is_favorite($user_id, $plan_id, $pdo)
{
  $stmt = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = :user_id AND plan_id = :plan_id");
  $stmt->execute(['user_id' => $user_id, 'plan_id' => $plan_id]);
  return $stmt->fetch() ? true : false;
}

function is_saved($user_id, $plan_id, $pdo)
{
  $stmt = $pdo->prepare("SELECT 1 FROM saved_plans WHERE user_id = :user_id AND plan_id = :plan_id");
  $stmt->execute(['user_id' => $user_id, 'plan_id' => $plan_id]);
  return $stmt->fetch() ? true : false;
}


// --- 3. PETICIÓN POST: Guardar estado de ánimo ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mood_selection'])) {
  if ($db_connected && save_user_mood($pdo, $current_user_id, $mood_table, $_POST['mood_selection'])) {
    header('Location: dashboard.php');
    exit;
  } else {
    echo "<script>alert('Error al guardar el estado de ánimo.');</script>";
  }
}

// --- 4. CHEQUEO INICIAL ---
$mood_check = $db_connected ? check_mood_required($pdo, $current_user_id, $mood_table) : ['required' => false];
$display_main_content = !$mood_check['required'];
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
  <div class="modal fade custom-mood-modal" id="moodModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="moodModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered custom-mood-dialog">
      <div class="modal-content border-0 rounded-4 shadow-lg p-3">
        <form method="POST" action="dashboard.php">

          <div class="modal-header border-0 pb-0 custom-mood-header d-flex justify-content-center align-items-center">
            <h5 class="modal-title fw-bold text-center" id="moodModalLabel">¿Cómo te sientes hoy?</h5>
          </div>


          <div class="modal-body text-center pt-2">
            <p class="text-secondary mb-4 custom-mood-text">Selecciona el estado que mejor te represente:</p>

            <div id="mood-prompt-content">
              <div class="d-flex justify-content-center flex-wrap">

                <button type="submit" name="mood_selection" value="feliz"
                  class="mood-btn-option btn m-2 py-3 px-4 rounded-3 d-flex flex-column align-items-center custom-mood-btn btn-mood-feliz">
                  <span>😊</span>
                  Feliz
                </button>

                <button type="submit" name="mood_selection" value="triste"
                  class="mood-btn-option btn m-2 py-3 px-4 rounded-3 d-flex flex-column align-items-center custom-mood-btn btn-mood-triste">
                  <span>😢</span>
                  Triste
                </button>

                <button type="submit" name="mood_selection" value="enfadado"
                  class="mood-btn-option btn m-2 py-3 px-4 rounded-3 d-flex flex-column align-items-center custom-mood-btn btn-mood-enfadado">
                  <span>😡</span>
                  Enfadado
                </button>

                <button type="submit" name="mood_selection" value="sorprendido"
                  class="mood-btn-option btn m-2 py-3 px-4 rounded-3 d-flex flex-column align-items-center custom-mood-btn btn-mm-sorprendido">
                  <span>😲</span>
                  Sorprendido
                </button>

                <button type="submit" name="mood_selection" value="enamorado"
                  class="mood-btn-option btn m-2 py-3 px-4 rounded-3 d-flex flex-column align-items-center custom-mood-btn btn-mood-enamorado">
                  <span>😍</span>
                  Enamorado
                </button>

              </div>
            </div>

            <p class="text-sm text-muted mt-3 custom-mood-footer-text">
              Solo te preguntaremos una vez cada 24 horas.
            </p>
          </div>

        </form>
      </div>
    </div>

  </div>

  <!--====== FIN MODAL ESTADO DE ÁNIMO ======-->


  <!--====== MAIN CONTENT (La visibilidad inicial se controla con PHP) ======-->
  <div id="main-content">
    <!--====== Planes ======-->
    <?php
    // ---------------- GET MEJOR VALORADOS ----------------
    // Sacamos los planes ordenados por rating promedio (descendente)
    $sql2 = "
    SELECT p.*, 
    IFNULL(AVG(r.rating),0) AS rating 
    FROM plans p
    LEFT JOIN reviews r ON p.id = r.plan_id
    GROUP BY p.id
    ORDER BY rating DESC
    LIMIT 8
";
    $stmt2 = $pdo->query($sql2);
    $planes = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="container" style="margin-top: 100px;">
      <h1>Mejor valorado</h1>
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3" id="cards-container">

        <?php foreach ($planes as $plan): ?>
          <?php
          $direccion = $plan['direccion']; // "Calle Mayor 25, 28013 Madrid, España"
          $partes = explode(',', $direccion);

          // Tomamos la penúltima parte
          $ciudadConCodigo = count($partes) >= 2 ? trim($partes[count($partes) - 2]) : $direccion;

          // Eliminamos posibles números al inicio (código postal)
          $ciudad = preg_replace('/^\d+\s*/', '', $ciudadConCodigo);
          ?>
          <?php
          $iconos_categoria = [
            'Feliz'      => '😊',
            'Triste'     => '😢',
            'Enfadado'   => '😡',
            'Sorprendido' => '😲',
            'Enamorado'  => '😍'
          ];

          $emoji = $iconos_categoria[$plan['category']] ?? '🏷️';
          ?>

          <div class="col">
            <div class="card plan-card border-0 shadow-sm"
              data-bs-toggle="modal"
              data-bs-target="#planModal-<?= $plan['id'] ?>">
              <div class="position-relative">
                <img src="<?= htmlspecialchars($plan['image']) ?>" class="card-img-top" alt="Plan image">
                <div class="rating-badge"><i class="bi bi-star-fill text-warning"></i> <?= number_format($plan['rating'], 1) ?></div>
                <div class="card-overlay p-3">
                  <h5 class="card-title mb-1"><?= htmlspecialchars($plan['title']) ?></h5>
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($ciudad) ?></div>
                    <div class="text-muted small"><?= $emoji ?></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal único para esta tarjeta -->
            <div class="modal fade" id="planModal-<?= $plan['id'] ?>" tabindex="-1" aria-labelledby="planModalLabel-<?= $plan['id'] ?>" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-lg modal-dimensiones" >
                <div class="modal-content border-0 rounded-4 overflow-hidden">
                  <img src="<?= htmlspecialchars($plan['image']) ?>" class="img-fluid" alt="plan">
                  <div class="modal-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h3 class="fw-bold mb-0"><?= htmlspecialchars($plan['title']) ?></h3>
                      <div class="d-flex gap-2">
                        <button class="btn btn-light border rounded-circle p-2 favorite-btn" data-plan-id="<?= $plan['id'] ?>">
                          <i class="bi <?= is_favorite($current_user_id, $plan['id'], $pdo) ? 'bi-heart-fill text-danger' : 'bi-heart text-danger' ?>"></i>
                        </button>

                        <button type="button" class="btn btn-light border rounded-circle p-2 save-btn" data-plan-id="<?= $plan['id'] ?>">
                          <i class="bi <?= is_saved($current_user_id, $plan['id'], $pdo) ? 'bi-bookmark-fill text-primary' : 'bi-bookmark text-primary' ?>"></i>
                        </button>


                      </div>
                    </div>
                    <div class="d-flex align-items-center text-muted mb-3">
                      <i class="bi bi-geo-alt me-2"></i> <?= htmlspecialchars($plan['direccion']) ?>
                      <div class="text-muted small ms-auto"><?= $emoji ?></div>
                    </div>
                    <p class="text-secondary mb-4"><?= htmlspecialchars($plan['description']) ?></p>
                    <div class="d-flex justify-content-start">
                      <?php if ($plan['created_by'] == $current_user_id): ?>

                        <button class="btn btn-outline-primary px-4 me-2 edit-btn"
                          data-plan-id="<?= (int)$plan['id'] ?>"
                          data-title="<?= htmlspecialchars($plan['title'], ENT_QUOTES) ?>"
                          data-description="<?= htmlspecialchars($plan['description'], ENT_QUOTES) ?>"
                          data-category="<?= htmlspecialchars($plan['category'], ENT_QUOTES) ?>"
                          type="button">
                          Editar
                        </button>

                        <button class="btn btn-outline-danger delete-btn"
                          data-plan-id="<?= $plan['id'] ?>" type="button">
                          Eliminar
                        </button>

                      <?php else: ?>

                        <button class="btn btn-outline-success score-btn"
                          data-plan-id="<?= (int)$plan['id'] ?>"
                          type="button">
                          Puntuar
                        </button>

                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

        <?php endforeach; ?>

      </div>
    </div>

    <div class="container" style="margin-top: 50px;">
      <?php
      // ---------------- GET MEJOR VALORADOS ----------------
      // Sacamos los planes ordenados por rating promedio (descendente)
      $sql3 = "
      SELECT p.*, 
            IFNULL(AVG(r.rating),0) AS rating 
      FROM plans p
      LEFT JOIN reviews r ON p.id = r.plan_id
      WHERE p.category = 'Feliz'
      GROUP BY p.id
      ORDER BY rating DESC
      LIMIT 8
      ";

      $stmt3 = $pdo->query($sql3);
      $feliz = $stmt3->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <h1> Planes Felices</h1>
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3" id="cards-container">

        <?php foreach ($feliz as $plan): ?>
          <?php
          $direccion = $plan['direccion']; // "Calle Mayor 25, 28013 Madrid, España"
          $partes = explode(',', $direccion);

          // Tomamos la penúltima parte
          $ciudadConCodigo = count($partes) >= 2 ? trim($partes[count($partes) - 2]) : $direccion;

          // Eliminamos posibles números al inicio (código postal)
          $ciudad = preg_replace('/^\d+\s*/', '', $ciudadConCodigo);
          ?>
          <div class="col">
            <div class="card plan-card border-0 shadow-sm"
              data-bs-toggle="modal"
              data-bs-target="#planModal-<?= $plan['id'] ?>">
              <div class="position-relative">
                <img src="<?= htmlspecialchars($plan['image']) ?>" class="card-img-top" alt="Plan image">
                <div class="rating-badge"><i class="bi bi-star-fill text-warning"></i> <?= number_format($plan['rating'], 1) ?></div>
                <div class="card-overlay p-3">
                  <h5 class="card-title mb-1"><?= htmlspecialchars($plan['title']) ?></h5>
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($ciudad) ?></div>
                    <div><span class="emoji">😊</span></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal único para esta tarjeta -->
            <div class="modal fade" id="planModal-<?= $plan['id'] ?>" tabindex="-1" aria-labelledby="planModalLabel-<?= $plan['id'] ?>" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-lg modal-dimensiones">
                <div class="modal-content border-0 rounded-4 overflow-hidden">
                  <img src="<?= htmlspecialchars($plan['image']) ?>" class="img-fluid" alt="plan">
                  <div class="modal-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h3 class="fw-bold mb-0"><?= htmlspecialchars($plan['title']) ?></h3>
                      <div class="d-flex gap-2">
                        <button class="btn btn-light border rounded-circle p-2 favorite-btn" data-plan-id="<?= $plan['id'] ?>">
                          <i class="bi <?= is_favorite($current_user_id, $plan['id'], $pdo) ? 'bi-heart-fill text-danger' : 'bi-heart text-danger' ?>"></i>
                        </button>

                        <button type="button" class="btn btn-light border rounded-circle p-2 save-btn" data-plan-id="<?= $plan['id'] ?>">
                          <i class="bi <?= is_saved($current_user_id, $plan['id'], $pdo) ? 'bi-bookmark-fill text-primary' : 'bi-bookmark text-primary' ?>"></i>
                        </button>


                      </div>
                    </div>
                    <div class="d-flex align-items-center text-muted mb-3">
                      <i class="bi bi-geo-alt me-2"></i> <?= htmlspecialchars($plan['category']) ?>
                    </div>
                    <p class="text-secondary mb-4"><?= htmlspecialchars($plan['description']) ?></p>
                    <div class="d-flex justify-content-start">
                      <?php if ($plan['created_by'] == $current_user_id): ?>

                        <button class="btn btn-outline-primary px-4 me-2 edit-btn"
                          data-plan-id="<?= (int)$plan['id'] ?>"
                          data-title="<?= htmlspecialchars($plan['title'], ENT_QUOTES) ?>"
                          data-description="<?= htmlspecialchars($plan['description'], ENT_QUOTES) ?>"
                          data-category="<?= htmlspecialchars($plan['category'], ENT_QUOTES) ?>"
                          type="button">
                          Editar
                        </button>

                        <button class="btn btn-outline-danger delete-btn"
                          data-plan-id="<?= $plan['id'] ?>" type="button">
                          Eliminar
                        </button>

                      <?php else: ?>

                        <button class="btn btn-outline-success score-btn"
                          data-plan-id="<?= (int)$plan['id'] ?>"
                          type="button">
                          Puntuar
                        </button>

                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

        <?php endforeach; ?>

      </div>
    </div>
    <div class="container" style="margin-top: 50px;">
      <?php
      // ---------------- GET MEJOR VALORADOS ----------------
      // Sacamos los planes ordenados por rating promedio (descendente)
      $sql4 = "
      SELECT p.*, 
            IFNULL(AVG(r.rating),0) AS rating 
      FROM plans p
      LEFT JOIN reviews r ON p.id = r.plan_id
      WHERE p.category = 'Triste'
      GROUP BY p.id
      ORDER BY rating DESC
      LIMIT 8
      ";

      $stmt4 = $pdo->query($sql4);
      $triste = $stmt4->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <h1> Planes Tristes</h1>
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3" id="cards-container">

        <?php foreach ($triste as $plan): ?>
          <?php
          $direccion = $plan['direccion']; // "Calle Mayor 25, 28013 Madrid, España"
          $partes = explode(',', $direccion);

          // Tomamos la penúltima parte
          $ciudadConCodigo = count($partes) >= 2 ? trim($partes[count($partes) - 2]) : $direccion;

          // Eliminamos posibles números al inicio (código postal)
          $ciudad = preg_replace('/^\d+\s*/', '', $ciudadConCodigo);
          ?>
          <div class="col">
            <div class="card plan-card border-0 shadow-sm"
              data-bs-toggle="modal"
              data-bs-target="#planModal-<?= $plan['id'] ?>">
              <div class="position-relative">
                <img src="<?= htmlspecialchars($plan['image']) ?>" class="card-img-top" alt="Plan image">
                <div class="rating-badge"><i class="bi bi-star-fill text-warning"></i> <?= number_format($plan['rating'], 1) ?></div>
                <div class="card-overlay p-3">
                  <h5 class="card-title mb-1"><?= htmlspecialchars($plan['title']) ?></h5>
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($ciudad) ?></div>
                    <div><span class="emoji">😢</span></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal único para esta tarjeta -->
            <div class="modal fade" id="planModal-<?= $plan['id'] ?>" tabindex="-1" aria-labelledby="planModalLabel-<?= $plan['id'] ?>" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-lg modal-dimensiones">
                <div class="modal-content border-0 rounded-4 overflow-hidden">
                  <img src="<?= htmlspecialchars($plan['image']) ?>" class="img-fluid" alt="plan">
                  <div class="modal-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h3 class="fw-bold mb-0"><?= htmlspecialchars($plan['title']) ?></h3>
                      <div class="d-flex gap-2">
                        <button class="btn btn-light border rounded-circle p-2 favorite-btn" data-plan-id="<?= $plan['id'] ?>">
                          <i class="bi <?= is_favorite($current_user_id, $plan['id'], $pdo) ? 'bi-heart-fill text-danger' : 'bi-heart text-danger' ?>"></i>
                        </button>

                        <button type="button" class="btn btn-light border rounded-circle p-2 save-btn" data-plan-id="<?= $plan['id'] ?>">
                          <i class="bi <?= is_saved($current_user_id, $plan['id'], $pdo) ? 'bi-bookmark-fill text-primary' : 'bi-bookmark text-primary' ?>"></i>
                        </button>


                      </div>
                    </div>
                    <div class="d-flex align-items-center text-muted mb-3">
                      <i class="bi bi-geo-alt me-2"></i> <?= htmlspecialchars($plan['category']) ?>
                    </div>
                    <p class="text-secondary mb-4"><?= htmlspecialchars($plan['description']) ?></p>
                    <div class="d-flex justify-content-start">
                      <?php if ($plan['created_by'] == $current_user_id): ?>

                        <button class="btn btn-outline-primary px-4 me-2 edit-btn"
                          data-plan-id="<?= (int)$plan['id'] ?>"
                          data-title="<?= htmlspecialchars($plan['title'], ENT_QUOTES) ?>"
                          data-description="<?= htmlspecialchars($plan['description'], ENT_QUOTES) ?>"
                          data-category="<?= htmlspecialchars($plan['category'], ENT_QUOTES) ?>"
                          type="button">
                          Editar
                        </button>

                        <button class="btn btn-outline-danger delete-btn"
                          data-plan-id="<?= $plan['id'] ?>" type="button">
                          Eliminar
                        </button>

                      <?php else: ?>

                        <button class="btn btn-outline-success score-btn"
                          data-plan-id="<?= (int)$plan['id'] ?>"
                          type="button">
                          Puntuar
                        </button>

                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

        <?php endforeach; ?>

      </div>
    </div>
    <!--====== END Planes ======-->
  </div>
  <!-- Modal editar plan -->
  <div class="modal fade" id="editPlanModal" tabindex="-1" aria-labelledby="editPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 shadow-lg p-3 ">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="editPlanModalLabel">Editar Plan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body ">
          <form id="editPlanForm">
            <input type="hidden" name="plan_id" id="edit-plan-id">
            <div class="mb-3 form-group smooth">
              <label for="edit-title" class="form-label">Título</label>
              <input type="text" class="form-control input-field" id="edit-title" name="title" required>
            </div>
            <div class="mb-3 form-group smooth">
              <label for="edit-description" class="form-label">Descripción</label>
              <textarea class="form-control input-field textarea" id="edit-description" name="description" rows="3" placeholder="Describe tu plan..." required></textarea>
            </div>
            <div class="mb-3 form-group smooth">
              <label for="edit-category" class="form-label">Categoría</label>
              <select name="category" class="input-field select" id="edit-category" required>
                <option>Feliz</option>
                <option>Triste</option>
                <option>Enfadado</option>
                <option>Sorprendido</option>
                <option>Enamorado</option>
              </select>
            </div>
            <div class="d-flex justify-content-end">
              <button type="button" class="btn btn-outline-danger me-2" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn-submit">Guardar cambios</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal de puntuación -->
  <div  class="modal fade" id="scoreModal" tabindex="-1" aria-labelledby="scoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 shadow-lg p-3" style=" background-color: rgba(232, 216, 216, 0.963);">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold" id="scoreModalLabel">Puntuar Plan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body text-center">
          <p class="mb-3">Selecciona tu puntuación:</p>
          <div id="star-container" class="d-flex justify-content-center gap-2">
            <i class="bi bi-star fs-2 star" data-value="1"></i>
            <i class="bi bi-star fs-2 star" data-value="2"></i>
            <i class="bi bi-star fs-2 star" data-value="3"></i>
            <i class="bi bi-star fs-2 star" data-value="4"></i>
            <i class="bi bi-star fs-2 star" data-value="5"></i>
          </div>
          <input type="hidden" id="selected-rating" value="0">
        </div>
        <div class="d-flex justify-content-end">
          <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" id="submitRating" class="btn-submit">Enviar</button>
        </div>
      </div>
    </div>
  </div>

  <?php include 'include-footer.php'; ?>
  <!-- Script para iniciar el modal y otros efectos visuales -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // 1. Mostrar el modal si PHP lo requiere
      const moodRequired = <?php echo $mood_check['required'] ? 'true' : 'false'; ?>;
      if (moodRequired) {
        const moodModalEl = document.getElementById('moodModal');
        const moodModal = new bootstrap.Modal(moodModalEl);
        moodModal.show();
      }

      document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.stopPropagation(); // Evita abrir el modal
          const planId = btn.dataset.planId;

          fetch('../src/toggle_favorite.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: 'plan_id=' + planId
            })
            .then(res => res.json())
            .then(data => {
              console.log(data); // <- Esto te ayudará a depurar
              if (data.success) {
                const icon = btn.querySelector('i');
                if (data.status === 'added') {
                  icon.classList.remove('bi-heart');
                  icon.classList.add('bi-heart-fill', 'text-danger');
                } else {
                  icon.classList.remove('bi-heart-fill');
                  icon.classList.add('bi-heart');
                  icon.classList.remove('text-danger');
                }
              } else {
                alert('Error: ' + data.message);
              }
            })
            .catch(err => console.error('Fetch error:', err));
        });
      });

      document.querySelectorAll('.save-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.stopPropagation(); // evita abrir modal
          const planId = btn.closest('.favorite-btn, .save-btn').dataset.planId || btn.dataset.planId;
          fetch('../src/toggle_saved.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: 'plan_id=' + planId
            })
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                const icon = btn.querySelector('i');
                if (data.status === 'added') {
                  icon.classList.remove('bi-bookmark');
                  icon.classList.add('bi-bookmark-fill');
                } else {
                  icon.classList.remove('bi-bookmark-fill');
                  icon.classList.add('bi-bookmark');
                }
              }
            });
        });
      });

    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        if (!confirm("¿Seguro que quieres eliminar este plan?")) return;

        const planId = btn.dataset.planId;

        fetch('../src/delete_plan.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'plan_id=' + planId
          })
          .then(async res => {
            const text = await res.text();
            try {
              const data = JSON.parse(text);
              if (data.success) {
                alert(data.message || "Plan eliminado");
                location.reload();
              } else {
                alert(data.message || "Error al eliminar");
              }
            } catch {
              console.error("Respuesta no JSON:", text);
              alert("Error inesperado. Ver consola Network para detalles.");
            }
          })
          .catch(err => {
            console.error(err);
            alert("Error de conexión al servidor.");
          });

      });
    });
    document.querySelectorAll('.edit-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();

        const planId = btn.dataset.planId;
        const title = btn.dataset.title;
        const description = btn.dataset.description;
        const category = btn.dataset.category;

        document.getElementById('edit-plan-id').value = planId;
        document.getElementById('edit-title').value = title;
        document.getElementById('edit-description').value = description;
        document.getElementById('edit-category').value = category;

        const editModalEl = document.getElementById('editPlanModal');
        const editModal = new bootstrap.Modal(editModalEl);
        editModal.show();
      });
    });
    document.getElementById('editPlanForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);

      fetch('../src/edit_plan.php', {
          method: 'POST',
          body: formData
        })
        .then(async res => {
          const text = await res.text();
          try {
            const data = JSON.parse(text);
            if (data.success) {
              alert(data.message || "Plan actualizado");
              location.reload();
            } else {
              alert(data.message || "No se pudo actualizar el plan");
            }
          } catch {
            console.error("Respuesta no JSON:", text);
            alert("Error inesperado. Revisa la consola.");
          }
        })
        .catch(err => {
          console.error(err);
          alert("Error de conexión al servidor");
        });
    });
    let currentPlanId = null;

    document.querySelectorAll('.score-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        currentPlanId = btn.dataset.planId;

        // Resetear estrellas
        document.querySelectorAll('#star-container .star').forEach(star => {
          star.classList.remove('bi-star-fill');
          star.classList.add('bi-star');
        });
        document.getElementById('selected-rating').value = 0;

        const scoreModalEl = document.getElementById('scoreModal');
        const scoreModal = new bootstrap.Modal(scoreModalEl);
        scoreModal.show();
      });
    });

    // Manejo de clic en estrellas
    document.querySelectorAll('#star-container .star').forEach(star => {
      star.addEventListener('click', function() {
        const value = parseInt(this.dataset.value);
        document.getElementById('selected-rating').value = value;

        // Pintar estrellas hasta el valor seleccionado
        document.querySelectorAll('#star-container .star').forEach(s => {
          const v = parseInt(s.dataset.value);
          if (v <= value) {
            s.classList.remove('bi-star');
            s.classList.add('bi-star-fill', 'text-warning');
          } else {
            s.classList.remove('bi-star-fill', 'text-warning');
            s.classList.add('bi-star');
          }
        });
      });
    });

    // Enviar puntuación
    document.getElementById('submitRating').addEventListener('click', function() {
      const rating = document.getElementById('selected-rating').value;
      if (rating == 0) {
        alert('Por favor selecciona una puntuación.');
        return;
      }

      fetch('../src/submit_rating.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `plan_id=${currentPlanId}&rating=${rating}`
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert('Puntuación enviada!');
            location.reload(); // refresca la página para actualizar el rating
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(err => console.error(err));
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>