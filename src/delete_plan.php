<?php
session_start();
require "config.php"; // Ajusta esta ruta a tu conexión PDO

header("Content-Type: application/json");

// Verificar sesión
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "No has iniciado sesión"]);
    exit;
}

$user_id = (int) $_SESSION["user_id"];

// Verificar si llega plan_id
if (!isset($_POST["plan_id"])) {
    echo json_encode(["success" => false, "message" => "ID de plan no recibido"]);
    exit;
}

$plan_id = (int) $_POST["plan_id"];

try {
    // Verificar que el plan sea del usuario
    $stmt = $pdo->prepare("SELECT created_by FROM plans WHERE id = :id");
    $stmt->execute(["id" => $plan_id]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$plan) {
        echo json_encode(["success" => false, "message" => "Plan no encontrado"]);
        exit;
    }

    if ($plan["created_by"] != $user_id) {
        echo json_encode(["success" => false, "message" => "No tienes permiso para eliminar este plan"]);
        exit;
    }

    // Eliminar el plan
    $delete = $pdo->prepare("DELETE FROM plans WHERE id = :id");
    $delete->execute(["id" => $plan_id]);

    echo json_encode(["success" => true, "message" => "Plan eliminado"]);
    exit;

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error al eliminar el plan"]);
    exit;
}
?>