<?php
require_once __DIR__ . '/../../Modele/DAO/auth.php';
require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/DAO/ParticiperDao.php';
require_once __DIR__ . '/../../Modele/DAO/CommentaireDao.php';
requireAuth();

$pdo = getDBConnection();
$matchDao = new MatchDao($pdo);
$joueurDao = new JoueurDao($pdo);
$participerDao = new ParticiperDao($pdo);
$commentaireDao = new CommentaireDao($pdo);

$error = '';
$success = '';

// Récupérer l'ID du match
$matchId = $_GET['id'] ?? null;
if (!$matchId) {
    header('Location: /Vue/Afficher/afficher_match.php');
    exit;
}

// Récupérer les infos du match
try {
    $matchObj = $matchDao->getById((int)$matchId);
    if (!$matchObj) {
        header('Location: /Vue/Afficher/afficher_match.php');
        exit;
    }
    
    $match = [
        'Id_Match' => $matchObj->getIdMatch(),
        'Date_Rencontre' => $matchObj->getDateRencontre(),
        'Heure' => $matchObj->getHeure(),
        'Nom_Equipe_Adverse' => $matchObj->getNomEquipeAdverse(),
        'Lieu' => $matchObj->getLieu(),
        'Score_Adversaire' => $matchObj->getScoreAdversaire(),
        'Score_Nous' => $matchObj->getScoreNous()
    ];
} catch (Exception $e) {
    header('Location: /Vue/Afficher/afficher_match.php');
    exit;
}

// Récupérer tous les joueurs actifs
try {
    $joueursObj = $joueurDao->getActifs();
    $joueurs = [];
    foreach ($joueursObj as $joueur) {
        $joueurs[] = [
            'Id_Joueur' => $joueur->getIdJoueur(),
            'Nom' => $joueur->getNom(),
            'Prenom' => $joueur->getPrenom(),
            'Taille' => $joueur->getTaille(),
            'Poids' => $joueur->getPoids(),
            'Statut' => $joueur->getStatut()
        ];
    }
} catch (Exception $e) {
    $error = 'Erreur lors du chargement des joueurs';
    $joueurs = [];
}

// Récupérer les postes possibles
$postes = ['Gardien', 'Défenseur', 'Milieu', 'Attaquant'];

// Récupérer la composition actuelle pour ce match
try {
    $participations = $participerDao->obtenirParMatch((int)$matchId);
} catch (Exception $e) {
    $participations = [];
}

$composition = [];
foreach ($participations as $p) {
    $composition[$p['Id_Joueur']] = $p;
}

// Traitement du formulaire de soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulaires = $_POST['titulaires'] ?? [];
    $remplacants = $_POST['remplacants'] ?? [];
    $posteTitulaires = $_POST['poste_titulaires'] ?? [];
    $posteRemplacants = $_POST['poste_remplacants'] ?? [];
    $notesPost = $_POST['note'] ?? [];

    // Ensure arrays
    if (!is_array($titulaires)) {
        $titulaires = [$titulaires];
    }
    if (!is_array($remplacants)) {
        $remplacants = [$remplacants];
    }

    // Validation du nombre minimum de joueurs (11 titulaires)
    if (count($titulaires) < 11) {
        $error = 'Vous devez sélectionner au moins 11 titulaires.';
    } else {
        try {
            // Supprimer les participations existantes
            $participerDao->supprimerParMatch((int)$matchId);

            // Insérer les titulaires
            foreach ($titulaires as $joueurId) {
                $poste = $posteTitulaires[$joueurId] ?? null;
                if ($poste) {
                    $noteVal = isset($notesPost[$joueurId]) && $notesPost[$joueurId] !== '' ? (int)$notesPost[$joueurId] : null;
                    $participerDao->ajouterParticipation((int)$joueurId, (int)$matchId, $poste, true, $noteVal);
                }
            }

            // Insérer les remplaçants
            foreach ($remplacants as $joueurId) {
                $poste = $posteRemplacants[$joueurId] ?? null;
                if ($poste) {
                    $noteVal = isset($notesPost[$joueurId]) && $notesPost[$joueurId] !== '' ? (int)$notesPost[$joueurId] : null;
                    $participerDao->ajouterParticipation((int)$joueurId, (int)$matchId, $poste, false, $noteVal);
                }
            }

            $success = 'Feuille de match sauvegardée avec succès.';
        } catch (Exception $e) {
            $error = 'Erreur lors de la sauvegarde: ' . $e->getMessage();
        }
    }
}

// Récupérer les commentaires et notes pour les joueurs
$commentaires = [];
try {
    $commentairesRows = $participerDao->obtenirCommentairesParMatch((int)$matchId);
    foreach ($commentairesRows as $row) {
        $joueurId = $row['Id_Joueur'];
        if (!isset($commentaires[$joueurId])) {
            $commentaires[$joueurId] = [];
        }
        $commentaires[$joueurId][] = $row;
    }
} catch (Exception $e) {
    $commentaires = [];
}

// Récupérer les notes pour les joueurs
$notes = [];
try {
    $notesRows = $participerDao->obtenirNotesParMatch((int)$matchId);
    foreach ($notesRows as $row) {
        $joueurId = $row['Id_Joueur'];
        if (!isset($notes[$joueurId])) {
            $notes[$joueurId] = [];
        }
        $notes[$joueurId][] = $row['Note'];
    }
} catch (Exception $e) {
    $notes = [];
}
?>
