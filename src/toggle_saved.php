<?php
session_start();
header('Content-Type: application/json');

// Conexión DB
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'moodplanned';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

if (!isset($_SESSION['user_id']) || !isset($_POST['plan_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$plan_id = (int)$_POST['plan_id'];

// Revisar si ya está guardado
$stmt = $pdo->prepare("SELECT id FROM saved_plans WHERE user_id = :user_id AND plan_id = :plan_id");
$stmt->execute(['user_id' => $user_id, 'plan_id' => $plan_id]);
$exists = $stmt->fetch();

if ($exists) {
    // Quitar guardado
    $del = $pdo->prepare("DELETE FROM saved_plans WHERE id = :id");
    $del->execute(['id' => $exists['id']]);
    echo json_encode(['success' => true, 'status' => 'removed']);
} else {
    // Añadir guardado
    $ins = $pdo->prepare("INSERT INTO saved_plans (user_id, plan_id) VALUES (:user_id, :plan_id)");
    $ins->execute(['user_id' => $user_id, 'plan_id' => $plan_id]);
    echo json_encode(['success' => true, 'status' => 'added']);
}