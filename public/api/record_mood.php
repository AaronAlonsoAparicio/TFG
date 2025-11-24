<?php
// public/api/record_mood.php
session_start();
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/gamification.php';

header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'Not logged']); exit; }
$userId = (int)$_SESSION['user_id'];
$mood = trim($_POST['mood'] ?? '');
$note = trim($_POST['note'] ?? '');
if ($mood === '') { http_response_code(400); echo json_encode(['error'=>'Missing mood']); exit; }

try {
    $stmt = $pdo->prepare("INSERT INTO moods (user_id, mood, note) VALUES (:u,:m,:n)");
    $stmt->execute([':u'=>$userId,':m'=>$mood,':n'=>$note]);

    register_daily_activity($pdo, $userId, 'mood');

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM moods WHERE user_id = :u");
    $stmt->execute([':u'=>$userId]);
    if ((int)$stmt->fetchColumn() == 1) {
        award_achievement_if_not($pdo, $userId, 'first_mood');
    }

    evaluate_and_award_cross_achievements($pdo, $userId);
    check_and_award_badges($pdo, $userId);

    echo json_encode(['success'=>true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
