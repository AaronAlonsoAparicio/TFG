<?php
require_once __DIR__ . '/../src/auth.php';
require_login();
require_once __DIR__ . '/../src/plans.php';

$user = current_user($pdo);
$id = $_GET['id'] ?? null;

if ($id && delete_plan($pdo, $id, $user['id'])) {
    header("Location:./dashboard.php");
    exit;
} else {
    echo "No se pudo eliminar el plan (¿es tuyo?).";
}
?>