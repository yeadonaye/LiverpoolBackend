<?php

// ======================================================
// Configuration de la base de données
// ======================================================
// Valeurs par défaut, peuvent être remplacées par des variables d'environnement en production
define('BD_TYPE', getenv('BD_TYPE') ?: 'mysql');                 
define('BD_HOST', getenv('BD_HOST') ?: 'mysql-yeadonaye.alwaysdata.net');           
define('BD_PORT', (int)(getenv('BD_PORT') ?: 3306));            
define('BD_NAME', getenv('BD_NAME') ?: 'yeadonaye_bd_gestion_equipe');      
define('BD_USER', getenv('BD_USER') ?: 'yeadonaye');                 
define('BD_PASS', getenv('BD_PASS') ?: 'admin@gestionFoot');                   
define('BD_CHARSET', getenv('BD_CHARSET') ?: 'utf8mb4'); // Jeu de caractères utilisé pour la connexion

// ======================================================
// Connexion à la base de données avec PDO
// ======================================================
try {

    // Création du DSN pour PDO (Data Source Name)
    $dsn = BD_TYPE . ":host=" . BD_HOST . ";port=" . BD_PORT . ";dbname=" . BD_NAME . ";charset=" . BD_CHARSET;

    // Initialisation de la connexion PDO avec gestion des erreurs et mode de récupération par défaut
    $linkpdo = new PDO(
        $dsn,
        BD_USER,
        BD_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Les erreurs lanceront des exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Les résultats seront récupérés sous forme de tableau associatif
        ]
    );

} catch (PDOException $e) {
    // Arrêt du script si la connexion échoue et affichage du message d'erreur
    die("Erreur lors de la connexion à la base de données : " . $e->getMessage());
}

?>