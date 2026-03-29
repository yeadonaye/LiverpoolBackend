<?php

require_once 'jwt_utils.php';
require_once '../Modele/DAO/ParticiperDao.php';
require_once '../Modele/DAO/connexionBD.php';

header('Content-Type: application/json');

$secret = "secret_key";
$headers = getallheaders();
$jwt = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

check_auth($jwt, $secret);
check_coach($jwt, $secret);

$participerDao = new ParticiperDao($linkpdo);

$http_method = $_SERVER['REQUEST_METHOD'];

switch ($http_method) {

    case 'GET':
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
    case 'PUT': // same logic for update

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            deliver_response(400, "Bad Request", "JSON invalide.");
            exit();
        }

        $matchId = $data['matchId'] ?? null;
        $titulaires = $data['titulaires'] ?? [];
        $remplacants = $data['remplacants'] ?? [];

        if (!$matchId || count($titulaires) < 11) {
            deliver_response(400, "Bad Request", "matchId et au moins 11 titulaires requis.");
            exit();
        }

        try {
            // reset (this is why PUT makes sense)
            $participerDao->supprimerParMatch((int)$matchId);

            // titulaires
            foreach ($titulaires as $joueur) {
                $participerDao->ajouterParticipation(
                    $joueur['id'],
                    $matchId,
                    $joueur['poste'],
                    true,
                    $joueur['note'] ?? null
                );
            }

            // remplacants
            foreach ($remplacants as $joueur) {
                $participerDao->ajouterParticipation(
                    $joueur['id'],
                    $matchId,
                    $joueur['poste'],
                    false,
                    $joueur['note'] ?? null
                );
            }

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
        deliver_response(405, "Method Not Allowed", "Méthode non autorisée.");
}