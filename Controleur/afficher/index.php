<?php
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';
require_once __DIR__ . '/../../Modele/Joueur.php';
require_once __DIR__ . '/../../Modele/Match.php';
require_once __DIR__ . '/../../Modele/DAO/connexionBD.php';

// Création des objets DAO pour interagir avec la base de données
$joueurDao = new JoueurDao($linkpdo);
$matchDao = new MatchDao($linkpdo);

// Initialisation des compteurs et variables
$playerCount = 0;
$injuredCount = 0;
$wins = 0;
$totalMatches = 0;
$nextMatch = null;
$recentComments = [];

// Statistiques sur les joueurs
try {
    $joueurs = $joueurDao->getAll(); // Récupère tous les joueurs
    $playerCount = count($joueurs);  // Nombre total de joueurs
    foreach ($joueurs as $j) {
        // Compte les joueurs dont le statut contient "bles" (blessé)
        if (stripos($j->getStatut(), 'bles') !== false) {
            $injuredCount++;
        }
    }
} catch (Exception $e) {
    // En cas d'erreur, les compteurs restent à leurs valeurs par défaut
}

// Statistiques sur les matchs et détermination du prochain match
try {
    $matchs = $matchDao->getAll(); // Récupère tous les matchs
    $now = new DateTime('now');     // Date/heure actuelle
    foreach ($matchs as $m) {
        $scoreN = $m->getScoreNous();
        $scoreA = $m->getScoreAdversaire();
        $hasScores = $scoreN !== null && $scoreA !== null;

        if ($hasScores) {
            $totalMatches++;       // Compte les matchs joués
            if ($scoreN > $scoreA) {
                $wins++;           // Compte les victoires
            }
        }

        // Calcul du prochain match : on cherche la date future la plus proche
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $m->getDateRencontre() . ' ' . ($m->getHeure() ?? '00:00:00'));
        if (!$dt) {
            $dt = DateTime::createFromFormat('Y-m-d H:i', $m->getDateRencontre() . ' ' . ($m->getHeure() ?? '00:00'));
        }
        if ($dt && $dt > $now) {
            // On garde le match avec la date la plus proche dans le futur
            if ($nextMatch === null || $dt < $nextMatch['dt']) {
                $nextMatch = [
                    'Date_Rencontre' => $m->getDateRencontre(),
                    'Heure' => $m->getHeure(),
                    'Nom_Equipe_Adverse' => $m->getNomEquipeAdverse(),
                    'Lieu' => $m->getLieu(),
                    'dt' => $dt // temporaire pour comparaison
                ];
            }
        }
    }
} catch (Exception $e) {
    // En cas d'erreur, les compteurs restent à leurs valeurs par défaut
}

// Retire l'objet DateTime pour ne pas l'exposer à la vue
if ($nextMatch && isset($nextMatch['dt'])) {
    unset($nextMatch['dt']);
}