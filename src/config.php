<?php
// ../src/config.php

// Evitar inclusión múltiple
if (!defined('DB_CONFIG_LOADED')) {
    define('DB_CONFIG_LOADED', true);

    // Iniciar sesión si no está activa
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Configuración de base de datos
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "moodplanned";

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // En producción deberías loguear esto, no mostrar al usuario
        die("Error de conexión a la base de datos. Inténtalo más tarde.");
    }
}
?>