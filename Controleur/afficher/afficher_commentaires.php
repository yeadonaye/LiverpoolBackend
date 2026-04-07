<?php
// Inclusion des fichiers nécessaires
require_once __DIR__ . '/../../Modele/DAO/jwt_utils.php';
require_once __DIR__ . '/../../Modele/DAO/CommentaireDao.php';
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/Commentaire.php';
require_once __DIR__ . '/../../Modele/Joueur.php';
require_once __DIR__ . "/../../Modele/DAO/connexionBD.php";

// Vérification de l'authentification via JWT
requireAuthJWT();

// Initialisation des DAO
$pdo = $linkpdo;
$commentaireDao = new CommentaireDao($pdo);
$joueurDao = new JoueurDao($pdo);

// Initialisation des variables
$joueur = null;
$commentaires = [];
$error = '';
$success = isset($_GET['success']) ? 'Commentaire ajouté avec succès !' : '';

// Récupération de l'ID du joueur depuis l'URL
$joueurId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($joueurId <= 0) {
    // Redirection si l'ID est invalide
    header('Location: /Vue/Afficher/liste_joueurs.php');
    exit;
}

try {
    // Récupération des informations du joueur
    $joueur = $joueurDao->getById($joueurId);
    if (!$joueur) {
        header('Location: /Vue/Afficher/liste_joueurs.php');
        exit;
    }

    // Récupération des commentaires associés au joueur
    $commentairesObjects = $commentaireDao->getByJoueur($joueurId);

    // Transformation des objets Commentaire en tableau avec date formatée jj/mm/aaaa
    foreach ($commentairesObjects as $commentaire) {
        $rawDate = substr($commentaire->getDate(), 0, 10);
        if ($rawDate && $rawDate !== '0000-00-00') {
            $dateObj = DateTime::createFromFormat('Y-m-d', $rawDate);
            $formattedDate = $dateObj ? $dateObj->format('d/m/Y') : '';
        } else {
            $formattedDate = ''; // ou date('d/m/Y') si tu veux la date du jour
        }

        $commentaires[] = [
            'Id_Commentaire' => $commentaire->getIdCommentaire(),
            'Description'    => $commentaire->getDescription(),
            'Date'           => $formattedDate, // date formatée
            'Id_Joueur'      => $commentaire->getIdJoueur()
        ];
    }

    // Optionnel : trier les commentaires du plus récent au plus ancien
    usort($commentaires, function($a, $b) {
        return strcmp($b['Date'], $a['Date']);
    });

} catch (Exception $e) {
    $error = "Erreur lors du chargement des commentaires";
}
?>