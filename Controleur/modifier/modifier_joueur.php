<?php

require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/Joueur.php';
require_once __DIR__ . '/../../Modele/DAO/connexionBD.php';

$joueurDao = new JoueurDao($linkpdo);
$error   = '';
$success = '';
$statuts = ['Actif', 'Blessé', 'Suspendue', 'Absent'];

if (!$id) {
    $error = 'Aucun joueur spécifié';
} else {
    try {
        $joueurObj = $joueurDao->getById((int)$id);

        if (!$joueurObj) {
            $error = 'Joueur non trouvé';
        } else {

            // GET — retourner les infos du joueur pour pré-remplir le formulaire
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $success = [
                    'Id_Joueur'      => $joueurObj->getIdJoueur(),
                    'Num_Licence'    => $joueurObj->getNumLicence(),
                    'Nom'            => $joueurObj->getNom(),
                    'Prenom'         => $joueurObj->getPrenom(),
                    'Date_Naissance' => $joueurObj->getDateNaissance(),
                    'Taille'         => $joueurObj->getTaille(),
                    'Poids'          => $joueurObj->getPoids(),
                    'Statut'         => $joueurObj->getStatut(),
                ];

            // PUT — modifier le joueur
            } elseif (in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'])) {

                $numLicence    = $data->Num_Licence    ?? '';
                $nom           = $data->Nom            ?? '';
                $prenom        = $data->Prenom         ?? '';
                $dateNaissance = $data->Date_Naissance ?? '';
                $taille        = $data->Taille         ?? '';
                $poids         = $data->Poids          ?? '';
                $statut        = $data->Statut         ?? '';

                if (empty($numLicence) || empty($nom) || empty($prenom) || empty($statut)) {
                    $error = 'Le numéro de licence, le nom, le prénom et le statut sont obligatoires';
                } else {
                    if (isset($taille)) {
                        $taille = str_replace(',', '.', $taille);
                    }
                    if ($taille !== '' && (!is_numeric($taille) || (float)$taille <= 0 || (float)$taille > 3)) {
                        $error = 'La taille doit être un nombre entre 0 et 3 mètres.';
                    }
                    if (!$error && $poids !== '' && (!is_numeric($poids) || (float)$poids <= 0)) {
                        $error = 'Le poids doit être un nombre positif.';
                    }
                    if (!$error && !in_array($statut, $statuts)) {
                        $error = 'Le statut sélectionné est invalide.';
                    }
                    if (!$error) {
                        $existing = $joueurDao->getByNumLicence($numLicence);
                        if ($existing && $existing->getIdJoueur() != $id) {
                            $error = 'Ce numéro de licence est déjà utilisé par un autre joueur.';
                        }
                    }
                    if (!$error && strlen($numLicence) > 4) {
                        $error = 'Le numéro de licence doit contenir au maximum 4 caractères.';
                    }
                    if (!$error) {
                        $joueurObj = new Joueur(
                            (int)$id,
                            (int)$numLicence,
                            $nom,
                            $prenom,
                            $dateNaissance,
                            !empty($taille) ? (float)$taille : 0,
                            !empty($poids) ? (int)$poids : 0,
                            $statut
                        );
                        $joueurDao->update($joueurObj);
                        $success = 'Joueur modifié avec succès!';
                    }
                }
            }
        }
    } catch (Exception $e) {
        $error = 'Erreur lors de l\'enregistrement: ' . $e->getMessage();
    }
}
?>