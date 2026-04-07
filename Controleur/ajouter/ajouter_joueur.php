<?php

// Inclusion des fichiers DAO et de la classe Joueur
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/Joueur.php';
require_once __DIR__ . '/../../Modele/DAO/connexionBD.php';

// Initialisation du DAO
$joueurDao = new JoueurDao($linkpdo);
$error = '';
$success = '';
$statuts = ['Actif', 'Blessé', 'Suspendu', 'Absent'];

// 🔹 Récupération des données JSON envoyées depuis le frontend
$numLicence    = $data->Num_Licence    ?? '';
$nom           = $data->Nom           ?? '';
$prenom        = $data->Prenom        ?? '';
$dateNaissance = $data->Date_Naissance ?? '';
$taille        = $data->Taille        ?? '';
$poids         = $data->Poids         ?? '';
$statut        = $data->Statut        ?? '';

// 🔹 Validation des champs obligatoires
if (empty($numLicence) || empty($nom) || empty($prenom) || empty($statut)) {
    $error = 'Le numéro de licence, le nom, le prénom et le statut sont obligatoires';
} else {

    // 🔹 Conversion des virgules en points pour les nombres
    if (isset($taille)) {
        $taille = str_replace(',', '.', $taille);
    }
    if (isset($poids)) {
        $poids = str_replace(',', '.', $poids);
    }

    // 🔹 Vérification de la taille
    if ($taille !== '' && (!is_numeric($taille) || (float)$taille <= 0 || (float)$taille > 3)) {
        $error = 'La taille doit être un nombre entre 0 et 3 mètres.';
    }

    // 🔹 Vérification du poids
    if (!$error && $poids !== '' && (!is_numeric($poids) || (float)$poids <= 0)) {
        $error = 'Le poids doit être un nombre positif.';
    }

    // 🔹 Vérification du statut
    if (!$error && !in_array($statut, $statuts)) {
        $error = 'Le statut sélectionné est invalide.';
    }

    // 🔹 Vérification que le numéro de licence contient uniquement des chiffres
    if (!$error && !preg_match('/^\d+$/', $numLicence)) {
        $error = 'Le numéro de licence doit contenir uniquement des chiffres.';
    }

    // 🔹 Vérification de la longueur du numéro de licence
    if (!$error && strlen($numLicence) > 4) {
        $error = 'Le numéro de licence doit contenir au maximum 4 caractères.';
    }

    // 🔹 Vérification de l'unicité du numéro de licence
    if (!$error) {
        try {
            $existing = $joueurDao->getByNumLicence($numLicence);
            if ($existing) {
                $error = 'Ce numéro de licence est déjà utilisé par un autre joueur.';
            }
        } catch (Exception $e) {
            $error = 'Erreur lors de la vérification des données : ' . $e->getMessage();
        }
    }

    // 🔹 Ajout du joueur si aucune erreur
    if (!$error) {
        try {
            $joueurObj = new Joueur(
                0, // ID automatique
                $numLicence,
                $nom,
                $prenom,
                $dateNaissance,
                !empty($taille) ? (float)$taille : 0,
                !empty($poids) ? (int)$poids : 0,
                $statut
            );

            $joueurDao->add($joueurObj);

            $success = 'Joueur ajouté avec succès !';

        } catch (Exception $e) {
            $error = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
        }
    }
}
?>