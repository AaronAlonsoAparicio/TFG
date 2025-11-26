<?php
// perfil.php — versión usando tu base de datos REAL con PDO
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
// API AJAX: /perfil.php?action=list&type=favoritos|publicaciones|guardados
// -----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'list') {
    header('Content-Type: application/json; charset=utf-8');

    session_start();
    $userId = $_SESSION['user_id']; // Aquí pones el ID de usuario logueado

    $type = $_GET['type'] ?? 'favoritos';
    $data = [];

    if ($type === 'favoritos') {
        $sql = "SELECT p.*, 
                       (SELECT AVG(rating) FROM reviews WHERE plan_id = p.id) AS rating 
                FROM favorites f
                JOIN plans p ON f.plan_id = p.id
                WHERE f.user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($type === 'publicaciones') {
        $sql = "SELECT p.*, 
                       (SELECT AVG(rating) FROM reviews WHERE plan_id = p.id) AS rating
                FROM plans p
                WHERE p.created_by = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($type === 'guardados') {
        $sql = "SELECT p.*, 
                       (SELECT AVG(rating) FROM reviews WHERE plan_id = p.id) AS rating
                FROM saved_plans s
                JOIN plans p ON s.plan_id = p.id
                WHERE s.user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(['status' => 'ok', 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}
session_start();
$userId = $_SESSION['user_id']; // Aquí pones el ID de usuario logueado
// Traer datos del usuario
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
            <button class="btn btn-outline-danger btn-logout-perfil"><i class="bi bi-box-arrow-right"></i> Cerrar
                sesión</button>
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
                    <form action="#" id="editProfileForm">
                        <div class="mb-3">
                            <label for="bannerInput" class="form-label">Banner</label>
                            <input class="form-control" type="file" id="bannerInput">
                            <div class="mt-3">
                                <img src="" alt="Banner" id="bannerPreview">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="perfilInput" class="form-label">Foto de perfil</label>
                            <input class="form-control" type="file" id="perfilInput">
                            <div class="mt-3">
                                <img src="" alt="Foto de perfil" id="perfilPreview">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="descripcionInput" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcionInput" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer-profile">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="saveProfile()">Guardar cambios</button>
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
                        <h4>24</h4>
                        <p>Planes realizados</p>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="stats-card">
                        <h4>😊 ❤️ 😲</h4>
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
            const ctx = document.getElementById('muscleChart');

            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: ['Feliz', 'Triste', 'Relajado', 'Enfadado', 'Nervioso'],
                    datasets: [{
                        label: 'Actual',
                        data: [6, 4, 2, 3, 5],
                        fill: true,
                        backgroundColor: 'rgba(0,123,255,0.2)',
                        borderColor: '#007bff',
                        pointBackgroundColor: '#007bff',
                    }]
                },
                options: {
                    scales: {
                        r: {
                            angleLines: {
                                color: '#333'
                            },
                            grid: {
                                color: '#222'
                            },
                            pointLabels: {
                                color: '#aaa',
                                font: {
                                    size: 12
                                }
                            },
                            ticks: {
                                display: false,
                                beginAtZero: true,
                                max: 10
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
            const ctx2 = document.getElementById('barChart');

            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: ['Feliz', 'Triste', 'Relajado', 'Enfadado', 'Nervioso'],
                    datasets: [{
                        label: 'Actual',
                        data: [6, 4, 2, 3, 5],
                        fill: true,
                        backgroundColor: 'rgba(0,123,255,0.2)',
                        borderColor: '#007bff',
                        pointBackgroundColor: '#007bff',
                    }]
                },
                options: {
                    scales: {
                        r: {
                            angleLines: {
                                color: '#333'
                            },
                            grid: {
                                color: '#222'
                            },
                            pointLabels: {
                                color: '#aaa',
                                font: {
                                    size: 12
                                }
                            },
                            ticks: {
                                display: false,
                                beginAtZero: true,
                                max: 10
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

            // Cargar por defecto
            document.addEventListener('DOMContentLoaded', () => loadCards('favoritos'));

            // Tabs
            for (let t of tabs) {
                t.onclick = () => {
                    tabs.forEach(x => x.classList.remove('active'));
                    t.classList.add('active');
                    loadCards(t.dataset.type);
                };
            }

            // --------------------
            // CARGAR CARDS
            // --------------------
            function loadCards(type) {
                container.innerHTML = '<div class="col-12 text-center py-5">Cargando...</div>';

                fetch(`profile.php?action=list&type=${type}`)
                    .then(r => r.json())
                    .then(json => {
                        if (json.status !== 'ok') return;
                        renderCards(json.data);
                    });
            }

            // --------------------
            // RENDER CARDS
            // --------------------
            function renderCards(data) {
                if (!data.length) {
                    container.innerHTML = '<div class="col-12 text-center text-muted py-5">Sin resultados</div>';
                    return;
                }

                container.innerHTML = '';

                data.forEach(plan => {
                    const rating = plan.rating ? Number(plan.rating).toFixed(1) : '—';

                    const div = document.createElement('div');
                    div.className = 'col';
                    div.innerHTML = `
            <div class="card plan-card-perfil shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal">
                <div class="position-relative">
                    <img src="${plan.image}" class="card-img-top" alt="${plan.title}">
                    <div class="rating-badge"><i class="bi bi-star-fill"></i> ${rating}</div>
                    <div class="card-overlay-perfil">
                        <h5 class="card-title mb-1">${plan.title}</h5>
                        <small>${plan.category ?? ''}</small>
                    </div>
                </div>
            </div>
        `;

                    // Al hacer clic en la tarjeta, llenamos el modal con la estructura deseada
                    div.querySelector('.plan-card-perfil').onclick = () => {
                        const modalBody = document.getElementById('planModal').querySelector('.modal-content');
                        modalBody.innerHTML = `
                <img src="${plan.image}" class="img-fluid" alt="${plan.title}" />
                <div class="modal-body p-4">
                    <h3 class="fw-bold mb-3" id="planModalLabel">${plan.title}</h3>
                    <div class="d-flex align-items-center text-muted mb-3">
                        <i class="bi bi-geo-alt me-2 text-primary"></i> ${plan.category ?? 'Sin categoría'}
                    </div>
                    <p class="text-secondary mb-4">
                        ${plan.description ?? 'No hay descripción disponible.'}
                    </p>
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
        </script>

    </main>

    <?php include 'include-footer.php'; ?>
</body>

</html>