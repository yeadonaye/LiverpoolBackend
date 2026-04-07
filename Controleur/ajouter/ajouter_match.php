<?php

// Inclusion des fichiers DAO et de la classe Match
require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';
require_once __DIR__ . '/../../Modele/Match.php';
require_once __DIR__ . "/../../Modele/DAO/connexionBD.php";

// Initialisation du DAO
$matchDao = new MatchDao($linkpdo);
$error   = '';
$success = '';

// 🔹 Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $error = 'Méthode non autorisée';
} else {

    // 🔹 Récupération des données JSON envoyées depuis le frontend
    $nomEquipeAdverse = $data->Nom_Equipe_Adverse ?? '';
    $dateRencontre    = $data->Date_Rencontre     ?? '';
    $heure            = $data->Heure              ?? '';
    $lieu             = $data->Lieu               ?? '';
    $scoreNous        = isset($data->Score_Nous) && $data->Score_Nous !== null ? $data->Score_Nous : '';
    $scoreAdverse     = isset($data->Score_Adversaire) && $data->Score_Adversaire !== null ? $data->Score_Adversaire : '';

    // 🔹 Vérification des champs obligatoires
    if (empty($nomEquipeAdverse) || empty($dateRencontre) || empty($heure)) {
        $error = 'Les champs avec * sont obligatoires';
    } else {

        // 🔹 Validation du format de la date (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateRencontre)) {
            $error = 'Date invalide (format attendu : YYYY-MM-DD)';
        }

        // 🔹 Validation du format de l'heure (HH:MM 24h)
        if (!$error && !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $heure)) {
            $error = 'Heure invalide (format 24h HH:MM)';
        }

        // 🔹 Validation des scores (doivent être des nombres positifs)
        if (!$error && $scoreNous !== '' && (!is_numeric($scoreNous) || $scoreNous < 0)) {
            $error = 'Score "nous" invalide';
        }

        if (!$error && $scoreAdverse !== '' && (!is_numeric($scoreAdverse) || $scoreAdverse < 0)) {
            $error = 'Score "adverse" invalide';
        }

        // 🔹 Ajout du match si toutes les validations sont correctes
        if (!$error) {
            try {
                $scoreNousInt    = ($scoreNous    !== '') ? (int)$scoreNous    : null;
                $scoreAdverseInt = ($scoreAdverse !== '') ? (int)$scoreAdverse : null;

                // 🔹 Calcul du résultat (ex: 2-1)
                $resultat = '';
                if ($scoreNous !== '' && $scoreAdverse !== '') {
                    $resultat = $scoreNousInt . '-' . $scoreAdverseInt;
                }

                // 🔹 Création de l'objet Match
                $matchObj = new Match_(
                    0,                  // ID automatique
                    $dateRencontre,
                    $heure,
                    $nomEquipeAdverse,
                    $lieu,
                    $resultat,
                    $scoreAdverseInt,
                    $scoreNousInt
                );

                // 🔹 Ajout en base de données
                $matchDao->add($matchObj);
                $success = 'Match ajouté avec succès !';

            } catch (Exception $e) {
                $error = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
            }
        }
    }
}
?>