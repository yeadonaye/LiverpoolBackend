<?php

// ======================================================
// Database Configuration
// ======================================================
// Default values can be overridden using environment variables
// Environment variables for production:
// BD_TYPE, BD_HOST, BD_PORT, BD_NAME, BD_USER, BD_PASS, BD_CHARSET
define('DB_TYPE', getenv('DB_TYPE') ?: 'mysql');                 
define('DB_HOST', getenv('DB_HOST') ?: 'mysql-yeadonaye.alwaysdata.net');           
define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));            
define('DB_NAME', getenv('DB_NAME') ?: 'yeadonaye_bd_gestion_equipe');      
define('DB_USER', getenv('DB_USER') ?: 'yeadonaye');                 
define('DB_PASS', getenv('DB_PASS') ?: 'admin@gestionFoot');                   
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');


// ======================================================
// Database Connection
// ======================================================

try {

    $dsn = BD_TYPE . ":host=" . BD_HOST . ";port=" . BD_PORT . ";dbname=" . BD_NAME . ";charset=" . BD_CHARSET;

    $linkpdo = new PDO(
        $dsn,
        BD_USER,
        BD_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

} catch (PDOException $e) {

    die("Erreur lors de la connexion à la base de données : " . $e->getMessage());

}

?>