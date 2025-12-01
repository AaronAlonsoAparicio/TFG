<?php
/*
    update_profile.php - versión depurada con mensajes de error detallados
*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/db.php'; // Ajusta la ruta según tu proyecto
use App\Includes\DB;

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
    exit;
}

$userId = $_SESSION['user_id'];
$pdo = DB::getPDO();

try {
    $updates = [];
    $params = [];

    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('No se pudo crear la carpeta de uploads');
        }
    }

    // Banner
    if (!empty($_FILES['banner']['name'])) {
        if ($_FILES['banner']['error'] === 0) {
            $bannerTmp = $_FILES['banner']['tmp_name'];
            $bannerExt = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
            if (!in_array($bannerExt, ['jpg','jpeg','png','gif'])) {
                throw new Exception('Formato de banner no permitido');
            }
            $bannerName = 'banner_' . time() . '.' . $bannerExt;
            if (!move_uploaded_file($bannerTmp, $uploadDir . '/' . $bannerName)) {
                throw new Exception('Error al mover el archivo de banner');
            }
            $updates[] = 'banner = ?';
            $params[] = 'uploads/' . $bannerName;
        } else {
            throw new Exception('Error subiendo el banner: ' . $_FILES['banner']['error']);
        }
    }

    // Avatar
    if (!empty($_FILES['avatar']['name'])) {
        if ($_FILES['avatar']['error'] === 0) {
            $avatarTmp = $_FILES['avatar']['tmp_name'];
            $avatarExt = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (!in_array($avatarExt, ['jpg','jpeg','png','gif'])) {
                throw new Exception('Formato de avatar no permitido');
            }
            $avatarName = 'avatar_' . time() . '.' . $avatarExt;
            if (!move_uploaded_file($avatarTmp, $uploadDir . '/' . $avatarName)) {
                throw new Exception('Error al mover el archivo de avatar');
            }
            $updates[] = 'profile_image = ?';
            $params[] = 'uploads/' . $avatarName;
        } else {
            throw new Exception('Error subiendo el avatar: ' . $_FILES['avatar']['error']);
        }
    }

    // Bio
    if (isset($_POST['bio'])) {
        $updates[] = 'bio = ?';
        $params[] = $_POST['bio'];
    }

    if (!empty($updates)) {
        $sql = "UPDATE users SET " . implode(',', $updates) . " WHERE id = ?";
        $params[] = $userId;
        $stmt = $pdo->prepare($sql);
        if (!$stmt->execute($params)) {
            throw new Exception('Error ejecutando la consulta SQL');
        }
    } else {
        throw new Exception('No hay datos para actualizar');
    }

    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
