<?php
require_once __DIR__ . '/../../Modele/DAO/auth.php';
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';
requireAuth();

$pdo = getDBConnection();
$joueurDao = new JoueurDao($pdo);
$matchDao = new MatchDao($pdo);

$error = '';
$stats = [
    'totalJoueurs' => 0,
    'totalMatchs' => 0,
    'victoires' => 0,
    'defaites' => 0,
    'nuls' => 0,
    'totalButs' => 0,
    'butsEncaisses' => 0,
];
$tauxVictoire = 0;
$differenceButts = 0;
$differenceButtsDisplay = '0';
$butsMoyenneParMatch = '0';
$progressEncaissesPct = 0;
$players = [];

try {
    $matchStats = $matchDao->getGlobalStats();
    $stats = [
        'totalJoueurs'   => $joueurDao->compterTotalJoueurs(),
        'totalMatchs'    => $matchStats['total'] ?? 0,
        'victoires'      => $matchStats['victoires'] ?? 0,
        'defaites'       => $matchStats['defaites'] ?? 0,
        'nuls'           => $matchStats['nuls'] ?? 0,
        'totalButs'      => $matchStats['buts'] ?? 0,
        'butsEncaisses'  => $matchStats['butsEncaisses'] ?? 0,
    ];

    $tauxVictoire = $stats['totalMatchs'] > 0
        ? round(($stats['victoires'] / $stats['totalMatchs']) * 100, 1)
        : 0;

    $differenceButts = $stats['totalButs'] - $stats['butsEncaisses'];
    $differenceButtsDisplay = ($differenceButts >= 0 ? '+' : '') . $differenceButts;

    $progressEncaissesPct = $stats['totalButs'] > 0
        ? (($stats['butsEncaisses'] / ($stats['totalButs'] + 1)) * 100)
        : 0;

    $butsMoyenneParMatch = $stats['totalMatchs'] > 0
        ? number_format($stats['totalButs'] / $stats['totalMatchs'], 1, ',', '')
        : '0';

    $joueurs = $joueurDao->getTousAvecStatistiques();
    $matchesOrdered = $matchDao->getMatchesOrderedByDate();

    foreach ($joueurs as $joueur) {
        $idp = $joueur['Id_Joueur'];

        $players[] = [
            'Nom' => $joueur['Nom'] ?? '',
            'Prenom' => $joueur['Prenom'] ?? '',
            'Statut' => $joueur['Statut'] ?? '',
            'starts' => $joueurDao->compterTitularisations($idp),
            'subs' => $joueurDao->compterRemplacements($idp),
            'avgNote' => $joueurDao->obtenirNoteMoyenne($idp),
            'participations' => $joueurDao->compterParticipations($idp),
            'winPercentWhenParticipated' => $joueurDao->pourcentageVictoiresLorsParticipation($idp),
            'consecutiveSelections' => $joueurDao->compterSelectionsConsecutives($idp, $matchesOrdered),
        ];
    }
} catch (Exception $e) {
    $error = 'Erreur lors du chargement des statistiques: ' . $e->getMessage();
}
?>
