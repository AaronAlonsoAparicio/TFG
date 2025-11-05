<?php
// src/api_places.php
require_once __DIR__ . '/config.php';       // carga $pdo
require_once __DIR__ . '/../public/constantes.php';

function search_places_by_mood(string $mood, ?float $lat = null, ?float $lng = null, int $limit = 5): array {
    global $pdo;

    $mood = trim(strtolower($mood));

    // Consulta: usamos solo parámetros con nombre
    $sql = "SELECT id, title, description, category, image, lat, lng
            FROM plans
            WHERE category LIKE :mood
            LIMIT :limit";

    $stmt = $pdo->prepare($sql);

    // Asignamos los valores
    $stmt->bindValue(':mood', "%{$mood}%", PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
