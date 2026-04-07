<?php

// Inclusion des fichiers nécessaires pour accéder à la base et au DAO Joueur
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . "/../../Modele/DAO/connexionBD.php";

// Initialisation de la connexion PDO et du DAO
$pdo = $linkpdo;
$joueurDao = new JoueurDao($pdo);

// Initialisation des variables
$joueurs = [];
$error = '';

try {
    // Récupération de tous les joueurs depuis la base
    $joueursObjects = $joueurDao->getAll();

    // Conversion des objets Joueur en tableaux pour compatibilité avec le template
    foreach ($joueursObjects as $joueur) {
        $joueurs[] = [
            'Id_Joueur'      => $joueur->getIdJoueur(),
            'Num_Licence'    => $joueur->getNumLicence(),
            'Nom'            => $joueur->getNom(),
            'Prenom'         => $joueur->getPrenom(),
            'Date_Naissance' => $joueur->getDateNaissance(),
            'Taille'         => $joueur->getTaille(),
            'Poids'          => $joueur->getPoids(),
            'Statut'         => $joueur->getStatut()
        ];
    }

} catch (Exception $e) {
    // Gestion des erreurs en cas de problème lors de la récupération des joueurs
    $error = 'Erreur lors du chargement des joueurs : ' . $e->getMessage();
}
?>