<?php
// src/favorites.php
require_once __DIR__ . '/config.php';

/**
 * Añadir o quitar un plan de favoritos (toggle)
 * Devuelve 'added' o 'removed'
 */
function toggle_favorite($pdo, $user_id, $plan_id)
{
    // Comprobar si ya es favorito
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id=? AND plan_id=?");
    $stmt->execute([$user_id, $plan_id]);

    if ($stmt->fetch()) {
        $del = $pdo->prepare("DELETE FROM favorites WHERE user_id=? AND plan_id=?");
        $del->execute([$user_id, $plan_id]);
        return 'removed';
    } else {
        $ins = $pdo->prepare("INSERT INTO favorites (user_id, plan_id) VALUES (?,?)");
        $ins->execute([$user_id, $plan_id]);
        return 'added';
    }
}

/**
 * Obtener todos los favoritos de un usuario
 */
function get_favorites($pdo, $user_id)
{
    $stmt = $pdo->prepare("SELECT p.* FROM favorites f JOIN plans p ON f.plan_id = p.id WHERE f.user_id = ? ORDER BY f.created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

/**
 * Comprobar si un plan está en favoritos
 */
function is_favorite($pdo, $user_id, $plan_id)
{
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id=? AND plan_id=?");
    $stmt->execute([$user_id, $plan_id]);
    return $stmt->fetch() ? true : false;
}
