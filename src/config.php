<?php
// src/config.php

session_start();

/**
 * Configuración de base de datos
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'moodplanned');
define('DB_USER', 'root');
define('DB_PASS', '');

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

function getPDOConnection() {
    global $options;
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            $options
        );
        return $pdo;
    } catch (PDOException $e) {
        $errorMessage = "[" . date('Y-m-d H:i:s') . "] " . $e->getMessage() . PHP_EOL;
        file_put_contents(__DIR__ . '/db_errors.log', $errorMessage, FILE_APPEND);
        die('Error de conexión a la base de datos. Intente más tarde.');
    }
}

$pdo = getPDOConnection();

/**
 * === GEOAPIFY PLACES API CONFIG ===
 */
define('GEOAPIFY_API_KEY', '6b5fe1e9fbae4d92aa61c8875fff6006');
define('GEOAPIFY_BASE_URL', 'https://api.geoapify.com/v2/places');

function mood_to_categories($mood) {
    $map = [
        'feliz'     => 'catering.restaurant,entertainment.cinema,entertainment',
        'triste'    => 'leisure.park,nature,nature.natural_feature',
        'relajado'  => 'leisure.spa,accommodation.hotel,healthcare',
        'enérgico'  => 'leisure.sports_centre,commercial.shopping_mall',
        'estresado' => 'catering.cafe,healthcare.doctor,leisure'
    ];
    return $map[strtolower(trim($mood))] ?? 'catering';
}