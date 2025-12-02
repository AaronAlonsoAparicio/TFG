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
    <div class="container text-center my-4">
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

    <!-- MODALES (uno solo de cada tipo, fuera del container) -->
    <!-- Modal editar plan -->
    <div class="modal fade" id="editPlanModal" tabindex="-1" aria-labelledby="editPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg p-3">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editPlanModalLabel">Editar Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="editPlanForm">
                        <input type="hidden" name="plan_id" id="edit-plan-id">
                        <div class="mb-3 form-group smooth">
                            <label for="edit-title" class="form-label">Título</label>
                            <input type="text" class="form-control input-field" id="edit-title" name="title" required>
                        </div>
                        <div class="mb-3 form-group smooth">
                            <label for="edit-description" class="form-label">Descripción</label>
                            <textarea class="form-control input-field textarea" id="edit-description" name="description" rows="3" required></textarea>
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
    <div class="modal fade" id="scoreModal" tabindex="-1" aria-labelledby="scoreModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg p-3" style="background-color: rgba(232, 216, 216, 0.963);">
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

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const buttons = document.querySelectorAll(".filter-btn");
            const container = document.getElementById("plansContainer");
            const currentUserId = <?= $_SESSION['user_id'] ?? 0 ?>;

            // Cargar todos al inicio
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
                            container.innerHTML = `<div class="col-12 text-center py-4">
                                <h5 class="text-muted">No hay planes para esta emoción.</h5>
                            </div>`;
                            return;
                        }

                        plans.forEach(plan => {
                            const col = document.createElement("div");
                            col.classList.add("col");

                            const emojiMap = {
                                "Feliz": "😊",
                                "Triste": "😢",
                                "Enfadado": "😡",
                                "Sorprendido": "😲",
                                "Enamorado": "😍"
                            };
                            const emoji = emojiMap[plan.category] || "🏷️";
                            const direccion = plan.direccion;

                            // Separar por comas
                            const partes = direccion.split(',');

                            // Tomar la penúltima parte
                            let ciudadConCodigo = partes.length >= 2 ? partes[partes.length - 2].trim() : direccion;

                            // Eliminar números al inicio (código postal)
                            const ciudad = ciudadConCodigo.replace(/^\d+\s*/, '');

                            col.innerHTML = `
                            <div class="card plan-card border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#planModal-${plan.id}">
                                <div class="position-relative">
                                    <img src="${plan.image}" class="card-img-top" alt="${plan.title}">
                                    <div class="rating-badge"><i class="bi bi-star-fill text-warning"></i> ${parseFloat(plan.rating).toFixed(1)}</div>
                                    <div class="card-overlay p-3">
                                        <h5 class="card-title mb-1">${plan.title}</h5>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-muted small"><i class="bi bi-geo-alt"></i> ${ciudad}</div>
                                            <div>${emoji}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal individual -->
                            <div class="modal fade" id="planModal-${plan.id}" tabindex="-1" aria-labelledby="planModalLabel-${plan.id}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg modal-dimensiones">
                                    <div class="modal-content border-0 rounded-4 overflow-hidden">
                                        <img src="${plan.image}" class="img-fluid" alt="plan">
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
                                            <p class="text-secondary mb-4">${plan.description}</p>
                                            <div class="d-flex justify-content-start">
                                                ${plan.created_by == currentUserId ?
                                                    `<button class="btn btn-outline-primary px-4 me-2 edit-btn" data-plan-id="${plan.id}" data-bs-toggle="modal" data-bs-target="#editPlanModal">Editar</button>
                                                    <button class="btn btn-outline-danger delete-btn" data-plan-id="${plan.id}">Eliminar</button>` :
                                                    `<button class="btn btn-outline-success score-btn" data-plan-id="${plan.id}" data-bs-toggle="modal" data-bs-target="#scoreModal">Puntuar</button>`
                                                }
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                            container.appendChild(col);
                        });

                        initModalButtons();
                    });
            }

            function initModalButtons() {
                // Favorito
                document.querySelectorAll(".favorite-btn").forEach(btn => {
                    btn.addEventListener("click", e => {
                        e.stopPropagation();
                        const planId = btn.dataset.planId;
                        fetch('../src/toggle_favorite.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `plan_id=${planId}`
                        }).then(res => res.json()).then(data => {
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
                            }
                        });
                    });
                });

                // Guardado
                document.querySelectorAll(".save-btn").forEach(btn => {
                    btn.addEventListener("click", e => {
                        e.stopPropagation();
                        const planId = btn.dataset.planId;
                        fetch('../src/toggle_saved.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `plan_id=${planId}`
                        }).then(res => res.json()).then(data => {
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

                // Editar
                document.querySelectorAll(".edit-btn").forEach(btn => {
                    btn.addEventListener("click", e => {
                        const planId = btn.dataset.planId;
                        // Rellenar modal con info del plan
                        const card = btn.closest('.modal-body');
                        document.getElementById('edit-plan-id').value = planId;
                        document.getElementById('edit-title').value = card.querySelector('h3').textContent;
                        document.getElementById('edit-description').value = card.querySelector('p').textContent;
                        document.getElementById('edit-category').value = card.querySelector('.text-muted').textContent.trim();
                    });
                });
                // Eliminar
                document.querySelectorAll(".delete-btn").forEach(btn => {
                    btn.addEventListener("click", e => {
                        e.stopPropagation();
                        const planId = btn.dataset.planId;

                        if (confirm("¿Estás seguro de que deseas eliminar esta publicación?")) {

                            // Obtener el modal actual donde está el botón
                            const modalEl = btn.closest('.modal');
                            const modalInstance = bootstrap.Modal.getInstance(modalEl);

                            fetch('../src/delete_plan.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: `plan_id=${planId}`
                                })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.success) {
                                        // Cerrar el modal antes de recargar planes
                                        if (modalInstance) modalInstance.hide();

                                        // Recargar la lista de planes
                                        loadPlans(document.querySelector('.filter-btn.active').dataset.filter);

                                        alert("Plan eliminado correctamente");
                                    } else {
                                        alert("No se pudo eliminar el plan");
                                    }
                                })
                                .catch(err => {
                                    console.error(err);
                                    alert("Ocurrió un error al eliminar el plan");
                                });
                        }
                    });
                });

                // Puntuar
                document.querySelectorAll(".score-btn").forEach(btn => {
                    btn.addEventListener("click", e => {
                        const planId = btn.dataset.planId;
                        document.getElementById('submitRating').dataset.planId = planId;
                    });
                });

                // Aquí añadir la funcionalidad de enviar el formulario de editar y la puntuación
                document.getElementById('editPlanForm').addEventListener('submit', e => {
                    e.preventDefault();
                    const formData = new FormData(e.target);
                    fetch('../src/edit_plan.php', {
                        method: 'POST',
                        body: formData
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            loadPlans(document.querySelector('.filter-btn.active').dataset.filter);
                            bootstrap.Modal.getInstance(document.getElementById('editPlanModal')).hide();
                        }
                    });
                });

                document.getElementById('submitRating').addEventListener('click', e => {
                    const rating = document.getElementById('selected-rating').value;
                    const planId = e.target.dataset.planId;
                    fetch('../src/submit_rating.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `plan_id=${planId}&rating=${rating}`
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            loadPlans(document.querySelector('.filter-btn.active').dataset.filter);
                            bootstrap.Modal.getInstance(document.getElementById('scoreModal')).hide();
                        }
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
            }

        });
    </script>
</body>

</html>