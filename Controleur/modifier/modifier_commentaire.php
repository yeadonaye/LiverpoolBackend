<?php

// Inclusion des fichiers DAO et classe Commentaire
require_once __DIR__ . '/../../Modele/DAO/CommentaireDao.php';
require_once __DIR__ . '/../../Modele/Commentaire.php';
require_once __DIR__ . "/../../Modele/DAO/connexionBD.php";

// Initialisation du DAO des commentaires
$pdo = $linkpdo;
$commentaireDao = new CommentaireDao($pdo);

$error = '';
$success = '';
$comment = null;

// Récupération de l'ID du commentaire depuis l'URL
$commentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($commentId <= 0) {
    header('Location: /Vue/Afficher/liste_joueurs.php');
    exit;
}

try {
    // Chargement du commentaire depuis la base
    $comment = $commentaireDao->getById($commentId);
    if (!$comment) {
        header('Location: /Vue/Afficher/liste_joueurs.php');
        exit;
    }

    // Formatage de la date pour l'affichage dans le formulaire (jj/mm/aaaa)
    $rawDate = substr($comment->getDate(), 0, 10);
    if ($rawDate && $rawDate !== '0000-00-00') {
        $dateObj = DateTime::createFromFormat('Y-m-d', $rawDate);
        $commentDateFormatted = $dateObj ? $dateObj->format('d/m/Y') : '';
    } else {
        $commentDateFormatted = ''; // ou date('d/m/Y') si tu veux la date du jour
    }

} catch (Exception $e) {
    $error = "Erreur lors du chargement du commentaire";
}

// Traitement du formulaire POST pour modifier le commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération et nettoyage des données
    $description = trim($_POST['description'] ?? '');
    $dateInput = trim($_POST['date_commentaire'] ?? '');

    // Vérification du champ description
    if ($description === '') {
        $error = 'Le commentaire est obligatoire';
    }

    // Date par défaut : date actuelle du commentaire ou aujourd'hui
    $dateForDb = $comment ? substr($comment->getDate(), 0, 10) : date('Y-m-d');

    // Si l'utilisateur a saisi une nouvelle date, conversion du format jj/mm/aaaa → Y-m-d
    if ($dateInput !== '') {
        $dt = DateTime::createFromFormat('d/m/Y', $dateInput);
        if ($dt) {
            $dateForDb = $dt->format('Y-m-d'); // format pour la base de données
        } else {
            $error = 'Date de commentaire invalide (format jj/mm/aaaa)';
        }
    }

    // Mise à jour du commentaire si aucune erreur
    if (!$error && $comment) {
        try {
            $comment->setDescription($description);
            $comment->setDate($dateForDb);
            $commentaireDao->update($comment);
            header('Location: /Vue/Afficher/afficher_commentaires.php?id=' . $comment->getIdJoueur() . '&success=1');
            exit;
        } catch (Exception $e) {
            $error = "Erreur lors de la mise à jour du commentaire";
        }
    }
}
?>