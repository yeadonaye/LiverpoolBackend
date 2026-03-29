<?php

require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';
require_once __DIR__ . '/../../Modele/Match.php';
require_once __DIR__ . "/../../Modele/DAO/connexionBD.php";

$matchDao = new MatchDao($linkpdo);
$error   = '';
$success = '';

if (!$id) {
    $error = 'Aucun match spécifié';
} else {
    try {
        $matchObj = $matchDao->getById((int)$id);

        if (!$matchObj) {
            $error = 'Match non trouvé';
        } else {

            // GET — retourner les infos du match pour pré-remplir le formulaire
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $success = [
                    'Id_Match'           => $matchObj->getIdMatch(),
                    'Nom_Equipe_Adverse' => $matchObj->getNomEquipeAdverse(),
                    'Date_Rencontre'     => $matchObj->getDateRencontre(),
                    'Heure'              => $matchObj->getHeure(),
                    'Lieu'               => $matchObj->getLieu(),
                    'Resultat'           => $matchObj->getResultat(),
                    'Score_Adversaire'   => $matchObj->getScoreAdversaire(),
                    'Score_Nous'         => $matchObj->getScoreNous(),
                ];

            // PUT — modifier le match
            } elseif (in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'])) {

                $nomEquipeAdverse = $data->Nom_Equipe_Adverse ?? '';
                $dateRencontre    = $data->Date_Rencontre     ?? '';
                $heure            = $data->Heure              ?? '';
                $lieu             = $data->Lieu               ?? '';
                $resultat         = $data->Resultat           ?? '';
                $scoreNous        = $data->Score_Nous         ?? '';
                $scoreAdverse     = $data->Score_Adversaire   ?? '';

                if (empty($nomEquipeAdverse) || empty($dateRencontre) || empty($heure)) {
                    $error = 'Les champs avec * sont obligatoires';
                } else {
                    try {
                        $scoreNousInt    = ($scoreNous    !== '') ? (int)$scoreNous    : 0;
                        $scoreAdverseInt = ($scoreAdverse !== '') ? (int)$scoreAdverse : 0;

                        $resultat = $data->Resultat ?? '';

                        $matchObj = new Match_(
                            (int)$id,
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

                    } catch (Exception $e) {
                        $error = 'Erreur lors de l\'enregistrement: ' . $e->getMessage();
                    }
                }
            }
        }
    } catch (Exception $e) {
        $error = 'Erreur lors du chargement: ' . $e->getMessage();
    }
}
?>