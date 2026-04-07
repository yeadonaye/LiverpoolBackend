<?php

// Inclusion des fichiers DAO et classe Joueur
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/Joueur.php';
require_once __DIR__ . '/../../Modele/DAO/connexionBD.php';

// Initialisation du DAO des joueurs
$joueurDao = new JoueurDao($linkpdo);
$error   = '';
$success = '';
$statuts = ['Actif', 'Blessé', 'Suspendue', 'Absent']; // Statuts autorisés

// Vérification de l'ID du joueur
if (!$id) {
    $error = 'Aucun joueur spécifié';
} else {
    try {
        // Récupération du joueur depuis la base
        $joueurObj = $joueurDao->getById((int)$id);

        if (!$joueurObj) {
            $error = 'Joueur non trouvé';
        } else {

            // GET — renvoyer les informations pour pré-remplir le formulaire
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

            // POST/PUT — modification du joueur
            } elseif (in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'])) {

                // Récupération des données envoyées
                $numLicence    = $data->Num_Licence    ?? '';
                $nom           = $data->Nom            ?? '';
                $prenom        = $data->Prenom         ?? '';
                $dateNaissance = $data->Date_Naissance ?? '';
                $taille        = $data->Taille         ?? '';
                $poids         = $data->Poids          ?? '';
                $statut        = $data->Statut         ?? '';

                // Vérification des champs obligatoires
                if (empty($numLicence) || empty($nom) || empty($prenom) || empty($statut)) {
                    $error = 'Le numéro de licence, le nom, le prénom et le statut sont obligatoires';
                } else {

                    // Remplacer la virgule par un point pour la taille
                    if (isset($taille)) {
                        $taille = str_replace(',', '.', $taille);
                    }

                    // Validation de la taille
                    if ($taille !== '' && (!is_numeric($taille) || (float)$taille <= 0 || (float)$taille > 3)) {
                        $error = 'La taille doit être un nombre entre 0 et 3 mètres.';
                    }

                    // Validation du poids
                    if (!$error && $poids !== '' && (!is_numeric($poids) || (float)$poids <= 0)) {
                        $error = 'Le poids doit être un nombre positif.';
                    }

                    // Vérification du statut
                    if (!$error && !in_array($statut, $statuts)) {
                        $error = 'Le statut sélectionné est invalide.';
                    }

                    // Vérification de l'unicité du numéro de licence
                    if (!$error) {
                        $existing = $joueurDao->getByNumLicence($numLicence);
                        if ($existing && $existing->getIdJoueur() != $id) {
                            $error = 'Ce numéro de licence est déjà utilisé par un autre joueur.';
                        }
                    }

                    // Vérification de la longueur du numéro de licence
                    if (!$error && strlen($numLicence) > 4) {
                        $error = 'Le numéro de licence doit contenir au maximum 4 caractères.';
                    }

                    // Si tout est OK, création de l'objet Joueur et mise à jour
                    if (!$error) {
                        $joueurObj = new Joueur(
                            (int)$id,
                            $numLicence,
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