<?php

require_once 'jwt_utils.php';
require_once '../Modele/DAO/ParticiperDao.php';
require_once '../Modele/DAO/connexionBD.php';

// Définition du type de réponse JSON
header('Content-Type: application/json');

// Récupération du JWT depuis le header Authorization
$headers = getallheaders();
$jwt = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

// Vérification de l'authentification et des droits coach
check_auth($jwt);
check_coach($jwt);

// DAO pour gérer les participations des joueurs aux matchs
$participerDao = new ParticiperDao($linkpdo);

// Détermination de la méthode HTTP utilisée
$http_method = $_SERVER['REQUEST_METHOD'];

switch ($http_method) {

    case 'GET':
        // Récupère les participations d'un match donné
        $matchId = $_GET['matchId'] ?? null;

        if (!$matchId) {
            deliver_response(400, "Bad Request", "matchId requis.");
            exit();
        }

        try {
            $participations = $participerDao->obtenirParMatch((int)$matchId);
            deliver_response(200, "OK", $participations);
        } catch (Exception $e) {
            deliver_response(500, "Internal Server Error", $e->getMessage());
        }
        break;

    case 'POST':
    case 'PUT': // Même logique pour création et mise à jour
        // Lecture des données JSON envoyées
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            deliver_response(400, "Bad Request", "JSON invalide.");
            exit();
        }

        $matchId = $data['matchId'] ?? null;
        $titulaires = $data['titulaires'] ?? [];
        $remplacants = $data['remplacants'] ?? [];

        // Validation minimale : matchId et au moins 11 titulaires
        if (!$matchId || count($titulaires) < 11) {
            deliver_response(400, "Bad Request", "matchId et au moins 11 titulaires requis.");
            exit();
        }

        try {
            // Réinitialisation des participations existantes pour ce match
            $participerDao->supprimerParMatch((int)$matchId);

            // Ajout des titulaires
            foreach ($titulaires as $joueur) {
                $participerDao->ajouterParticipation(
                    $joueur['id'],
                    $matchId,
                    $joueur['poste'],
                    true, // titulaire
                    $joueur['note'] ?? null
                );
            }

            // Ajout des remplaçants
            foreach ($remplacants as $joueur) {
                $participerDao->ajouterParticipation(
                    $joueur['id'],
                    $matchId,
                    $joueur['poste'],
                    false, // remplaçant
                    $joueur['note'] ?? null
                );
            }

            // Réponse HTTP selon la méthode
            deliver_response(
                $http_method === 'POST' ? 201 : 200,
                "OK",
                "Feuille de match sauvegardée."
            );

        } catch (Exception $e) {
            deliver_response(500, "Internal Server Error", $e->getMessage());
        }

        break;

    case 'DELETE':
        // Suppression d'une feuille de match
        $matchId = $_GET['matchId'] ?? null;

        if (!$matchId) {
            deliver_response(400, "Bad Request", "matchId requis.");
            exit();
        }

        try {
            $participerDao->supprimerParMatch((int)$matchId);
            deliver_response(200, "OK", "Feuille supprimée.");
        } catch (Exception $e) {
            deliver_response(500, "Internal Server Error", $e->getMessage());
        }

        break;

    default:
        // Méthode HTTP non supportée
        deliver_response(405, "Method Not Allowed", "Méthode non autorisée.");
}