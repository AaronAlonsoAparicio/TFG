<?php
// profile.php — versión usando tu base de datos REAL con PDO

// ---------------- CONFIGURACIÓN DB ----------------
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

// -----------------------------------------------------
// API AJAX: /profile.php?action=list&type=favoritos|publicaciones|guardados
// -----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'list') {
    header('Content-Type: application/json; charset=utf-8');

    session_start();
    $userId = $_SESSION['user_id'];

    $type = $_GET['type'] ?? 'favoritos';
    $data = [];

    if ($type === 'favoritos') {
        $sql = "SELECT p.*, 
                       (SELECT AVG(rating) FROM reviews WHERE plan_id = p.id) AS rating,
                       EXISTS(SELECT 1 FROM favorites f2 WHERE f2.user_id = ? AND f2.plan_id = p.id) AS is_favorite,
                       EXISTS(SELECT 1 FROM saved_plans s2 WHERE s2.user_id = ? AND s2.plan_id = p.id) AS is_saved
                FROM favorites f
                JOIN plans p ON f.plan_id = p.id
                WHERE f.user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $userId, $userId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($type === 'publicaciones') {
        $sql = "SELECT p.*, 
                       (SELECT AVG(rating) FROM reviews WHERE plan_id = p.id) AS rating,
                       EXISTS(SELECT 1 FROM favorites f2 WHERE f2.user_id = ? AND f2.plan_id = p.id) AS is_favorite,
                       EXISTS(SELECT 1 FROM saved_plans s2 WHERE s2.user_id = ? AND s2.plan_id = p.id) AS is_saved
                FROM plans p
                WHERE p.created_by = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $userId, $userId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($type === 'guardados') {
        $sql = "SELECT p.*, 
                       (SELECT AVG(rating) FROM reviews WHERE plan_id = p.id) AS rating,
                       EXISTS(SELECT 1 FROM favorites f2 WHERE f2.user_id = ? AND f2.plan_id = p.id) AS is_favorite,
                       EXISTS(SELECT 1 FROM saved_plans s2 WHERE s2.user_id = ? AND s2.plan_id = p.id) AS is_saved
                FROM saved_plans s
                JOIN plans p ON s.plan_id = p.id
                WHERE s.user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $userId, $userId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Convertimos los valores a booleanos para JS
    foreach ($data as &$plan) {
        $plan['is_favorite'] = (bool)$plan['is_favorite'];
        $plan['is_saved'] = (bool)$plan['is_saved'];
    }

    echo json_encode(['status' => 'ok', 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}


// ---------------------- CONTADOR DE PLANES CREADOS ----------------------
if (isset($_GET['action']) && $_GET['action'] === 'count_created') {
    header('Content-Type: application/json');

    session_start();
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM plans WHERE created_by = ?");
    $stmt->execute([$userId]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode(['status' => 'ok', 'count' => $count]);
    exit;
}

// ---------------------- EMOCIONES MÁS VIVIDAS ----------------------
if (isset($_GET['action']) && $_GET['action'] === 'top_moods') {
    header('Content-Type: application/json; charset=utf-8');

    session_start();
    $userId = $_SESSION['user_id'];

    // Contar las emociones más frecuentes desde user_mood_tracker
    $stmt = $pdo->prepare("
        SELECT mood, COUNT(*) AS count
        FROM user_mood_tracker
        WHERE user_id = ?
        GROUP BY mood
        ORDER BY count DESC
        LIMIT 3
    ");
    $stmt->execute([$userId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'ok', 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

// ---------------------- ESTADÍSTICAS DE EMOCIONES ----------------------
if (isset($_GET['action']) && $_GET['action'] === 'mood_stats') {
    header('Content-Type: application/json; charset=utf-8');

    session_start();
    $userId = $_SESSION['user_id'];

    // Contamos cuántas veces aparece cada emoción
    $stmt = $pdo->prepare("
        SELECT mood, COUNT(*) AS count
        FROM user_mood_tracker
        WHERE user_id = ?
        GROUP BY mood
    ");
    $stmt->execute([$userId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'ok', 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}



// ---------------------- DATOS DE USUARIO ----------------------
session_start();
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name, profile_image, banner, bio, points FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Perfil de Usuario - MoodPlaned</title>

    <!--====== Title ======-->
    <title>Moodplaned</title>

    <!--====== Bootstrap css ======-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    <!--====== Line Icons css ======-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="./assets/css/profile.css" />
    <link rel="stylesheet" href="./assets/css/style.css" />
    <!--====== Grafico ======-->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <style>
        /* ==== MODAL DE PERFIL ==== */
        .modal-content {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            background-color: #ffffff;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        /* Header */
        .modal-header-profile {
            background: linear-gradient(135deg, #4f46e5, #6d28d9);
            color: #fff;
            padding: 1.2rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header-profile .modal-title-profile {
            font-weight: 600;
            font-size: 1.25rem;
        }

        .modal-header-profile .btn-close {
            filter: invert(1);
            opacity: 0.9;
        }

        /* Cuerpo */
        .modal-body-profile {
            padding: 2rem 1.5rem;
            background-color: #fafafa;
        }

        .modal-body-profile label {
            font-weight: 600;
            color: #333;
        }

        .modal-body-profile input[type="file"],
        .modal-body-profile input[type="text"],
        .modal-body-profile textarea {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 0.6rem 0.8rem;
            background-color: #fff;
            transition: all 0.3s ease;
        }

        .modal-body-profile input:focus,
        .modal-body-profile textarea:focus {
            border-color: #6d28d9;
            box-shadow: 0 0 0 0.15rem rgba(109, 40, 217, 0.25);
        }

        /* Vista previa de imágenes */
        #bannerPreview {
            border-radius: 12px;
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border: 1px solid #ddd;
        }

        #perfilPreview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #6d28d9;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }

        /* Footer */
        .modal-footer-profile {
            display: flex;
            justify-content: flex-end;
            padding: 1rem 1.5rem;
            background-color: #f1f1f1;
        }

        .modal-footer-profile .btn {
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .modal-footer-profile .btn-primary {
            background-color: #6d28d9;
            border: none;
        }

        .modal-footer-profile .btn-primary:hover {
            background-color: #4f46e5;
        }

        .modal-footer-profile .btn-secondary {
            background-color: #e5e7eb;
            color: #333;
            border: none;
        }

        .modal-footer-profile .btn-secondary:hover {
            background-color: #d1d5db;
        }

        /* Animación al abrir el modal */
        .modal.fade .modal-dialog {
            transform: scale(0.95);
            transition: all 0.25s ease-in-out;
        }

        .modal.show .modal-dialog {
            transform: scale(1);
        }



        .profile-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }

        .profile-tab {
            padding: 8px 14px;
            border-radius: 10px;
            cursor: pointer;
            border: 1px solid #ccc;
        }

        .profile-tab.active {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }
    </style>

<body>

    <?php include 'include-header.php'; ?>
    <div class="profile-header">
        <div class="profile-overlay"> <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Foto de perfil"></div>

    </div>
    <div class="profile-info">
        <h3><?= htmlspecialchars($user['name'] ?? 'Usuario') ?></h3>
        <p>@<?= strtolower(htmlspecialchars($user['name'] ?? 'user')) ?> · <?= htmlspecialchars($user['bio'] ?? 'Amante de los viajes y las emociones') ?></p>

        <div class="mt-3">
            <button type="button" class="btn btn-edit-perfil me-2" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                <i class="bi bi-pencil-square"></i> Editar perfil
            </button>
            <a class="btn btn-outline-danger btn-logout-perfil" href="./logout.php">
                <i class="bi bi-box-arrow-right"></i> Cerrar sesión
            </a>
        </div>
    </div>

    <!-- Modal -->
   <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header-profile">
        <h5 class="modal-title-profile" id="editProfileModalLabel">Editar perfil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body-profile">
        <form id="editProfileForm" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="bannerInput" class="form-label">Banner</label>
            <input type="file" name="banner" id="bannerInput" class="form-control" accept="image/*">
            <div class="mt-3">
              <img src="<?= htmlspecialchars($user['banner'] ?? '') ?>" id="bannerPreview" style="width:100%; max-height:200px; object-fit:cover;">
            </div>
          </div>

          <div class="mb-3">
            <label for="avatarInput" class="form-label">Foto de perfil</label>
            <input type="file" name="avatar" id="avatarInput" class="form-control" accept="image/*">
            <div class="mt-3">
              <img src="<?= htmlspecialchars($user['profile_image'] ?? '') ?>" id="avatarPreview" style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:2px solid #6d28d9;">
            </div>
          </div>

          <div class="mb-3">
            <label for="bioInput" class="form-label">Descripción</label>
            <textarea name="bio" id="bioInput" class="form-control" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer-profile">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary" onclick="guardarPerfil()">Guardar cambios</button>
      </div>
    </div>
  </div>
</div>


    <main class="container mt-5 pt-5">

        <!-- ESTADÍSTICAS -->
        <section class="mb-5">
            <div class="row g-3 justify-content-center text-center">
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="stats-card">
                        <h4 id="created-plans-count">0</h4>
                        <p>Planes creados</p>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-3">
                    <div class="stats-card">
                        <h4 id="top-moods">...</h4>
                        <p>Emociones más vividas</p>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-3">
                    <div class="stats-card">
                        <h4>5</h4>
                        <p>Destinos favoritos</p>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="stats-card">
                        <h4>3</h4>
                        <p>Puntos</p>
                    </div>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-12 col-xs-12 col-sm-12 col-md-6 col-lg-6  d-flex justify-content-center">
                    <div class="stats-card mb-4 text-center" style="width: 90%; height: 20rem;">
                        <h5 class="m-0">Grafico de emociones</h5>
                        <canvas id="muscleChart"></canvas>
                    </div>
                </div>
                <div class="col-12 col-xs-12 col-sm-12 col-md-6 col-lg-6 d-flex justify-content-center">
                    <div class="stats-card mb-4 text-center" style="width: 90%; height: 20rem;">
                        <h5 class="m-0">Grafico de emociones</h5>
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>
        </section>
        <script>
            function guardarPerfil() {
    const form = document.getElementById('editProfileForm');
    const formData = new FormData(form);

    fetch('update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'ok') {
            alert('Perfil actualizado correctamente');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error al actualizar el perfil');
    });
}

// Previews en tiempo real
document.getElementById('bannerInput').addEventListener('change', e => {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = () => document.getElementById('bannerPreview').src = reader.result;
        reader.readAsDataURL(file);
    }
});

document.getElementById('avatarInput').addEventListener('change', e => {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = () => document.getElementById('avatarPreview').src = reader.result;
        reader.readAsDataURL(file);
    }
});







            const moodLabels = ['Feliz', 'Triste', 'Enfadado', 'Sorprendido', 'Enamorado'];
            const moodEmojisGrafico = {
                'feliz': "😊",
                'triste': "😢",
                'enfadado': "😡",
                'sorprendido': "😲",
                'enamorado': "😍"
            };

            let radarChart = null;
            let barChart = null;

            async function loadMoodStats() {
                try {
                    const res = await fetch('profile.php?action=mood_stats', {
                        credentials: 'same-origin'
                    });
                    const json = await res.json();
                    if (json.status !== 'ok') return;

                    const countsMap = {};
                    json.data.forEach(m => countsMap[m.mood.toLowerCase()] = Number(m.count));

                    const dataCounts = moodLabels.map(label => countsMap[label.toLowerCase()] || 0);

                    // === Radar Chart ===
                    if (!radarChart) {
                        const ctx = document.getElementById('muscleChart');
                        radarChart = new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: moodLabels.map(l => moodEmojisGrafico[l.toLowerCase()] + ' ' + l),
                                datasets: [{
                                    label: 'Emociones',
                                    data: dataCounts,
                                    fill: true,
                                    backgroundColor: 'rgba(0,123,255,0.2)',
                                    borderColor: '#007bff',
                                    pointBackgroundColor: '#007bff',
                                }]
                            },
                            options: {
                                scales: {
                                    r: {
                                        ticks: {
                                            beginAtZero: true,
                                            max: Math.max(...dataCounts, 1)
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                }
                            }
                        });
                    } else {
                        radarChart.data.datasets[0].data = dataCounts;
                        radarChart.update();
                    }

                    // === Bar Chart ===
                    if (!barChart) {
                        const ctx2 = document.getElementById('barChart');
                        barChart = new Chart(ctx2, {
                            type: 'bar',
                            data: {
                                labels: moodLabels.map(l => moodEmojisGrafico[l.toLowerCase()] + ' ' + l),
                                datasets: [{
                                    label: 'Emociones',
                                    data: dataCounts,
                                    backgroundColor: 'rgba(0,123,255,0.2)',
                                    borderColor: '#007bff',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: Math.max(...dataCounts, 1)
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                }
                            }
                        });
                    } else {
                        barChart.data.datasets[0].data = dataCounts;
                        barChart.update();
                    }

                } catch (err) {
                    console.error('Error cargando estadísticas de emociones:', err);
                }
            }
        </script>

        <!-- PLANES FAVORITOS -->
        <div class="container py-4">

            <h2 class="mb-3">Mi perfil</h2>

            <div class="profile-tabs">
                <div class="profile-tab active" data-type="favoritos">Favoritos</div>
                <div class="profile-tab" data-type="publicaciones">Publicaciones</div>
                <div class="profile-tab" data-type="guardados">Guardados</div>
            </div>

            <section>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3" id="cards-container"></div>
            </section>
        </div>

        <!-- MODAL PLAN -->
        <div class="modal fade" id="planModal" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dimensiones">
                <div class="modal-content border-0 rounded-4 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb" class="img-fluid" alt="plan" />
                    <div class="modal-body p-4">
                        <h3 class="fw-bold mb-3" id="planModalLabel">Atardecer en Bali</h3>
                        <div class="d-flex align-items-center text-muted mb-3">
                            <i class="bi bi-geo-alt me-2 text-primary"></i> Indonesia
                        </div>
                        <p class="text-secondary mb-4">
                            Disfruta de una experiencia inolvidable viendo el atardecer junto a la playa mientras te
                            conectas con tus emociones más profundas.
                        </p>
                        <div class="d-flex justify-content-start">
                            <button class="btn btn-outline-primary px-4 me-2" type="button">Editar</button>
                            <button class="btn btn-outline-danger" type="button">Eliminar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        <script>
            const container = document.getElementById('cards-container');
            const tabs = document.querySelectorAll('.profile-tab');

            // Mapa de nombres de emociones → emojis
            const moodEmojis = {
                feliz: "😊",
                triste: "😢",
                enfadado: "😡",
                sorprendido: "😲",
                enamorado: "😍"
            };

            // --------------------
            // CARGAR CARDS
            // --------------------
            async function loadCards(type) {
                container.innerHTML = '<div class="col-12 text-center py-5">Cargando...</div>';
                try {
                    const res = await fetch(`profile.php?action=list&type=${type}`, {
                        credentials: 'same-origin'
                    });
                    const json = await res.json();
                    if (json.status !== 'ok') {
                        container.innerHTML = '<div class="col-12 text-center text-danger py-5">Error al cargar</div>';
                        return;
                    }
                    renderCards(json.data);
                    updateCreatedPlansCount(); // actualiza contador
                } catch (err) {
                    console.error('Error cargando cards:', err);
                    container.innerHTML = '<div class="col-12 text-center text-danger py-5">Error al cargar</div>';
                }
            }

            // --------------------
            // RENDER CARDS
            // --------------------
            function renderCards(data) {
                container.innerHTML = '';

                if (!data.length) {
                    container.innerHTML = '<div class="col-12 text-center text-muted py-5">Sin resultados</div>';
                    return;
                }

                data.forEach(plan => {
                    const emoji = moodEmojis[plan.category?.toLowerCase()] || "🏷️";
                    const rating = plan.rating ? Number(plan.rating).toFixed(1) : '—';
                    const direccion = `${plan.direccion}`;
                    const partes = direccion.split(',');
                    const ciudadConCodigo = partes.length >= 2 ? partes[partes.length - 2].trim() : direccion;
                    const ciudad = ciudadConCodigo.replace(/^\d+\s*/, '');

                    const div = document.createElement('div');
                    div.className = 'col';
                    div.innerHTML = `
        <div class="card plan-card-perfil shadow-sm" data-plan-id="${plan.id}" data-bs-toggle="modal" data-bs-target="#planModal">
            <div class="position-relative">
                <img src="${plan.image}" class="card-img-top" alt="${plan.title}">
                <div class="rating-badge"><i class="bi bi-star-fill"></i> ${rating}</div>
                <div class="card-overlay-perfil">
                    <h5 class="card-title mb-1">${plan.title}</h5>
                    <small>${ciudad ?? ''}</small>
                </div>
                <div class="card-icons position-absolute top-2 end-2 d-flex gap-2">
                    <i class="bi ${plan.is_favorite ? 'bi-heart-fill text-danger' : 'bi-heart text-danger'} favorite-icon" data-plan-id="${plan.id}"></i>
                    <i class="bi ${plan.is_saved ? 'bi-bookmark-fill text-primary' : 'bi-bookmark text-primary'} save-icon" data-plan-id="${plan.id}"></i>
                </div>
            </div>
        </div>
        `;

                    // Modal dinámico
                    div.querySelector('.plan-card-perfil').onclick = () => {
                        const modalBody = document.getElementById('planModal').querySelector('.modal-content');
                        modalBody.innerHTML = `
            <img src="${plan.image}" class="img-fluid" alt="${plan.title}" />
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="fw-bold mb-0">${plan.title}</h3>
                    <div class="d-flex gap-2">
                        <button class="btn btn-light border rounded-circle p-2 favorite-btn" data-plan-id="${plan.id}">
                            <i class="bi ${plan.is_favorite ? 'bi-heart-fill text-danger':'bi-heart text-danger'}"></i>
                        </button>
                        <button class="btn btn-light border rounded-circle p-2 save-btn" data-plan-id="${plan.id}">
                            <i class="bi ${plan.is_saved ? 'bi-bookmark-fill text-primary':'bi-bookmark text-primary'}"></i>
                        </button>
                    </div>
                </div>
                <div class="d-flex align-items-center text-muted mb-3">
                    <i class="bi bi-geo-alt me-2"></i> ${plan.direccion}
                    <div class="text-muted small ms-auto">${emoji}</div>
                </div>
                <p class="text-secondary mb-4">${plan.description ?? 'No hay descripción disponible.'}</p>
                <div class="d-flex justify-content-start">
                    <button class="btn btn-outline-primary px-4 me-2" type="button">Editar</button>
                    <button class="btn btn-outline-danger" type="button">Eliminar</button>
                </div>
            </div>
            `;
                    };

                    container.appendChild(div);
                });
            }


            // --------------------
            // DELEGACIÓN DE EVENTOS PARA BOTONES DEL MODAL
            // --------------------
            const modal = document.getElementById('planModal');

            modal.addEventListener('click', e => {
                const btn = e.target.closest('.favorite-btn, .save-btn');
                if (!btn) return;
                e.stopPropagation();

                const planId = btn.dataset.planId;

                const updateCardIcon = (selector, isActive) => {
                    const icon = container.querySelector(`${selector}[data-plan-id="${planId}"]`);
                    if (!icon) return;
                    if (selector.includes('favorite')) {
                        icon.classList.toggle('bi-heart-fill', isActive);
                        icon.classList.toggle('bi-heart', !isActive);
                        icon.classList.toggle('text-danger', isActive);
                    } else {
                        icon.classList.toggle('bi-bookmark-fill', isActive);
                        icon.classList.toggle('bi-bookmark', !isActive);
                        icon.classList.toggle('text-primary', isActive);
                    }
                };

                // Favorito
                if (btn.classList.contains('favorite-btn')) {
                    fetch('../src/toggle_favorite.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `plan_id=${planId}`
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            const icon = btn.querySelector('i');
                            const isAdded = data.status === 'added';
                            icon.classList.toggle('bi-heart-fill', isAdded);
                            icon.classList.toggle('bi-heart', !isAdded);
                            icon.classList.toggle('text-danger', isAdded);

                            // Actualizar icono en la card principal
                            updateCardIcon('.favorite-icon', isAdded);
                        }
                    });
                }

                // Guardado
                if (btn.classList.contains('save-btn')) {
                    fetch('../src/toggle_saved.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `plan_id=${planId}`
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            const icon = btn.querySelector('i');
                            const isAdded = data.status === 'added';
                            icon.classList.toggle('bi-bookmark-fill', isAdded);
                            icon.classList.toggle('bi-bookmark', !isAdded);

                            // Actualizar icono en la card principal
                            updateCardIcon('.save-icon', isAdded);
                        }
                    });
                }
            });


            // --------------------
            // CONTADOR DE PLANES CREADOS
            // --------------------
            async function updateCreatedPlansCount() {
                try {
                    const res = await fetch('profile.php?action=count_created', {
                        credentials: 'same-origin'
                    });
                    if (!res.ok) return;
                    const json = await res.json();
                    if (json.status !== 'ok') return;

                    const el = document.getElementById('created-plans-count');
                    if (el) el.textContent = json.count;
                } catch (err) {
                    console.error('Error al actualizar planes creados:', err);
                }
            }

            // --------------------
            // EMOCIONES MÁS VIVIDAS (máx 3)
            // --------------------
            async function updateTopMoods() {
                try {
                    const res = await fetch('profile.php?action=top_moods', {
                        credentials: 'same-origin'
                    });
                    if (!res.ok) return;

                    const json = await res.json();
                    if (json.status !== 'ok') return;

                    const el = document.getElementById('top-moods');
                    if (!el) return;

                    // Convertimos nombres de emociones a emojis y tomamos máximo 3
                    const moods = json.data.map(m => moodEmojis[m.mood] ?? '').filter(Boolean).slice(0, 3);
                    el.textContent = moods.join(' ');
                } catch (err) {
                    console.error('Error al cargar emociones más vividas:', err);
                }
            }

            // --------------------
            // EVENTOS PESTAÑAS
            // --------------------
            tabs.forEach(t => {
                t.addEventListener('click', () => {
                    tabs.forEach(x => x.classList.remove('active'));
                    t.classList.add('active');
                    loadCards(t.dataset.type);
                });
            });

            // --------------------
            // INICIALIZACIÓN
            // --------------------
            document.addEventListener('DOMContentLoaded', () => {
                loadCards('favoritos');
                updateCreatedPlansCount();
                updateTopMoods();
                loadMoodStats(); // <-- carga inicial de gráficos
                setInterval(updateCreatedPlansCount, 10000);
                setInterval(updateTopMoods, 10000);
                setInterval(loadMoodStats, 10000); // refresco cada 10s
            });
        </script>



    </main>

    <?php include 'include-footer.php'; ?>
</body>

</html>