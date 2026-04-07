<?php

require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';

// Initialisation des DAO avec la connexion PDO
$pdo = $linkpdo;
$joueurDao = new JoueurDao($pdo);
$matchDao = new MatchDao($pdo);

// Variables pour stocker les erreurs et les statistiques globales
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

// Bloc principal pour calculer les statistiques globales et par joueur
try {
    // Récupération des stats globales sur les matchs
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

    // Calcul du pourcentage de victoires
    $tauxVictoire = $stats['totalMatchs'] > 0
        ? round(($stats['victoires'] / $stats['totalMatchs']) * 100, 1)
        : 0;

    // Différence de buts pour l'affichage (+/-)
    $differenceButts = $stats['totalButs'] - $stats['butsEncaisses'];
    $differenceButtsDisplay = ($differenceButts >= 0 ? '+' : '') . $differenceButts;

    // Pourcentage de buts encaissés par rapport aux buts marqués
    $progressEncaissesPct = $stats['totalButs'] > 0
        ? (($stats['butsEncaisses'] / ($stats['totalButs'] + 1)) * 100)
        : 0;

    // Moyenne de buts par match
    $butsMoyenneParMatch = $stats['totalMatchs'] > 0
        ? number_format($stats['totalButs'] / $stats['totalMatchs'], 1, ',', '')
        : '0';

    // Récupération des joueurs avec leurs statistiques individuelles
    $joueurs = $joueurDao->getTousAvecStatistiques();
    $matchesOrdered = $matchDao->getMatchesOrderedByDate(); // utile pour calculer les sélections consécutives

    foreach ($joueurs as $joueur) {
        $idp = $joueur['Id_Joueur'];

        // Création d'un tableau résumant les stats pour chaque joueur
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
    // Gestion simple des erreurs : message stocké pour affichage
    $error = 'Erreur lors du chargement des statistiques: ' . $e->getMessage();
}
?>