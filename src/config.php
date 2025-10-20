<?php
// src/config.php
session_start();

define('DB_HOST','127.0.0.1');
define('DB_NAME','moodplanned');
define('DB_USER','root');      // ajustar
define('DB_PASS','');          // ajustar

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, $options);
} catch (Exception $e) {
    die('DB Connection error: '.$e->getMessage());
}
