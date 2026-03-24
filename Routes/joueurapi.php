<?php

require_once 'connexionDB.php';
require_once 'jwt_utils.php';

$secret = "secret_key"; // Clé secrète pour la validation du token
$headers = getallheaders();

//Récupération du token
$jwt = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

//Verification auth
function check_auth($jwt, $secret) {
    if (!is_jwt_valid($jwt, $secret)) {
        deliver_response(401, "Unauthorized", "Votre token n'est pas valide ou a expiré.");
        exit();
    }
}

// Vérification rôle coach
function check_coach($jwt, $secret) {
    if (!is_coach($jwt, $secret)) {
        deliver_response(403, "Forbidden", "Vous n'avez pas les permissions nécessaires pour accéder à cette ressource.");
        exit();
    }
}

$http_method = $_SERVER['REQUEST_METHOD'];

switch ($http_method){
    case 'GET': // GET pour afficher la liste des joueurs
        check_auth($jwt, $secret); // Vérifie que le token est valide
        check_coach($jwt, $secret); // Vérifie que l'utilisateur est un coach

        try{
            $sql = "SELECT * FROM Joueur";
            $stmt = $linkpdo->prepare($sql);
            $stmt->execute();
            $joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            deliver_response(200, "OK", $joueurs);
        } catch (PDOException $e) {
            deliver_response(500, "Internal Server Error", "Erreur lors de la récupération des joueurs.");
        }
        break;
    
    case 'POST': // POST pour ajouter un joueur
        check_auth($jwt, $secret); // Vérifie que le token est valide
        check_coach($jwt, $secret); // Vérifie que l'utilisateur est un coach

        try {
            $data = json_decode(file_get_contents("php://input"));

            // Vérifier que data n'est pas null
            if(!$data){
                deliver_response(400, "Bad Request", "JSON Invalide ou manquant.");
                exit();
            }

            if (!empty($data->nom) && !empty($data->prenom) && !empty($data->numLicence) && !empty($data->statut)) {
                $sql = "INSERT INTO Joueur
                (Num_Licence, Nom, Prenom, Date_Naissance, Taille, Poids, Statut)
                VALUES (:numLicence, :nom, :prenom, :dateNaissance, :taille, :poids, :statut)";

                $stmt = $linkpdo->prepare($sql);

                $stmt->execute([
                    'numLicence'     => $data->numLicence,
                    'nom'            => $data->nom,
                    'prenom'         => $data->prenom,
                    'statut'         => $data->statut,
                    'dateNaissance'  => $data->dateNaissance ?? null,
                    'taille'         => $data->taille ?? null,
                    'poids'          => $data->poids ?? null
                ]);

                deliver_response(201, "Created", "Joueur ajouté avec succès.");
            } else {
                deliver_response(400, "Bad Request", "Les champs numéro de licence, nom, prenom et statut sont obligatoires.");
            }
        } catch (PDOException $e) {
            deliver_response(500, "Internal Server Error", "Erreur lors de l'ajout du joueur.");
            exit();
        }
        break;

    case 'PUT': // PUT pour mettre à jour les informations d'un joueur
        check_auth($jwt, $secret); // Vérifie que le token est valide
        check_coach($jwt, $secret); // Vérifie que l'utilisateur est un coach

        $id = $_GET['id'] ?? null; // Récupérer l'ID du joueur à mettre à jour depuis les paramètres de la requête
        
        if (!$id){
            deliver_response(400, "Bad Request", "L'ID du joueur est requis pour la mise à jour.");
            exit();
        }

        $data = json_decode(file_get_contents("php://input"));

        // Vérifier que data n'est pas null
        if(!$data){
            deliver_response(400, "Bad Request", "JSON Invalide ou manquant.");
            exit();
        }

        if(!empty($data->nom) && !empty($data->prenom) && !empty($data->numLicence) && !empty($data->statut)){
            try{
                $sql = "UPDATE Joueur SET 
                        Num_Licence = :numLicence,
                        Nom = :nom,
                        Prenom = :prenom,
                        Date_Naissance = :dateNaissance,
                        Taille = :taille,
                        Poids = :poids,
                        Statut = :statut
                    WHERE Id_Joueur = :id";
                $stmt = $linkpdo->prepare($sql);

                $stmt->execute([
                    'numLicence' => $data->numLicence,
                    'nom'        => $data->nom,
                    'prenom'     => $data->prenom,
                    'dateNaissance' => $data->dateNaissance ?? null,
                    'taille'     => $data->taille ?? null,
                    'poids'      => $data->poids ?? null,
                    'statut'     => $data->statut,
                    'id'         => $id
                ]);

                deliver_response(200, "OK", "Joueur mis à jour avec succès.");
            } catch (PDOException $e){
                deliver_response(500, "Internal Server Error", "Erreur lors de la modification du joueur.");
            }

        } else {        
            deliver_response(400, "Bad Request", "Champs obligatoires manquants : numéro de licence, nom, prénom, et statut.");
        }   

        break;

    case 'DELETE': // DELETE pour supprimer un joueur
        check_auth($jwt, $secret); // Vérifie que le token est valide
        check_coach($jwt, $secret); // Vérifie que l'utilisateur est un coach

        $id = $_GET['id'] ?? null; // Récupérer l'ID du joueur à supprimer depuis les paramètres de la requête
        
        if (!$id){
            deliver_response(400, "Bad Request", "L'ID du joueur est requis pour la suppression.");
            exit();
        }

        try {
            $sql = "DELETE FROM Joueur WHERE Id_Joueur = :id";
            $stmt = $linkpdo->prepare($sql);

            $stmt->execute(['id' => $id]);

            deliver_response(200, "OK", "Joueur supprimé.");
        } catch (PDOException $e) {
            deliver_response(500, "Internal Server Error", "Erreur lors de la suppression du joueur.");
            exit();
        }
        break;

    default: // DEFAULT pour les méthodes non définies / non supportées
        deliver_response(405, "Method Not Allowed", "Méthode non supportée.");
        break;
}

?>