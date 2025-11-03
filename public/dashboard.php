<?php
require_once __DIR__ . '/../src/auth.php';
require_login();
require_once __DIR__ . '/../src/plans.php';

$user = current_user($pdo);
$plans = get_plans($pdo);
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis planes - MoodPlanned</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- MATERIAL ICONS -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- GOOGLE FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- ESTILO PERSONALIZADO -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fb 0%, #e9efff 100%);
            color: #333;
            padding-top: 70px;
        }

        /* NAVBAR */
        .navbar {
            backdrop-filter: blur(10px);
            background: rgba(33, 37, 41, 0.9) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* OFFCANVAS MENU */
        .offcanvas {
            background: rgba(18, 18, 18, 0.95);
            backdrop-filter: blur(10px);
            color: #fff;
        }

        .offcanvas-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .chip {
            display: inline-flex;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 50px;
            padding: 0 14px 0 0;
            font-size: 0.9rem;
            font-weight: 500;
            height: 46px;
            transition: background 0.3s ease;
        }

        .chip:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .chip img {
            border-radius: 50%;
            width: 46px;
            height: 46px;
            object-fit: cover;
            margin-right: 10px;
        }

        .offcanvas-body hr {
            border-color: rgba(255, 255, 255, 0.2);
        }

        .offcanvas a {
            transition: all 0.2s ease;
        }

        .offcanvas a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            padding-left: 6px;
        }

        /* BOTONES */
        .btn-primary {
            background-color: #6C63FF;
            border-color: #6C63FF;
        }

        .btn-primary:hover {
            background-color: #5a53e0;
            border-color: #5a53e0;
        }

        .btn-info {
            background-color: #00bfa6;
            border-color: #00bfa6;
        }

        /* TARJETAS */
        .card {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .card-footer {
            background-color: #fafafa;
            border-top: none;
            border-radius: 0 0 18px 18px;
        }

        /* ALERTA */
        .alert-info {
            background-color: #eef4ff;
            color: #495057;
            border: none;
        }

        /* TITULOS */
        h3 {
            font-weight: 600;
            color: #343a40;
        }

        /* BOTÓN MENU */
        .navbar .btn-outline-light {
            border-color: rgba(255, 255, 255, 0.6);
            color: #fff;
        }

        .navbar .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">MoodPlanned</a>
            <button class="btn btn-outline-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
                <i class="material-icons">menu</i>
            </button>
        </div>

    </nav>

    <!-- SIDENAV / OFFCANVAS -->
    <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="offcanvasMenu">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Menú</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body text-center">

            <!-- CHIP DE USUARIO -->
            <div class="chip mb-3">
                <img src="../spiderman.avif" alt="Usuario">
                <?= htmlspecialchars($user['name']) ?>
            </div>

            <p class="text-muted small mb-1"><?= htmlspecialchars($user['email']) ?></p>
            <a href="edit_profile.php" class="btn btn-sm btn-outline-light mb-3">
                <i class="material-icons align-middle me-1">edit</i> Editar perfil
            </a>

            <hr>

            <ul class="list-unstyled text-start">
                <li><a href="create_plan.php" class="text-white text-decoration-none d-block py-2 px-2 rounded"><i class="material-icons align-middle me-2">add_circle</i> Nuevo plan</a></li>
                <li><a href="search_mood.php" class="text-white text-decoration-none d-block py-2 px-2 rounded"><i class="material-icons align-middle me-2">search</i> Buscar por Mood</a></li>
                <li><a href="log_mood.php" class="text-white text-decoration-none d-block py-2 px-2 rounded"><i class="material-icons align-middle me-2">edit_note</i> Registrar Ánimo</a></li>
                <li><hr></li>
                <li class="text-secondary text-uppercase small">Más opciones</li>
                <li><a href="profile.php" class="text-white text-decoration-none d-block py-2 px-2 rounded"><i class="material-icons align-middle me-2">person</i> Mi Perfil</a></li>
                <li><a href="logout.php" class="text-danger text-decoration-none d-block py-2 px-2 rounded"><i class="material-icons align-middle me-2">logout</i> Cerrar sesión</a></li>
            </ul>
        </div>
    </div>

   

        <!-- LISTA DE PLANES -->
        <div class="row g-3">
            <?php if (empty($plans)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center shadow-sm">
                        No tienes planes aún. <a href="create_plan.php">Crea uno</a> o <a href="search_mood.php">busca por tu mood</a>.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($plans as $p): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card shadow-sm h-100">
                            <?php if ($p['image']): ?>
                                <img src="<?= htmlspecialchars($p['image']) ?>" class="card-img-top" style="height:180px; object-fit:cover;" alt="Imagen del plan">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-semibold"><?= htmlspecialchars($p['title']) ?></h5>
                                <p class="card-text small text-muted mb-1"><?= htmlspecialchars($p['category']) ?></p>
                                <p class="flex-grow-1"><?= htmlspecialchars(substr($p['description'], 0, 120)) ?>...</p>
                                <div class="mt-auto">
                                    <a href="plan.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Ver detalles</a>
                                    <a href="edit_plan.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                    <a href="delete_plan.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este plan?')">Eliminar</a>
                                </div>
                            </div>
                            <div class="card-footer small text-muted">
                                Creado por <?= htmlspecialchars($p['author'] ?? 'Tú') ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
