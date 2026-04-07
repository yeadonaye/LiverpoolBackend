<?php

require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/DAO/ParticiperDao.php';
require_once __DIR__ . '/../../Modele/DAO/connexionBD.php';

// Initialisation des DAO avec la connexion PDO
$pdo = $linkpdo;
$matchDao = new MatchDao($pdo);
$joueurDao = new JoueurDao($pdo);
$participerDao = new ParticiperDao($pdo);

// Variables pour messages d'erreur ou de succès
$error = '';
$success = '';

// Vérification que des données JSON ont été reçues
if (!isset($data)) {
    $error = "Aucune donnée reçue.";
    return;
}

// Récupération des informations depuis le JSON
$matchId = $data['matchId'] ?? null;
$titulaires = $data['titulaires'] ?? [];
$remplacants = $data['remplacants'] ?? [];

// Validation minimale des données
if (!$matchId) {
    $error = "matchId est requis.";
    return;
}

if (!is_array($titulaires) || count($titulaires) < 11) {
    $error = "Vous devez fournir au moins 11 titulaires.";
    return;
}

try {
    // Vérifie que le match existe
    $match = $matchDao->getById((int)$matchId);
    if (!$match) {
        $error = "Match introuvable.";
        return;
    }

    // Réinitialisation des participations existantes pour ce match (logique PUT-like)
    $participerDao->supprimerParMatch((int)$matchId);

    // Ajout des joueurs titulaires
    foreach ($titulaires as $joueur) {
        if (!isset($joueur['id'], $joueur['poste'])) {
            continue; // ignore les entrées invalides
        }

        $participerDao->ajouterParticipation(
            (int)$joueur['id'],
            (int)$matchId,
            $joueur['poste'],
            true, // indique qu'il s'agit d'un titulaire
            isset($joueur['note']) ? (int)$joueur['note'] : null
        );
    }

    // Ajout des remplaçants
    foreach ($remplacants as $joueur) {
        if (!isset($joueur['id'], $joueur['poste'])) {
            continue;
        }

        $participerDao->ajouterParticipation(
            (int)$joueur['id'],
            (int)$matchId,
            $joueur['poste'],
            false, // indique qu'il s'agit d'un remplaçant
            isset($joueur['note']) ? (int)$joueur['note'] : null
        );
    }

    // Message de succès si tout s'est bien passé
    $success = "Feuille de match enregistrée avec succès.";

} catch (Exception $e) {
    // Gestion des exceptions : capture l'erreur pour affichage
    $error = "Erreur lors de la sauvegarde: " . $e->getMessage();
}