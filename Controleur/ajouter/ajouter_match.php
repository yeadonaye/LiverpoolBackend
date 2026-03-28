<?php

require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';
require_once __DIR__ . '/../../Modele/Match.php';
require_once __DIR__ . "/../../Modele/DAO/connexionBD.php";

$matchDao = new MatchDao($linkpdo);
$error = '';
$success = '';

// 🔹 Récupération des données JSON
$nomEquipeAdverse = $data->Nom_Equipe_Adverse ?? '';
$dateRencontre    = $data->Date_Rencontre ?? '';
$heure            = $data->Heure ?? '';
$lieu             = $data->Lieu ?? '';
$scoreNous        = $data->Score_Nous ?? '';
$scoreAdverse     = $data->Score_Adversaire ?? '';

// 🔹 Validation
if (empty($nomEquipeAdverse) || empty($dateRencontre) || empty($heure)) {
    $error = 'Les champs avec * sont obligatoires';
} else {

    // Format date attendu : YYYY-MM-DD
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateRencontre)) {
        $error = 'Date invalide (format attendu : YYYY-MM-DD)';
    }

    // Format heure HH:MM
    if (!$error && !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $heure)) {
        $error = 'Heure invalide (format 24h HH:MM)';
    }

    // Validation scores
    if (!$error && $scoreNous !== '' && (!is_numeric($scoreNous) || $scoreNous < 0)) {
        $error = 'Score nous invalide';
    }

    if (!$error && $scoreAdverse !== '' && (!is_numeric($scoreAdverse) || $scoreAdverse < 0)) {
        $error = 'Score adverse invalide';
    }

    // 🔹 Ajout
    if (!$error) {
        try {

            $scoreNousInt = ($scoreNous !== '') ? (int)$scoreNous : 0;
            $scoreAdverseInt = ($scoreAdverse !== '') ? (int)$scoreAdverse : 0;

            // Résultat format "X-Y"
            $resultat = '';
            if ($scoreNous !== '' && $scoreAdverse !== '') {
                $resultat = $scoreNousInt . '-' . $scoreAdverseInt;
            }

            $matchObj = new Match_(
                0,
                $dateRencontre,
                $heure,
                $nomEquipeAdverse,
                $lieu,
                $resultat,
                $scoreAdverseInt,
                $scoreNousInt
            );

            $matchDao->add($matchObj);

            $success = 'Match ajouté avec succès!';

        } catch (Exception $e) {
            $error = 'Erreur lors de l\'enregistrement: ' . $e->getMessage();
        }
    }
}
?>

#foot