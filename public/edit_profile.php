<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
    exit;
}

$userId = $_SESSION['user_id'];

// ------------------- CONFIGURACIÓN DB -------------------
$host = "localhost";
$db   = "moodplanned";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

// ------------------- RUTA DE SUBIDA -------------------
$uploadDir = __DIR__ . '/assets/images/'; // barra final
if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

$updates = [];
$params = [];

// ------------------- FUNCIONES AUXILIARES -------------------
function validarImagen($file, $maxSizeMB = 5) {
    $allowedTypes = ['image/jpeg', 'image/pjpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return 'Tipo de archivo no permitido';
    }
    if ($file['size'] > $maxSizeMB * 1024 * 1024) {
        return 'El archivo es demasiado grande (máx '.$maxSizeMB.'MB)';
    }
    if (getimagesize($file['tmp_name']) === false) {
        return 'El archivo no es una imagen válida';
    }
    return true;
}

function subirImagen($file, $prefijo, $uploadDir) {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $nombre = $prefijo . '_' . time() . '.' . $ext;
    $rutaFisica = $uploadDir . $nombre;
    if (!move_uploaded_file($file['tmp_name'], $rutaFisica)) {
        return false;
    }
    return '/assets/images/' . $nombre; // ruta relativa para la DB
}

// ------------------- BIO -------------------
if (isset($_POST['bio'])) {
    $bio = trim($_POST['bio']);
    $updates[] = "bio = ?";
    $params[] = $bio;
}

// ------------------- AVATAR -------------------
if (!empty($_FILES['avatar']['name'])) {
    $res = validarImagen($_FILES['avatar'], 5);
    if ($res !== true) {
        echo json_encode(['status' => 'error', 'message' => 'Avatar: ' . $res]);
        exit;
    }
    $rutaAvatar = subirImagen($_FILES['avatar'], 'avatar_'.$userId, $uploadDir);
    if (!$rutaAvatar) {
        echo json_encode(['status' => 'error', 'message' => 'Error al subir avatar']);
        exit;
    }
    $updates[] = "profile_image = ?";
    $params[] = $rutaAvatar;
}

// ------------------- BANNER -------------------
if (!empty($_FILES['banner']['name'])) {
    $res = validarImagen($_FILES['banner'], 10);
    if ($res !== true) {
        echo json_encode(['status' => 'error', 'message' => 'Banner: ' . $res]);
        exit;
    }
    $rutaBanner = subirImagen($_FILES['banner'], 'banner_'.$userId, $uploadDir);
    if (!$rutaBanner) {
        echo json_encode(['status' => 'error', 'message' => 'Error al subir banner']);
        exit;
    }
    $updates[] = "banner = ?";
    $params[] = $rutaBanner;
}

// ------------------- ACTUALIZAR DB -------------------
if (!empty($updates)) {
    $params[] = $userId;
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

echo json_encode(['status' => 'ok', 'message' => 'Perfil actualizado correctamente']);
