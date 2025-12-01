<?php
session_start();
require_once 'includes/db.php'; // Ajusta según tu estructura
use App\Includes\DB;

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit;
}

$userId = $_SESSION['user_id'];
$pdo = DB::getPDO();

try {
    $updates = [];
    $params = [];

    // Carpeta uploads
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    // Banner
    if (!empty($_FILES['banner']['name'])) {
        $bannerTmp = $_FILES['banner']['tmp_name'];
        $bannerExt = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
        $bannerName = 'banner_' . time() . '.' . $bannerExt;
        move_uploaded_file($bannerTmp, $uploadDir . '/' . $bannerName);
        $updates[] = 'banner = ?';
        $params[] = 'uploads/' . $bannerName;
    }

    // Avatar
    if (!empty($_FILES['avatar']['name'])) {
        $avatarTmp = $_FILES['avatar']['tmp_name'];
        $avatarExt = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatarName = 'avatar_' . time() . '.' . $avatarExt;
        move_uploaded_file($avatarTmp, $uploadDir . '/' . $avatarName);
        $updates[] = 'profile_image = ?';
        $params[] = 'uploads/' . $avatarName;
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
        $stmt->execute($params);
    }

    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
