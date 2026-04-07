<?php

require_once 'jwt_utils.php';
require_once '../Modele/DAO/JoueurDao.php';
require_once '../Modele/DAO/connexionBD.php';

// Récupère tous les headers HTTP pour extraire le token JWT
$headers = getallheaders();

// Récupération du JWT depuis le header Authorization
$jwt = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

// DAO pour accéder aux données des joueurs
$joueurDao = new JoueurDao($linkpdo);

// Détermine la méthode HTTP utilisée
$http_method = $_SERVER['REQUEST_METHOD'];

switch ($http_method){
    case 'GET': // GET pour afficher la liste des joueurs ou un joueur spécifique
        check_auth($jwt); // Vérifie que le token est valide
        check_coach($jwt); // Vérifie que l'utilisateur est un coach

        $id = $_GET['id'] ?? null;

        if ($id) {
            // Charger les informations d'un joueur spécifique
            require_once '../Controleur/modifier/modifier_joueur.php';
            if (!empty($error)) {
                deliver_response(404, "Not Found", $error);
            } else {
                deliver_response(200, "OK", $success);
            }
        } else {
            // Lister tous les joueurs
            require_once '../Controleur/afficher/afficher_joueur.php';
            if (!empty($error)) {
                deliver_response(500, "Internal Server Error", "Erreur lors de la récupération des joueurs.");
            } else {
                deliver_response(200, "OK", $joueurs);
            }
        }
        break;
    
    case 'POST': // POST pour ajouter un joueur
        check_auth($jwt); // Vérifie que le token est valide
        check_coach($jwt); // Vérifie que l'utilisateur est un coach

        $data = json_decode(file_get_contents("php://input")); // Lecture des données JSON

        if(!$data){
            // JSON invalide ou manquant
            deliver_response(400, "Bad Request", "JSON Invalide ou manquant.");
            exit();
        }

        require_once '../Controleur/ajouter/ajouter_joueur.php';

        // Gestion des réponses selon le résultat de l'ajout
        if (!empty($error)) {
            deliver_response(400, "Bad Request", $error);
        } elseif (!empty($success)) {
            deliver_response(201, "Created", $success);
        } else {
            deliver_response(500, "Internal Server Error", "Erreur inconnue lors de l'ajout du joueur.");
        }
        break;

    case 'PUT': // PUT pour mettre à jour un joueur
        check_auth($jwt);
        check_coach($jwt);

        $id = $_GET['id'] ?? null;
        if (!$id) {
            deliver_response(400, "Bad Request", "L'ID du joueur est requis pour la mise à jour.");
            exit();
        }

        $data = json_decode(file_get_contents("php://input"));
        if (!$data) {
            deliver_response(400, "Bad Request", "JSON invalide ou manquant.");
            exit();
        }

        require_once '../Controleur/modifier/modifier_joueur.php';

        // Gestion des réponses selon le résultat de la mise à jour
        if (!empty($error)) {
            deliver_response(400, "Bad Request", $error);
        } elseif (!empty($success)) {
            deliver_response(200, "OK", $success);
        } else {
            deliver_response(500, "Internal Server Error", "Erreur inconnue lors de la mise à jour du joueur.");
        }
        break;

    case 'DELETE': // DELETE pour supprimer un joueur
        check_auth($jwt);
        check_coach($jwt);

        $id = $_GET['id'] ?? null;
        if (!$id) {
            deliver_response(400, "Bad Request", "L'ID du joueur est requis pour la suppression.");
            exit();
        }

        try {
            // Vérifie que le joueur existe avant suppression
            $joueur = $joueurDao->getById((int)$id);
            if (!$joueur) {
                deliver_response(404, "Not Found", "Joueur introuvable.");
                exit();
            }

            // Suppression du joueur
            $joueurDao->delete($joueur);

            deliver_response(200, "OK", "Joueur supprimé.");
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