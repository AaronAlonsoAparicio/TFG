<?php
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
    echo json_encode(['success'=>false,'message'=>'No autenticado']);
    exit;
}

if(!isset($_POST['plan_id'], $_POST['rating'])){
    echo json_encode(['success'=>false,'message'=>'Datos incompletos']);
    exit;
}

$plan_id = (int)$_POST['plan_id'];
$rating = (int)$_POST['rating'];
$user_id = $_SESSION['user_id'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=moodplanned;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insertar o actualizar rating
    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, plan_id, rating) 
                            VALUES (:user_id,:plan_id,:rating)
                            ON DUPLICATE KEY UPDATE rating=:rating");
    $stmt->execute([
        'user_id'=>$user_id,
        'plan_id'=>$plan_id,
        'rating'=>$rating
    ]);

    echo json_encode(['success'=>true]);
} catch (Exception $e){
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>