<?php
require_once "../src/config.php";
$userId = $_SESSION['user_id']; // Aquí pones el ID de usuario logueado
// Traer datos del usuario
$stmt = $pdo->prepare("SELECT name, profile_image, banner, bio, points FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
  <!--====== NAVBAR NINE PART START ======-->
<header>
  <section class="navbar-area navbar-nine">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <nav class="navbar navbar-expand-lg navbar-nine">
            <a class="navbar-brand" href="./index.php">
              <img src="assets/images/logo.png" alt="Logo" width="290px"/>
            </a>

            <!-- BOTÓN SIDEBAR SOLO MÓVIL -->
            <a class="menu-bar ms-auto d-flex d-lg-none" href="#side-menu-left">
              <i class=" tres-rayas bi-list color-white"></i>
            </a>

            <div class="collapse navbar-collapse sub-menu-bar justify-content-end" id="navbarNine">

              <!-- MENÚ SOLO DESKTOP -->
              <ul class="navbar-nav d-none d-lg-flex">
                <li class="nav-item"><a class="nav-link" href="./create_plan.php">Crear Plan</a></li>
                <li class="nav-item"><a class="nav-link" href="./mood_filter.php">Buscar por Mood</a></li>
              </ul>
              <!-- DESPLEGABLE DEL USUARIO -->
              <div class="dropdown d-flex  justify-content-end margin-left-5">
                <a class="dropdown-toggle color-white" href="#" role="button" id="dropdownUser"
                  data-bs-toggle="dropdown" aria-expanded="false">
                  <img src="<?= htmlspecialchars($user['profile_image']) ?>" class="rounded-circle" alt="User Photo" width="30" height="30">
                </a>
                <ul class="dropdown-menu dropdown-menu-end drop" aria-labelledby="dropdownUser">
                  <li><a class="dropdown-item" href="./profile.php">Perfil</a></li>
                  <li><a class="dropdown-item" href="./logout.php">Cerrar Sesión</a></li>
                </ul>
              </div>
            </div>



          </nav>
          <!-- navbar -->
        </div>
      </div>
    </div>
  </section>


  <!--====== NAVBAR NINE PART ENDS ======-->

  <!--====== SIDEBAR PART START ======-->

  <div class="sidebar-left">
    <div class="sidebar-close">
      <a class="close" href="#close"> <i class=" cruz bi bi-x color-white"></i></a>
    </div>
    <div class="sidebar-content">
      <div class="sidebar-logo">
        <a href="index.php"><img src="assets/images/Logo_Mood_Planned.png" alt="Logo" /></a>
      </div>
      <p class="text"><?= $user["name"] ?></p>
      <!-- logo -->
      <div class="sidebar-menu">
        <ul>
          <li><a href="./create_plan.php">Crear Plan</a></li>
          <li><a href="./mood_filter.php">Buscar por Mood</a></li>
          <li><a href="./profile.php">Ver Perfil</a></li>
          <li><a href="./logout.php">Cerrar Sesión</a></li>
        </ul>
      </div>
      <!-- menu -->
    </div>
    <!-- content -->
  </div>
  <div class="overlay-left"></div>
</header>
  <!--====== SIDEBAR PART ENDS ======-->
    <script>

    // Sidebar
    let sidebarLeft = document.querySelector(".sidebar-left");
    let overlayLeft = document.querySelector(".overlay-left");
    let sidebarClose = document.querySelector(".sidebar-close .close");

    // Abrir sidebar desde cualquier botón .menu-bar
    document.querySelectorAll(".menu-bar").forEach(btn => {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        sidebarLeft.classList.add("open");
        overlayLeft.classList.add("open");
      });
    });

    // Cerrar sidebar
    overlayLeft.addEventListener("click", () => {
      sidebarLeft.classList.remove("open");
      overlayLeft.classList.remove("open");
    });
    sidebarClose.addEventListener("click", () => {
      sidebarLeft.classList.remove("open");
      overlayLeft.classList.remove("open");
    });
  </script>