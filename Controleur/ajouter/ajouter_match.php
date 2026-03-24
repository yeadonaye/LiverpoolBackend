<?php
require_once __DIR__ . '/../../Modele/DAO/auth.php';
require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';
require_once __DIR__ . '/../../Modele/Match.php';
requireAuth(); 

$pdo = getDBConnection();
$matchDao = new MatchDao($pdo);
$match = null;
$error = '';
$success = '';

// Helpers
$toFrDate = static function (?string $date) {
    if (!$date) return '';
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d ? $d->format('d/m/Y') : $date;
};

$toDbDate = static function (?string $dateFr) {
    if (!$dateFr) return null;
    $d = DateTime::createFromFormat('d/m/Y', $dateFr);
    return $d ? $d->format('Y-m-d') : null;
};

$normalizeTime = static function (?string $time) {
    if (!$time) return '';
    // Accept HH:MM or HH:MM:SS from DB, return HH:MM
    if (preg_match('/^(\d{2}:\d{2})/', $time, $m)) {
        return $m[1];
    }
    return $time;
};

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $match = $matchDao->getById((int)$id);
    } catch (Exception $e) {
        $error = 'Erreur lors du chargement du match';
    }
}

// Show success message after redirect (Post-Redirect-Get)
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'modified') {
        $success = 'Match modifié avec succès!';
    } elseif ($_GET['success'] === 'created') {
        $success = 'Match ajouté avec succès!';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomEquipeAdverse = $_POST['nomEquipeAdverse'] ?? '';
    $dateRencontre = $_POST['dateRencontre'] ?? '';
    $heure = $_POST['heure'] ?? '';
    $lieu = $_POST['lieu'] ?? '';
    $scoreNous = $_POST['scoreNous'] ?? '';
    $scoreAdverse = $_POST['scoreAdverse'] ?? '';
    // Les scores par défaut à 0 si laissés vides
    $scoreNousInt = ($scoreNous === '' ? 0 : (int)$scoreNous);
    $scoreAdverseInt = ($scoreAdverse === '' ? 0 : (int)$scoreAdverse);

    if (empty($nomEquipeAdverse) || empty($dateRencontre) || empty($heure)) {
        $error = 'Les champs avec * sont obligatoires';
    }

    // Validate date format dd/mm/yyyy
    $dateSql = $toDbDate($dateRencontre);
    if (!$error && !$dateSql) {
        $error = 'Date invalide (format jj/mm/aaaa)';
    }

    // Validate 24h time HH:MM
    if (!$error && !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $heure)) {
        $error = 'Heure invalide (format 24h HH:MM)';
    }

    if (!$error) {
        try {
            // Créer le résultat au format "3-2" si les scores sont fournis
            $resultat = '';
            if ($scoreNous !== '' && $scoreAdverse !== '') {
                $resultat = $scoreNousInt . '-' . $scoreAdverseInt;
            }

            if ($id) {
                // Modification
                $matchObj = new Match_(
                    (int)$id,
                    $dateSql,
                    $heure,
                    $nomEquipeAdverse,
                    $lieu,
                    $scoreAdverseInt,
                    $scoreNousInt
                );
                $matchDao->update($matchObj);
                // Redirect to reload fresh data from DB (Post-Redirect-Get)
                header('Location: /Vue/Ajouter/ajouter_match.php?id=' . $id . '&success=modified');
                exit;
            } else {
                // Ajout
                $matchObj = new Match_(
                    0,
                    $dateSql,
                    $heure,
                    $nomEquipeAdverse,
                    $lieu,
                    $scoreAdverseInt,
                    $scoreNousInt
                );
                $matchDao->add($matchObj);
                // Redirection automatique vers la liste des matchs
                header('Location: /Vue/Afficher/afficher_match.php');
                exit;
            }
        } catch (Exception $e) {
            $error = 'Erreur lors de l\'enregistrement: ' . $e->getMessage();
        }
    }
}

// For template display - convert object to array if needed
$match_display = null;
if ($match) {
    $match_display = [
        'Id_Match' => $match->getIdMatch(),
        'Nom_Equipe_Adverse' => $match->getNomEquipeAdverse(),
        'Date_Rencontre' => $toFrDate($match->getDateRencontre()),
        'Heure' => $normalizeTime($match->getHeure()),
        'Lieu' => $match->getLieu(),
        'Score_Adversaire' => $match->getScoreAdversaire(),
        'Score_Nous' => $match->getScoreNous()
    ];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Keep user input on validation errors
    $match_display = [
        'Id_Match' => $id,
        'Nom_Equipe_Adverse' => $nomEquipeAdverse ?? '',
        'Date_Rencontre' => $dateRencontre ?? '',
        'Heure' => $heure ?? '',
        'Lieu' => $lieu ?? '',
        'Score_Adversaire' => $scoreAdverse,
        'Score_Nous' => $scoreNous
    ];
}
?>
