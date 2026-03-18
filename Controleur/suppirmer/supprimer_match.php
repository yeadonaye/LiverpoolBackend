<?php
require_once __DIR__ . '/../../Modele/DAO/auth.php';
require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';
requireAuth();

$pdo = getDBConnection();
$matchDao = new MatchDao($pdo);
$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $match = $matchDao->getById((int)$id);
        if ($match) {
            $matchDao->delete($match);
        }
    } catch (Exception $e) {
        // Ignorer l'erreur
    }
}

header('Location: /Vue/Afficher/afficher_match.php');
exit;
?>
