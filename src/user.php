<?php
// src/user.php
require_once __DIR__ . '/config.php';

/**
 * Obtener datos de un usuario por su ID
 */
function get_user_by_id($pdo, $id)
{
    $stmt = $pdo->prepare("SELECT id, name, email, avatar, points, created_at FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Actualizar perfil de usuario (nombre o avatar)
 */
function update_user_profile($pdo, $id, $name, $avatar = null)
{
    $stmt = $pdo->prepare("UPDATE users SET name=?, avatar=? WHERE id=?");
    return $stmt->execute([$name, $avatar, $id]);
}

/**
 * Cambiar contraseña de usuario
 */
function change_user_password($pdo, $id, $old_pass, $new_pass)
{
    // Obtener hash actual
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id=?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($old_pass, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Contraseña actual incorrecta'];
    }

    // Guardar nueva contraseña
    $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?");
    $stmt->execute([$new_hash, $id]);

    return ['success' => true, 'message' => 'Contraseña actualizada correctamente'];
}

/**
 * Sumar puntos al usuario (gamificación)
 */
function add_user_points($pdo, $id, $points)
{
    $stmt = $pdo->prepare("UPDATE users SET points = points + ? WHERE id = ?");
    return $stmt->execute([$points, $id]);
}
