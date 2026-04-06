<?php

require_once __DIR__ . '/../../Modele/DAO/CommentaireDao.php';
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/Commentaire.php';
require_once __DIR__ . '/../../Modele/Joueur.php';
require_once __DIR__ . "/../../Modele/DAO/connexionBD.php";

$pdo = $linkpdo;
$commentaireDao = new CommentaireDao($pdo);
$joueurDao = new JoueurDao($pdo);

$joueur = null;
$error = '';
$success = '';

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
} catch (Exception $e) {
    $error = "Erreur lors du chargement du joueur";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $dateInput = trim($_POST['date_commentaire'] ?? '');

    if ($description === '') {
        $error = 'Le commentaire est obligatoire';
    }

    $dateForDb = date('Y-m-d'); // default today in DB format
    if ($dateInput !== '') {
        // Parse input as d/m/Y (jj/mm/aaaa)
        $dt = DateTime::createFromFormat('d/m/Y', $dateInput);
        if ($dt) {
            $dateForDb = $dt->format('Y-m-d'); // store as Y-m-d in DB
        } else {
            $error = 'Date de commentaire invalide (format jj/mm/aaaa)';
        }
    }

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