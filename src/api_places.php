<?php
// src/api_places.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../public/constantes.php';

function search_places_by_mood(string $mood, float $lat, float $lng): array {
    global $pdo; // usa la conexión ya creada

    // Escoger la constante correcta según el estado de ánimo
    switch ($mood) {
        case 'feliz':
            $sql = PLANES_FELIZ;
            break;
        case 'triste':
            $sql = PLANES_TRISTE;
            break;
        case 'enfadado':
            $sql = PLANES_ENFADADO;
            break;
        case 'relajado':
            $sql = PLANES_RELAJADO;
            break;
        case 'nervioso':
            $sql = PLANES_NERVIOSO;
            break;
        default:
            return []; // si no hay coincidencia, devolvemos vacío
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mood]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function import_place_as_plan($pdo, $place, $user_id) {
    $stmt = $pdo->prepare("
        INSERT INTO plans (title, description, category, lat, lng, source, external_id, image, created_by)
        VALUES (?, ?, ?, ?, ?, 'geoapify', ?, ?, ?)
    ");
    $ok = $stmt->execute([
        $place['title'], $place['description'], $place['category'],
        $place['lat'], $place['lng'], $place['external_id'],
        $place['image'], $user_id
    ]);

    if ($ok) {
        require_once __DIR__ . '/user.php';
        add_user_points($pdo, $user_id, 10);
        return $pdo->lastInsertId();
    }
    return false;
}