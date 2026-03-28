<?php

require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/Joueur.php';
require_once __DIR__ . '/../../Modele/DAO/connexionBD.php';

$joueurDao = new JoueurDao($linkpdo);
$error = '';
$success = '';

$statuts = ['Actif', 'Blessé', 'Suspendue', 'Absent'];

// 🔹 Récupération des données JSON
$numLicence = $data->numLicence ?? '';
$nom = $data->nom ?? '';
$prenom = $data->prenom ?? '';
$dateNaissance = $data->dateNaissance ?? '';
$taille = $data->taille ?? '';
$poids = $data->poids ?? '';
$statut = $data->statut ?? '';

// 🔹 Validation
if (empty($numLicence) || empty($nom) || empty($prenom) || empty($statut)) {
    $error = 'Le numéro de licence, le nom, le prénom et le statut sont obligatoires';
} else {

    if ($taille !== '' && (!is_numeric($taille) || (float)$taille <= 0 || (float)$taille > 3)) {
        $error = 'La taille doit être un nombre entre 0 et 3 mètres.';
    }

    if (!$error && $poids !== '' && (!is_numeric($poids) || (float)$poids <= 0)) {
        $error = 'Le poids doit être un nombre positif.';
    }

    if (!$error && !in_array($statut, $statuts)) {
        $error = 'Le statut sélectionné est invalide.';
    }

    if (!$error && !preg_match('/^[0-9A-Za-z\-]+$/', $numLicence)) {
        $error = 'Le numéro de licence doit contenir uniquement des chiffres, lettres et tirets.';
    }

    // 🔹 Vérification unicité licence
    if (!$error) {
        try {
            $existing = $joueurDao->getByNumLicence($numLicence);
            if ($existing) {
                $error = 'Ce numéro de licence est déjà utilisé par un autre joueur.';
            }
        } catch (Exception $e) {
            $error = 'Erreur lors de la vérification des données: ' . $e->getMessage();
        }
    }

    // 🔹 Ajout
    if (!$error) {
        try {

            $joueurObj = new Joueur(
                0, // auto ID
                (int)$numLicence,
                $nom,
                $prenom,
                $dateNaissance,
                !empty($taille) ? (float)$taille : 0,
                !empty($poids) ? (int)$poids : 0,
                $statut
            );

            $joueurDao->add($joueurObj);

            $success = 'Joueur ajouté avec succès!';

        } catch (Exception $e) {
            $error = 'Erreur lors de l\'enregistrement: ' . $e->getMessage();
        }
    }
}
?>