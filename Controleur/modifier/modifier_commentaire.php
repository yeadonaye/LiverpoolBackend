<?php
require_once __DIR__ . '/../../Modele/DAO/auth.php';
require_once __DIR__ . '/../../Modele/DAO/CommentaireDao.php';
require_once __DIR__ . '/../../Modele/Commentaire.php';
requireAuth();

$pdo = getDBConnection();
$commentaireDao = new CommentaireDao($pdo);

$error = '';
$success = '';
$comment = null;

$commentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($commentId <= 0) {
    header('Location: /Vue/Afficher/liste_joueurs.php');
    exit;
}

try {
    $comment = $commentaireDao->getById($commentId);
    if (!$comment) {
        header('Location: /Vue/Afficher/liste_joueurs.php');
        exit;
    }
} catch (Exception $e) {
    $error = "Erreur lors du chargement du commentaire";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $dateInput = trim($_POST['date_commentaire'] ?? '');

    if ($description === '') {
        $error = 'Le commentaire est obligatoire';
    }

    // Utiliser uniquement la date (sans heure) au format jj/mm/aaaa. Si vide, conserver la valeur actuelle ou la date du jour.
    $dateForDb = $comment ? substr($comment->getDate(), 0, 10) : date('Y-m-d');
    if ($dateInput !== '') {
        $dt = DateTime::createFromFormat('d/m/Y', $dateInput) ?: DateTime::createFromFormat('Y-m-d', $dateInput);
        if ($dt) {
            $dateForDb = $dt->format('Y-m-d');
        } else {
            $error = 'Date de commentaire invalide (format jj/mm/aaaa)';
        }
    }

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
