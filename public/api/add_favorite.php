<?php
// public/api/add_favorite.php
session_start();
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/gamification.php';

header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'Not logged']); exit; }
$userId = (int)$_SESSION['user_id'];
$planId = (int)($_POST['plan_id'] ?? 0);
if ($planId <= 0) { http_response_code(400); echo json_encode(['error'=>'Missing plan_id']); exit; }

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO favorites (user_id, plan_id) VALUES (:u,:p)");
    $stmt->execute([':u'=>$userId,':p'=>$planId]);

    // registrar actividad; triggers DB ya suman puntos pero aseguramos registro
    register_daily_activity($pdo, $userId, 'favorite');

    // comprobar primer favorito
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = :u");
    $stmt->execute([':u'=>$userId]);
    if ((int)$stmt->fetchColumn() == 1) {
        award_achievement_if_not($pdo, $userId, 'first_favorite');
    }

    check_and_award_badges($pdo, $userId);
    evaluate_and_award_cross_achievements($pdo, $userId);

    echo json_encode(['success'=>true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
