<?php

// Inclusion du DAO pour les matchs et de la connexion à la base de données
require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';
require_once __DIR__ . "/../../Modele/DAO/connexionBD.php";

// Utilisation de la connexion PDO existante
$pdo = $linkpdo;

// Initialisation du DAO des matchs
$matchDao = new MatchDao($pdo);

// Récupération de l'ID du match depuis l'URL
$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // Récupérer le match par ID
        $match = $matchDao->getById((int)$id);
        if ($match) {
            // Supprimer le match si trouvé
            $matchDao->delete($match);
        }
    } catch (Exception $e) {
        // Ignorer les erreurs éventuelles lors de la suppression
    }
}

// Redirection vers la page d'affichage des matchs
header('Location: /Vue/Afficher/afficher_match.php');
exit;
?>