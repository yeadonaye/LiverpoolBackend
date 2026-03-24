<?php
require_once __DIR__ . '/../../Modele/DAO/auth.php';
require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';
requireAuth();

$pdo = getDBConnection();
$matchDao = new MatchDao($pdo);
$matchs = [];
$error = '';

try {
    $matchsObjects = $matchDao->getAll();
    // Convert Match_ objects to arrays for template compatibility
    foreach ($matchsObjects as $match) {
        $matchs[] = [
            'Id_Match' => $match->getIdMatch(),
            'Date_Rencontre' => $match->getDateRencontre(),
            'Heure' => $match->getHeure(),
            'Nom_Equipe_Adverse' => $match->getNomEquipeAdverse(),
            'Lieu' => $match->getLieu(),
            'Score_Adversaire' => $match->getScoreAdversaire(),
            'Score_Nous' => $match->getScoreNous()
        ];
    }
} catch (Exception $e) {
    $error = 'Erreur lors du chargement des matchs: ' . $e->getMessage();
}

// Récupérer la composition pour chaque match
$compositions = [];
foreach ($matchs as $match) {
    try {
        $compositions[$match['Id_Match']] = $matchDao->getCompositionsByMatchId($match['Id_Match']);
    } catch (Exception $e) {
        $compositions[$match['Id_Match']] = ['titulaires' => 0, 'remplacants' => 0];
    }
}
?>
