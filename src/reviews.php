<?php
// src/reviews.php
require_once __DIR__ . '/config.php';

/**
 * Crear reseña
 */
function add_review($pdo, $user_id, $plan_id, $rating, $comment)
{
    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, plan_id, rating, comment) VALUES (?,?,?,?)");
    $stmt->execute([$user_id, $plan_id, $rating, $comment]);
    return $pdo->lastInsertId();
}

/**
 * Obtener reseñas de un plan
 */
function get_reviews($pdo, $plan_id)
{
    $stmt = $pdo->prepare("SELECT r.*, u.name AS author FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.plan_id = ? ORDER BY r.created_at DESC");
    $stmt->execute([$plan_id]);
    return $stmt->fetchAll();
}

/**
 * Calcular media de puntuación de un plan
 */
function get_average_rating($pdo, $plan_id)
{
    $stmt = $pdo->prepare("SELECT AVG(rating) AS avg_rating FROM reviews WHERE plan_id=?");
    $stmt->execute([$plan_id]);
    $row = $stmt->fetch();
    return round($row['avg_rating'] ?? 0, 1);
}

/**
 * Eliminar reseña (solo el autor)
 */
function delete_review($pdo, $review_id, $user_id)
{
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id=? AND user_id=?");
    return $stmt->execute([$review_id, $user_id]);
}
