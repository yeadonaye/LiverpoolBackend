<?php

// Inclusion des fichiers DAO et classes nécessaires
require_once __DIR__ . '/../../Modele/DAO/CommentaireDao.php';
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/Commentaire.php';
require_once __DIR__ . '/../../Modele/Joueur.php';
require_once __DIR__ . "/../../Modele/DAO/connexionBD.php";

// Initialisation des DAO
$pdo = $linkpdo;
$commentaireDao = new CommentaireDao($pdo);
$joueurDao = new JoueurDao($pdo);

$joueur = null;
$error = '';
$success = '';

// Récupération de l'ID du joueur depuis l'URL
$joueurId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($joueurId <= 0) {
    header('Location: /Vue/Afficher/liste_joueurs.php');
    exit;
}

try {
    // Chargement du joueur depuis la base
    $joueur = $joueurDao->getById($joueurId);
    if (!$joueur) {
        header('Location: /Vue/Afficher/liste_joueurs.php');
        exit;
    }
} catch (Exception $e) {
    $error = "Erreur lors du chargement du joueur";
}

// Traitement du formulaire POST pour ajouter un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération et nettoyage des champs du formulaire
    $description = trim($_POST['description'] ?? '');
    $dateInput = trim($_POST['date_commentaire'] ?? '');

    // Vérification que le commentaire n'est pas vide
    if ($description === '') {
        $error = 'Le commentaire est obligatoire';
    }

    // Date par défaut : aujourd'hui au format Y-m-d
    $dateForDb = date('Y-m-d');

    // Conversion de la date saisie par l'utilisateur (jj/mm/aaaa → Y-m-d)
    if ($dateInput !== '') {
        $dt = DateTime::createFromFormat('d/m/Y', $dateInput);
        if ($dt) {
            $dateForDb = $dt->format('Y-m-d'); // format pour la base
        } else {
            $error = 'Date de commentaire invalide (format jj/mm/aaaa)';
        }
    }

    // Si aucune erreur, création et insertion du commentaire
    if (!$error) {
        try {
            $comment = new Commentaire(0, $description, $dateForDb, $joueurId);
            $commentaireDao->add($comment);
            header('Location: /Vue/Afficher/liste_joueurs.php?success=1');
            exit;
        } catch (Exception $e) {
            $error = "Erreur lors de l'enregistrement du commentaire";
        }
    }
}
?>