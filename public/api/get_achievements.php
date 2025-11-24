<?php
// public/api/get_achievements.php
session_start();
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/gamification.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}
$userId = (int)$_SESSION['user_id'];

try {
    $ach = get_user_achievements($pdo, $userId);
    $badges = get_user_badges($pdo, $userId);
    echo json_encode(['achievements' => $ach, 'badges' => $badges]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
