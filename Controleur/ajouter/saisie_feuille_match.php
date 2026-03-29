<?php

require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/DAO/ParticiperDao.php';
require_once __DIR__ . '/../../Modele/DAO/connexionBD.php';

$pdo = $linkpdo;

$matchDao = new MatchDao($pdo);
$joueurDao = new JoueurDao($pdo);
$participerDao = new ParticiperDao($pdo);

$error = '';
$success = '';

// ✅ DATA COMES FROM API (NOT $_POST)
if (!isset($data)) {
    $error = "Aucune donnée reçue.";
    return;
}

// 🔹 Récupération des données JSON
$matchId = $data['matchId'] ?? null;
$titulaires = $data['titulaires'] ?? [];
$remplacants = $data['remplacants'] ?? [];

// 🔹 Validation
if (!$matchId) {
    $error = "matchId est requis.";
    return;
}

if (!is_array($titulaires) || count($titulaires) < 11) {
    $error = "Vous devez fournir au moins 11 titulaires.";
    return;
}

try {
    // Vérifier que le match existe
    $match = $matchDao->getById((int)$matchId);
    if (!$match) {
        $error = "Match introuvable.";
        return;
    }

    // 🔥 Reset (logique PUT-like)
    $participerDao->supprimerParMatch((int)$matchId);

    // 🔹 Ajouter titulaires
    foreach ($titulaires as $joueur) {

        if (!isset($joueur['id'], $joueur['poste'])) {
            continue; // skip invalid entry
        }

        $participerDao->ajouterParticipation(
            (int)$joueur['id'],
            (int)$matchId,
            $joueur['poste'],
            true, // titulaire
            isset($joueur['note']) ? (int)$joueur['note'] : null
        );
    }

    // 🔹 Ajouter remplaçants
    foreach ($remplacants as $joueur) {

        if (!isset($joueur['id'], $joueur['poste'])) {
            continue;
        }

        $participerDao->ajouterParticipation(
            (int)$joueur['id'],
            (int)$matchId,
            $joueur['poste'],
            false, // remplaçant
            isset($joueur['note']) ? (int)$joueur['note'] : null
        );
    }

    $success = "Feuille de match enregistrée avec succès.";

} catch (Exception $e) {
    $error = "Erreur lors de la sauvegarde: " . $e->getMessage();
}