<?php
// src/auth.php
require_once __DIR__ . '/config.php';

/**
 * Esta función se usa para proteger páginas privadas.
 * Si el usuario NO ha iniciado sesión, lo redirige al login.
 */
function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * Esta función devuelve la información del usuario que está logueado.
 * Puedes usarla en cualquier página para mostrar su nombre, puntos, etc.
 */
function current_user($pdo) {
    if (empty($_SESSION['user_id'])) return null;

    $stmt = $pdo->prepare("SELECT id, name, email, points FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
