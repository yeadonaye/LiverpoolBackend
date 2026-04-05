<?php

require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/Joueur.php';
require_once __DIR__ . '/../../Modele/DAO/connexionBD.php';

$joueurDao = new JoueurDao($linkpdo);
$error = '';
$success = '';

// ✅ Statuts autorisés (exactement comme votre front-end)
$statuts = ['Actif', 'Blessé', 'Suspendu', 'Absent'];

// 🔹 Récupération des données JSON et nettoyage
$numLicence    = trim($data->Num_Licence ?? '');
$nom           = trim($data->Nom ?? '');
$prenom        = trim($data->Prenom ?? '');
$dateNaissance = trim($data->Date_Naissance ?? '');
$taille        = trim($data->Taille ?? '');
$poids         = trim($data->Poids ?? '');
$statut        = trim($data->Statut ?? '');

// 🔹 Validation champs obligatoires
if (empty($numLicence) || empty($nom) || empty($prenom) || empty($statut)) {
    $error = 'Le numéro de licence, le nom, le prénom et le statut sont obligatoires.';
}

// 🔹 Conversion des nombres français (1,8 → 1.8)
if (!$error && $taille !== '') {
    $taille = str_replace(',', '.', $taille);
    if (!is_numeric($taille) || (float)$taille <= 0 || (float)$taille > 3) {
        $error = 'La taille doit être un nombre entre 0 et 3 mètres.';
    }
}

if (!$error && $poids !== '') {
    $poids = str_replace(',', '.', $poids);
    if (!is_numeric($poids) || (float)$poids <= 0) {
        $error = 'Le poids doit être un nombre positif.';
    }
}

// 🔹 Validation du statut (UTF-8 safe)
if (!$error && !in_array($statut, $statuts, true)) {
    $error = 'Le statut sélectionné est invalide.';
}

// 🔹 Numéro de licence valide
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

// 🔹 Ajout du joueur
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

// 🔹 Réponse API
if ($error) {
    deliver_response(400, 'Bad Request', $error);
} else {
    deliver_response(201, 'Created', $success);
}

?>