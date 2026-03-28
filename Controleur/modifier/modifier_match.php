<?php

require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';
require_once __DIR__ . '/../../Modele/Match.php';
require_once __DIR__ . "/../../Modele/DAO/connexionBD.php";

$matchDao = new MatchDao($linkpdo);
$error = '';
$success = '';

if (!$id) {
    $error = 'Aucun match spécifié';
} else {
    try {
        $matchObj = $matchDao->getById((int)$id);
        if (!$matchObj) {
            $error = 'Match non trouvé';
        } else {
            $nomEquipeAdverse = $data->Nom_Equipe_Adverse ?? '';
            $dateRencontre    = $data->Date_Rencontre ?? '';
            $heure            = $data->Heure ?? '';
            $lieu             = $data->Lieu ?? '';
            $scoreNous        = $data->Score_Nous ?? '';
            $scoreAdverse     = $data->Score_Adversaire ?? '';

            if (empty($nomEquipeAdverse) || empty($dateRencontre) || empty($heure)) {
                $error = 'Les champs avec * sont obligatoires';
            } else {
                $matchObj = new Match_(
                    (int)$id,
                    $dateRencontre,
                    $heure,
                    $nomEquipeAdverse,
                    $lieu,
                    $resultat,
                    $scoreAdverse,
                    $scoreNous
                );
                $matchDao->update($matchObj);
                $success = 'Match modifié avec succès!';
            }
        }
    } catch (Exception $e) {
        $error = 'Erreur lors de l\'enregistrement: ' . $e->getMessage();
    }
}
?>