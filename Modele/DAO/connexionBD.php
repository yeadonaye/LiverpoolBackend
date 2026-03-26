<?php

// ======================================================
// Database Configuration
// ======================================================
// Default values can be overridden using environment variables
// Environment variables for production:
// BD_TYPE, BD_HOST, BD_PORT, BD_NAME, BD_USER, BD_PASS, BD_CHARSET
define('BD_TYPE', getenv('BD_TYPE') ?: 'mysql');                 
define('BD_HOST', getenv('BD_HOST') ?: 'mysql-yeadonaye.alwaysdata.net');           
define('BD_PORT', (int)(getenv('BD_PORT') ?: 3306));            
define('BD_NAME', getenv('BD_NAME') ?: 'yeadonaye_bd_gestion_equipe');      
define('BD_USER', getenv('BD_USER') ?: 'yeadonaye');                 
define('BD_PASS', getenv('BD_PASS') ?: 'admin@gestionFoot');                   
define('BD_CHARSET', getenv('BD_CHARSET') ?: 'utf8mb4');


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