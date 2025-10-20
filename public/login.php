<?php
require_once __DIR__ . '/../src/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        // Regenerar id de sesión por seguridad
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        header('Location: /dashboard.php');
        exit;
    } else {
        $error = "Credenciales incorrectas.";
    }
}
?>