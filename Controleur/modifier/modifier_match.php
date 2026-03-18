<?php
require_once __DIR__ . '/../../Modele/DAO/auth.php';
require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';
require_once __DIR__ . '/../../Modele/Match.php';
requireAuth();

$pdo = getDBConnection();
$matchDao = new MatchDao($pdo);
$match = [];
$resultats = ['Victoire', 'Nul', 'Défaite'];
$error = '';
$success = '';

$id = $_GET['id'] ?? null;

if (!$id) {
    $error = 'Aucun match spécifié';
} else {
    try {
        $matchObj = $matchDao->getById((int)$id);
        if ($matchObj) {
            // Convert object to array for template
            $match = [
                'Id_Match' => $matchObj->getIdMatch(),
                'Date_Rencontre' => $matchObj->getDateRencontre(),
                'Heure' => $matchObj->getHeure(),
                'Nom_Equipe_Adverse' => $matchObj->getNomEquipeAdverse(),
                'Lieu' => $matchObj->getLieu(),
                'Resultat' => $matchObj->getResultat(),
                'Score_Adversaire' => $matchObj->getScoreAdversaire(),
                'Score_Nous' => $matchObj->getScoreNous()
            ];
        } else {
            $error = 'Match non trouvé';
        }
    } catch (Exception $e) {
        $error = 'Erreur lors du chargement du match';
    }
}

// Show success message after redirect (Post-Redirect-Get)
if (isset($_GET['success']) && $_GET['success'] === 'modified') {
    $success = 'Match modifié avec succès!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomEquipeAdverse = $_POST['nomEquipeAdverse'] ?? '';
    $dateRencontre = $_POST['dateRencontre'] ?? '';
    $heure = $_POST['heure'] ?? '';
    $lieu = $_POST['lieu'] ?? '';
    $resultat = $_POST['resultat'] ?? '';
    $scoreNous = $_POST['scoreNous'] ?? '';
    $scoreAdverse = $_POST['scoreAdverse'] ?? '';
    $scoreNousInt = ($scoreNous === '' ? '-' : (int)$scoreNous);
    $scoreAdverseInt = ($scoreAdverse === '' ? '-' : (int)$scoreAdverse);

    if (empty($nomEquipeAdverse) || empty($dateRencontre) || empty($heure)) {
        $error = 'Les champs avec * sont obligatoires';
    }

    // Déterminer si le match est passé ou futur (accepte d/m/Y et Y-m-d)
    $matchIsPast = false;
    if (!$error) {
        $matchDt = DateTime::createFromFormat('d/m/Y H:i', $dateRencontre . ' ' . $heure);
        if (!$matchDt) {
            $matchDt = DateTime::createFromFormat('Y-m-d H:i', $dateRencontre . ' ' . $heure);
        }
        if ($matchDt) {
            $matchIsPast = $matchDt <= new DateTime();
        }
    }

    if (!$error) {
        if (!$matchIsPast) {
            // Match futur : résultat et scores interdits
            if ($resultat !== '') {
                $error = 'Impossible de saisir un résultat pour un match futur';
            } elseif ($scoreNous !== '' || $scoreAdverse !== '') {
                $error = 'Impossible de saisir des scores pour un match futur';
            }
        } else {
            // Match passé : résultat obligatoire et cohérent avec les scores
            if (!in_array($resultat, $resultats, true)) {
                $error = 'Le résultat est obligatoire pour un match déjà joué';
            }

            if (!$error && (($scoreNous === '') xor ($scoreAdverse === ''))) {
                $error = 'Veuillez saisir les deux scores ou laisser les deux vides';
            }

            if (!$error && $scoreNous !== '' && $scoreAdverse !== '') {
                $expectedResultat = 'Nul';
                if ($scoreNousInt > $scoreAdverseInt) {
                    $expectedResultat = 'Victoire';
                } elseif ($scoreNousInt < $scoreAdverseInt) {
                    $expectedResultat = 'Défaite';
                }

                if ($resultat !== $expectedResultat) {
                    $error = 'Résultat incohérent avec les scores saisis';
                }
            }
        }
    }

    if (!$error) {
        try {
            // Modification
            $matchObj = new Match_(
                $id,
                $dateRencontre,
                $heure,
                $nomEquipeAdverse,
                $lieu,
                $resultat,
                $scoreAdverseInt,
                $scoreNousInt
            );
            $matchDao->update($matchObj);
            $success = 'Match modifié avec succès!';
            
            // Update $match array for display
            $match = [
                'Id_Match' => $id,
                'Date_Rencontre' => $dateRencontre,
                'Heure' => $heure,
                'Nom_Equipe_Adverse' => $nomEquipeAdverse,
                'Lieu' => $lieu,
                'Resultat' => $resultat,
                'Score_Adversaire' => $scoreAdverseInt,
                'Score_Nous' => $scoreNousInt
            ];
        } catch (Exception $e) {
            $error = 'Erreur lors de l\'enregistrement: ' . $e->getMessage();
        }
    }
}
?>
