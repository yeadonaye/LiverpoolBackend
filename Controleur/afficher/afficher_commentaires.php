<?php
require_once __DIR__ . '/../../Modele/DAO/jwt_utils.php';
require_once __DIR__ . '/../../Modele/DAO/CommentaireDao.php';
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/Commentaire.php';
require_once __DIR__ . '/../../Modele/Joueur.php';
require_once __DIR__ . "/../../Modele/DAO/connexionBD.php";
requireAuthJWT();

$pdo = $linkpdo;
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

    $commentairesObjects = $commentaireDao->getByJoueur($joueurId);

    // Convert Commentaire objects to arrays with formatted date (jj/mm/yyyy)
    foreach ($commentairesObjects as $commentaire) {
        $rawDate = substr($commentaire->getDate(), 0, 10);
        if ($rawDate && $rawDate !== '0000-00-00') {
            $dateObj = DateTime::createFromFormat('Y-m-d', $rawDate);
            $formattedDate = $dateObj ? $dateObj->format('d/m/Y') : '';
        } else {
            $formattedDate = ''; // or date('d/m/Y') if you want today
        }

        $commentaires[] = [
            'Id_Commentaire' => $commentaire->getIdCommentaire(),
            'Description' => $commentaire->getDescription(),
            'Date' => $formattedDate, // formatted here
            'Id_Joueur' => $commentaire->getIdJoueur()
        ];
    }

    // Optional: sort from most recent to oldest
    usort($commentaires, function($a, $b) {
        return strcmp($b['Date'], $a['Date']);
    });

} catch (Exception $e) {
    $error = "Erreur lors du chargement des commentaires";
}
?>