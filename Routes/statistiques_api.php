<?php
require_once __DIR__ 'jwt_utils.php';
require_once __DIR__ '../Modele/DAO/JoueurDao.php';
require_once __DIR__ '../Modele/DAO/MatchDao.php';
require_once __DIR__ '../Modele/DAO/connexionBD.php';

header('Content-Type: application/json');

$secret = "secret_key";
$headers = getallheaders();
$jwt = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

// Check auth and coach role
check_auth($jwt, $secret);
check_coach($jwt, $secret);

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'error' => 'Méthode HTTP non autorisée. Seule GET est acceptée.'
    ]);
    exit();
}

try {
    $joueurDao = new JoueurDao($linkpdo);
    $matchDao = new MatchDao($linkpdo);

    // Collect statistics (same as your statistiques code)
    $matchStats = $matchDao->getGlobalStats();
    $totalJoueurs = $joueurDao->compterTotalJoueurs();
    $totalMatchs = $matchStats['total'] ?? 0;
    $victoires = $matchStats['victoires'] ?? 0;
    $defaites = $matchStats['defaites'] ?? 0;
    $nuls = $matchStats['nuls'] ?? 0;
    $totalButs = $matchStats['buts'] ?? 0;
    $butsEncaisses = $matchStats['butsEncaisses'] ?? 0;

    $tauxVictoire = $totalMatchs > 0 ? round(($victoires / $totalMatchs) * 100, 1) : 0;
    $differenceButs = $totalButs - $butsEncaisses;
    $differenceButsDisplay = ($differenceButs >= 0 ? '+' : '') . $differenceButs;
    $progressEncaissesPct = $totalButs > 0 ? ($butsEncaisses / ($totalButs + 1)) * 100 : 0;
    $butsMoyenneParMatch = $totalMatchs > 0 ? number_format($totalButs / $totalMatchs, 1, ',', '') : '0';

    // Players
    $players = [];
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

    echo json_encode([
        'error' => '',
        'stats' => [
            'totalJoueurs' => $totalJoueurs,
            'totalMatchs' => $totalMatchs,
            'victoires' => $victoires,
            'defaites' => $defaites,
            'nuls' => $nuls,
            'totalButs' => $totalButs,
            'butsEncaisses' => $butsEncaisses,
            'tauxVictoire' => $tauxVictoire,
            'differenceButs' => $differenceButsDisplay,
            'progressEncaissesPct' => round($progressEncaissesPct, 1),
            'butsMoyenneParMatch' => $butsMoyenneParMatch
        ],
        'players' => $players
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors du chargement des statistiques: ' . $e->getMessage()]);
}
?>