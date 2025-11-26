<?php
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no logueado']);
    exit;
}

$current_user_id = $_SESSION['user_id'];

if(!isset($_POST['plan_id'])) {
    echo json_encode(['success' => false, 'message' => 'Plan ID no recibido']);
    exit;
}

$plan_id = intval($_POST['plan_id']);

// Conexión a la BBDD
$pdo = new PDO("mysql:host=localhost;dbname=moodplanned;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Verificar si ya está en favoritos
$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = :user_id AND plan_id = :plan_id");
$stmt->execute(['user_id' => $current_user_id, 'plan_id' => $plan_id]);
$exists = $stmt->fetch();

if($exists) {
    // Borrar
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE id = :id");
    $stmt->execute(['id' => $exists['id']]);
    echo json_encode(['success' => true, 'status' => 'removed']);
} else {
    // Agregar
    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, plan_id) VALUES (:user_id, :plan_id)");
    $stmt->execute(['user_id' => $current_user_id, 'plan_id' => $plan_id]);
    echo json_encode(['success' => true, 'status' => 'added']);
}