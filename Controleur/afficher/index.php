<?php
require_once __DIR__ . '/../../Modele/DAO/auth.php';
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';
require_once __DIR__ . '/../../Modele/Joueur.php';
require_once __DIR__ . '/../../Modele/Match.php';
requireAuth();

$pdo = getDBConnection();
$joueurDao = new JoueurDao($pdo);
$matchDao = new MatchDao($pdo);

$playerCount = 0;
$injuredCount = 0;
$wins = 0;
$totalMatches = 0;
$nextMatch = null;
$recentComments = [];

// Stats joueurs
try {
    $joueurs = $joueurDao->getAll();
    $playerCount = count($joueurs);
    foreach ($joueurs as $j) {
        if (stripos($j->getStatut(), 'bles') !== false) {
            $injuredCount++;
        }
    }
} catch (Exception $e) {
    // valeurs par défaut si erreur
}

// Stats matchs + prochain match
try {
    $matchs = $matchDao->getAll();
    $now = new DateTime('now');
    foreach ($matchs as $m) {
        $scoreN = $m->getScoreNous();
        $scoreA = $m->getScoreAdversaire();
        $hasScores = $scoreN !== null && $scoreA !== null;
        if ($hasScores) {
            $totalMatches++;
            if ($scoreN > $scoreA) {
                $wins++;
            }
        }

        // prochain match : date future la plus proche
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $m->getDateRencontre() . ' ' . ($m->getHeure() ?? '00:00:00'));
        if (!$dt) {
            $dt = DateTime::createFromFormat('Y-m-d H:i', $m->getDateRencontre() . ' ' . ($m->getHeure() ?? '00:00'));
        }
        if ($dt && $dt > $now) {
            if ($nextMatch === null || $dt < $nextMatch['dt']) {
                $nextMatch = [
                    'Date_Rencontre' => $m->getDateRencontre(),
                    'Heure' => $m->getHeure(),
                    'Nom_Equipe_Adverse' => $m->getNomEquipeAdverse(),
                    'Lieu' => $m->getLieu(),
                    'dt' => $dt
                ];
            }
        }
    }
} catch (Exception $e) {
    // valeurs par défaut si erreur
}

// Ne pas exposer l'objet DateTime dans la vue
if ($nextMatch && isset($nextMatch['dt'])) {
    unset($nextMatch['dt']);
}
