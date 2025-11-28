<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No has iniciado sesiÃ³n']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$plan_id = isset($_POST['plan_id']) ? (int)$_POST['plan_id'] : 0;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$category = isset($_POST['category']) ? trim($_POST['category']) : '';

if (!$plan_id || !$title || !$description || !$category) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

// Verificar que el plan sea del usuario
$stmt = $pdo->prepare("SELECT created_by FROM plans WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $plan_id]);
$plan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$plan) {
    echo json_encode(['success' => false, 'message' => 'Plan no encontrado']);
    exit;
}

if ((int)$plan['created_by'] !== $user_id) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar este plan']);
    exit;
}

// Actualizar plan
$update = $pdo->prepare("UPDATE plans SET title = :title, description = :description, category = :category WHERE id = :id");
$update->execute([
    'title' => $title,
    'description' => $description,
    'category' => $category,
    'id' => $plan_id
]);

echo json_encode(['success' => true, 'message' => 'Plan actualizado correctamente']);
exit;
?>