<?php

// Inclusion du DAO pour les joueurs et de la connexion à la base de données
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . "/../../Modele/DAO/connexionBD.php";

// Utilisation de la connexion PDO existante
$pdo = $linkpdo;

// Initialisation du DAO des joueurs
$joueurDao = new JoueurDao($pdo);

// Récupération de l'ID du joueur depuis l'URL
$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // Récupérer le joueur par ID
        $joueur = $joueurDao->getById((int)$id);
        if ($joueur) {
            // Supprimer le joueur si trouvé
            $joueurDao->delete($joueur);
        }
    } catch (Exception $e) {
        // Ignorer les erreurs éventuelles lors de la suppression
    }
}

// Redirection vers la page de liste des joueurs
header('Location: /Vue/Afficher/liste_joueurs.php');
exit;
?>