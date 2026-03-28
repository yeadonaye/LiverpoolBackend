<?php

require_once 'jwt_utils.php';
require_once '../Modele/DAO/MatchDao.php';

$secret = "secret_key"; // Clé secrète pour la validation du token
$headers = getallheaders();

//Récupération du token
$jwt = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;


$http_method = $_SERVER['REQUEST_METHOD'];

switch ($http_method){
    case 'GET': // GET pour afficher la liste des matchs
        check_auth($jwt, $secret); // Vérifie que le token est valide
        check_coach($jwt, $secret); // Vérifie que l'utilisateur est un coach


        require_once '../Controleur/afficher/afficher_match.php';
        if (!empty($error)){
            deliver_response(500, "Internal Server Error", "Erreur lors de la récupération des matchs.");
        }else{
            deliver_response(200, "OK", $matchs);
        }

        break;
    
    case 'POST': // POST pour ajouter un match
        check_auth($jwt, $secret); // Vérifie que le token est valide
        check_coach($jwt, $secret); // Vérifie que l'utilisateur est un coach


        $data = json_decode(file_get_contents("php://input"));

        // Vérifier que data n'est pas null
        if(!$data){
            deliver_response(400, "Bad Request", "JSON Invalide ou manquant.");
            exit();
        }


        require_once '../Controleur/ajouter/ajouter_match.php';

        if (!empty($error)) {
            deliver_response(400, "Bad Request", $error);
        } elseif (!empty($success)) {
            deliver_response(201, "Created", $success);
        } else {
            deliver_response(500, "Internal Server Error", "Erreur inconnue lors de l'ajout du match.");
        }
        
    break;


    case 'PUT': // PUT pour mettre à jour un match
        check_auth($jwt, $secret);
        check_coach($jwt, $secret);

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

        require_once '../modifier/modifier_match.php';

        if (!empty($error)) {
            deliver_response(400, "Bad Request", $error);
        } elseif (!empty($success)) {
            deliver_response(200, "OK", $success);
        } else {
            deliver_response(500, "Internal Server Error", "Erreur inconnue lors de la mise à jour du match.");
        }

    break;

    case 'DELETE': // DELETE pour supprimer un match
        check_auth($jwt, $secret);
        check_coach($jwt, $secret);

        $id = $_GET['id'] ?? null;
        if (!$id) {
            deliver_response(400, "Bad Request", "L'ID du match est requis pour la suppression.");
            exit();
        }

        try {
            $match = $matchDao->getById((int)$id);
            if (!$match) {
                deliver_response(404, "Not Found", "Match introuvable.");
                exit();
            }

            $matchDao->delete($match);

            deliver_response(200, "OK", "Match supprimé.");
        } catch (Exception $e) {
            deliver_response(500, "Internal Server Error", "Erreur lors de la suppression: " . $e->getMessage());
            exit();
        }
        break;

    default:
        deliver_response(405, "Method Not Allowed", "Méthode HTTP non autorisée.");
        exit();
}

?>