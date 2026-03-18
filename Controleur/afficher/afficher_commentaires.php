<?php
require_once __DIR__ . '/../../Modele/DAO/auth.php';
require_once __DIR__ . '/../../Modele/DAO/CommentaireDao.php';
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/Commentaire.php';
require_once __DIR__ . '/../../Modele/Joueur.php';
requireAuth();

$pdo = getDBConnection();
$commentaireDao = new CommentaireDao($pdo);
$joueurDao = new JoueurDao($pdo);

$joueur = null;
$commentaires = [];
$error = '';
$success = isset($_GET['success']) ? 'Commentaire ajouté avec succès!' : '';

$joueurId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($joueurId <= 0) {
    header('Location: /Vue/Afficher/liste_joueurs.php');
    exit;
}

try {
    $joueur = $joueurDao->getById($joueurId);
    if (!$joueur) {
        header('Location: /Vue/Afficher/liste_joueurs.php');
        exit;
    }

    $commentaires = $commentaireDao->getByJoueur($joueurId);
    // Trier du plus récent au plus ancien si possible (date_ DESC)
    usort($commentaires, function($a, $b) {
        return strcmp($b->getDate(), $a->getDate());
    });
} catch (Exception $e) {
    $error = "Erreur lors du chargement des commentaires";
}
