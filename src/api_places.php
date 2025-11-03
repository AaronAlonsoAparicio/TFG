<?php
// src/api_places.php
require_once __DIR__ . '/config.php';

function search_places_by_mood($mood, $lat, $lng, $limit = 5) {
    $categories = mood_to_categories($mood);
    $url = GEOAPIFY_BASE_URL . '?' . http_build_query([
        'categories' => $categories,
        'filter'     => "circle:$lng,$lat,5000",
        'limit'      => $limit,
        'apiKey'     => "6bf5e1e9fbae4d92aa61c8875ff6f006"
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) return [];

    $data = json_decode($response, true);
    $features = $data['features'] ?? [];

    $places = [];
    foreach ($features as $f) {
        $p = $f['properties'];
        $places[] = [
            'title'       => $p['name'] ?? 'Sin nombre',
            'description' => $p['formatted'] ?? 'Sin dirección',
            'category'    => $mood,
            'lat'         => $f['geometry']['coordinates'][1],
            'lng'         => $f['geometry']['coordinates'][0],
            'image'       => $p['image'] ?? null,
            'external_id' => $p['place_id'] ?? null,
            'source'      => 'geoapify'
        ];
    }
    return $places;
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