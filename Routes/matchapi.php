<?php

require_once 'jwt_utils.php';
require_once '../Modele/DAO/MatchDao.php';
require_once '../Modele/DAO/connexionBD.php';

// Récupère tous les headers HTTP pour extraire le token JWT
$headers = getallheaders();

// Récupération du JWT depuis le header Authorization
$jwt = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

// DAO pour accéder aux données des matchs
$matchDao = new MatchDao($linkpdo);

// Détermine la méthode HTTP utilisée
$http_method = $_SERVER['REQUEST_METHOD'];

switch ($http_method){
    case 'GET': // GET pour afficher la liste des matchs ou un match spécifique

        $id = $_GET['id'] ?? null;

        if ($id) { 
            /**
             * Récupération des détails d'un match spécifique pour pré-remplir le formulaire de modification
             * Accès réservé aux coachs
             */
            check_auth($jwt); // Vérifie que le token est valide
            check_coach($jwt); // Vérifie que l'utilisateur est un coach

            require_once '../Controleur/modifier/modifier_match.php';

            if (!empty($error)) {
                deliver_response(404, "Not Found", $error);
            } else {
                deliver_response(200, "OK", $success);
            }
        } else {
            /**
             * Liste de tous les matchs accessible publiquement (même sans authentification)
             */
            require_once '../Controleur/afficher/afficher_match.php';

            if (!empty($error)) {
                deliver_response(500, "Internal Server Error", "Erreur lors de la récupération des matchs.");
            } else {
                deliver_response(200, "OK", $matchs);
            }
        }

        break;
    
    case 'POST': // POST pour ajouter un match
        check_auth($jwt); // Vérifie que le token est valide
        check_coach($jwt); // Vérifie que l'utilisateur est un coach

        $data = json_decode(file_get_contents("php://input")); // Lecture des données JSON

        if(!$data){
            deliver_response(400, "Bad Request", "JSON Invalide ou manquant.");
            exit();
        }

        require_once '../Controleur/ajouter/ajouter_match.php';

        // Réponse HTTP selon le résultat de l'ajout
        if (!empty($error)) {
            deliver_response(400, "Bad Request", $error);
        } elseif (!empty($success)) {
            deliver_response(201, "Created", $success);
        } else {
            deliver_response(500, "Internal Server Error", "Erreur inconnue lors de l'ajout du match.");
        }
        break;

    case 'PUT': // PUT pour mettre à jour un match
        check_auth($jwt);
        check_coach($jwt);

        $id = $_GET['id'] ?? null;
        if (!$id) {
            deliver_response(400, "Bad Request", "L'ID du match est requis pour la mise à jour.");
            exit();
        }

        $data = json_decode(file_get_contents("php://input"));
        if (!$data) {
            deliver_response(400, "Bad Request", "JSON invalide ou manquant.");
            exit();
        }

        require_once '../Controleur/modifier/modifier_match.php';

        // Réponse HTTP selon le résultat de la mise à jour
        if (!empty($error)) {
            deliver_response(400, "Bad Request", $error);
        } elseif (!empty($success)) {
            deliver_response(200, "OK", $success);
        } else {
            deliver_response(500, "Internal Server Error", "Erreur inconnue lors de la mise à jour du match.");
        }
        break;

    case 'DELETE': // DELETE pour supprimer un match
        check_auth($jwt);
        check_coach($jwt);

        $id = $_GET['id'] ?? null;
        if (!$id) {
            deliver_response(400, "Bad Request", "L'ID du match est requis pour la suppression.");
            exit();
        }

        try {
            // Vérifie que le match existe avant suppression
            $match = $matchDao->getById((int)$id);
            if (!$match) {
                deliver_response(404, "Not Found", "Match introuvable.");
                exit();
            }

            // Suppression du match
            $matchDao->delete($match);

            deliver_response(200, "OK", "Match supprimé.");
        } catch (Exception $e) {
            // Gestion des erreurs lors de la suppression
            deliver_response(500, "Internal Server Error", "Erreur lors de la suppression: " . $e->getMessage());
            exit();
        }
        break;

    default:
        // Méthode HTTP non supportée
        deliver_response(405, "Method Not Allowed", "Méthode HTTP non autorisée.");
        exit();
}

?>