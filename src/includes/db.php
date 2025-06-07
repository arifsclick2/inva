<?php
// This file will contain the database connection logic.
// The actual credentials will be loaded from config.php

function get_db_connection() {
    // Check if constants are defined (user needs to set them in config.php)
    if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
        // In a real app, you might throw an exception or handle this more gracefully
        die("Database configuration constants are not defined in config.php. Please set them up.");
    }

    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Trigger exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // In a real app, log this error and show a user-friendly message
        // For development, it's okay to die with the error for now.
        error_log("Database Connection Error: " . $e->getMessage());
        die("Database connection failed. Please check your configuration and ensure the database server is running. Error: " . $e->getMessage());
    }
}
?>
