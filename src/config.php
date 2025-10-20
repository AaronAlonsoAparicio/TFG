<?php
// src/config.php

session_start();

/**
 * Configuración de base de datos
 */
define('DB_HOST', '127.0.0.1');   // Dirección del servidor de base de datos
define('DB_NAME', 'moodplanned'); // Nombre de la base de datos
define('DB_USER', 'root');        // Usuario de la base de datos
define('DB_PASS', '');            // Contraseña (vacía si no hay)

/**
 * Opciones de conexión PDO
 */
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,   // Lanza excepciones en caso de error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Devuelve arrays asociativos
    PDO::ATTR_EMULATE_PREPARES => false,           // Desactiva la emulación de consultas preparadas
];

/**
 * Función para obtener una conexión PDO
 */
function getPDOConnection()
{
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
        // Registrar el error con fecha y hora en un archivo local
        $errorMessage = "[" . date('Y-m-d H:i:s') . "] " . $e->getMessage() . PHP_EOL;
        file_put_contents(__DIR__ . '/db_errors.log', $errorMessage, FILE_APPEND);

        // Mostrar mensaje genérico (no revelar detalles del error)
        die('Error de conexión a la base de datos. Intente más tarde.');
    }
}

/**
 * Crea la conexión (opcionalmente puedes llamarla solo cuando la necesites)
 */
$pdo = getPDOConnection();
