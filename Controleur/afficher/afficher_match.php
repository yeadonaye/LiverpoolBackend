<?php

// Inclusion des fichiers nécessaires pour accéder à la base et au DAO Match
require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';
require_once __DIR__ . "/../../Modele/DAO/connexionBD.php";

// Initialisation de la connexion PDO et du DAO
$pdo = $linkpdo;
$matchDao = new MatchDao($pdo);

// Initialisation des variables
$matchs = [];
$error = '';

try {
    // Récupération de tous les matchs depuis la base
    $matchsObjects = $matchDao->getAll();

    // Conversion des objets Match_ en tableaux pour compatibilité avec le template
    foreach ($matchsObjects as $match) {
        $matchs[] = [
            'Id_Match'            => $match->getIdMatch(),
            'Date_Rencontre'      => $match->getDateRencontre(),
            'Heure'               => $match->getHeure(),
            'Nom_Equipe_Adverse'  => $match->getNomEquipeAdverse(),
            'Lieu'                => $match->getLieu(),
            'Score_Adversaire'    => $match->getScoreAdversaire(),
            'Score_Nous'          => $match->getScoreNous()
        ];
    }

} catch (Exception $e) {
    // Gestion des erreurs en cas de problème lors de la récupération des matchs
    $error = 'Erreur lors du chargement des matchs : ' . $e->getMessage();
}

// Récupération de la composition pour chaque match (titulaires et remplaçants)
$compositions = [];
foreach ($matchs as $match) {
    try {
        $compositions[$match['Id_Match']] = $matchDao->getCompositionsByMatchId($match['Id_Match']);
    } catch (Exception $e) {
        // Si erreur, retourner une composition vide par défaut
        $compositions[$match['Id_Match']] = ['titulaires' => 0, 'remplacants' => 0];
    }
}
?>