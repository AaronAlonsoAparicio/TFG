<?php
require_once __DIR__ . '/../../src/auth.php';
require_login();
require_once __DIR__ . '/../../src/favorites.php';

header('Content-Type: application/json');

$user = current_user($pdo);
$plan_id = intval($_POST['plan_id'] ?? 0);

if (!$plan_id) {
    echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
    exit;
}

$status = toggle_favorite($pdo, $user['id'], $plan_id);
echo json_encode(['status' => $status]);
