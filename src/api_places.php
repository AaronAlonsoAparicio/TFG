<?php
// src/api_places.php
require_once __DIR__ . '/config.php';       // carga $pdo
require_once __DIR__ . '/../public/constantes.php';

function search_places_by_mood(string $mood, ?float $lat = null, ?float $lng = null, int $limit = 5): array {
    global $pdo;

    // Normalizamos el mood por seguridad
    $mood = trim(strtolower($mood));

    // Usa LIKE con comodines para evitar fallos por coincidencia exacta
    $sql = PLANES_POR_ESTADO . " LIMIT :limit";
    $stmt = $pdo->prepare($sql);

    // bindValue para el limit (integer) y ejecutar con comodín en category
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute(["%{$mood}%"]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
