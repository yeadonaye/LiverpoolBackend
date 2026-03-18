<?php
require_once __DIR__ . '/../../Modele/DAO/auth.php';
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
requireAuth();

$pdo = getDBConnection();
$joueurDao = new JoueurDao($pdo);
$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $joueur = $joueurDao->getById((int)$id);
        if ($joueur) {
            $joueurDao->delete($joueur);
        }
    } catch (Exception $e) {
        // Ignorer l'erreur
    }
}

header('Location: /Vue/Afficher/liste_joueurs.php');
exit;
?>
