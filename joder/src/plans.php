<?php
// src/plans.php
require_once __DIR__ . '/config.php';

/**
 * Crear un nuevo plan
 */
function create_plan($pdo, $data)
{
    $stmt = $pdo->prepare("INSERT INTO plans (title, description, category, lat, lng, image, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['title'],
        $data['description'],
        $data['category'],
        $data['lat'] ?: null,
        $data['lng'] ?: null,
        $data['image'] ?: null,
        $data['created_by']
    ]);
    return $pdo->lastInsertId();
}

/**
 * Obtener todos los planes (últimos primero)
 */
function get_plans($pdo)
{
    $stmt = $pdo->query("SELECT p.*, u.name AS author FROM plans p LEFT JOIN users u ON p.created_by = u.id ORDER BY p.created_at DESC");
    return $stmt->fetchAll();
}

/**
 * Obtener un plan por ID
 */
function get_plan($pdo, $id)
{
    $stmt = $pdo->prepare("SELECT p.*, u.name AS author FROM plans p LEFT JOIN users u ON p.created_by = u.id WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Actualizar un plan (solo si es del usuario actual)
 */
function update_plan($pdo, $data)
{
    $stmt = $pdo->prepare("UPDATE plans SET title=?, description=?, category=?, lat=?, lng=?, image=? WHERE id=? AND created_by=?");
    return $stmt->execute([
        $data['title'],
        $data['description'],
        $data['category'],
        $data['lat'] ?: null,
        $data['lng'] ?: null,
        $data['image'] ?: null,
        $data['id'],
        $data['created_by']
    ]);
}

/**
 * Eliminar un plan (solo si es del usuario actual)
 */
function delete_plan($pdo, $plan_id, $user_id)
{
    $stmt = $pdo->prepare("DELETE FROM plans WHERE id = ? AND created_by = ?");
    return $stmt->execute([$plan_id, $user_id]);
}
