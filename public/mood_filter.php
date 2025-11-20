<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Filtrar por Emociones</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="./assets/css/style.css" />
</head>

<body>

    <?php include 'include-header.php'; ?>

    <!-- FILTROS -->
    <div class="container text-center my-4 ">
        <div class="btn-group flex-wrap filtros" role="group">
            <button class="btn btn-outline-primary filter-btn active" data-filter="all" title="Todas">🌈</button>
            <button class="btn btn-outline-primary filter-btn" data-filter="Feliz" title="Feliz">😊</button>
            <button class="btn btn-outline-primary filter-btn" data-filter="Triste" title="Triste">😢</button>
            <button class="btn btn-outline-primary filter-btn" data-filter="Enfadado" title="Enfadado">😡</button>
            <button class="btn btn-outline-primary filter-btn" data-filter="Sorprendido" title="Sorprendido">😲</button>
            <button class="btn btn-outline-primary filter-btn" data-filter="Enamorado" title="Enamorado">😍</button>
        </div>
    </div>

    <!-- GRID DONDE IRÁN LAS TARJETAS FILTRADAS -->
    <div class="container">
        <div id="plansContainer" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3"></div>
    </div>

    <?php include 'include-footer.php'; ?>

    <!-- AJAX -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            const buttons = document.querySelectorAll(".filter-btn");
            const container = document.getElementById("plansContainer");

            // Cargar todos al principio
            loadPlans("all");

            buttons.forEach(btn => {
                btn.addEventListener("click", () => {

                    buttons.forEach(b => b.classList.remove("active"));
                    btn.classList.add("active");

                    const filter = btn.getAttribute("data-filter");
                    loadPlans(filter);
                });
            });

            function loadPlans(emotion) {
                fetch("search_mood.php?emotion=" + emotion)
                    .then(res => res.json())
                    .then(plans => {

                        container.innerHTML = "";

                        if (plans.length === 0) {
                            container.innerHTML = `
                        <div class="col-12 text-center py-4">
                            <h5 class="text-muted">No hay planes para esta emoción.</h5>
                        </div>`;
                            return;
                        }

                        plans.forEach(plan => {
                            container.innerHTML += `
                        <div class="col">
                            <div class="card plan-card border-0 shadow-sm">
                                <div class="position-relative">
                                    <img src="${plan.image}" class="card-img-top" alt="${plan.title}">
                                    <div class="rating-badge">
                                        <i class="bi bi-star-fill"></i> ${parseFloat(plan.rating).toFixed(1)}
                                    </div>
                                    <div class="card-overlay p-3">
                                        <h5 class="card-title mb-1">${plan.title}</h5>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-light small">
                                                <i class="bi bi-geo-alt"></i> ${plan.category}
                                            </div>
                                            <div><span class="emoji">😊</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                        });
                    });
            }

        });
    </script>

</body>

</html>