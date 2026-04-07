<?php
// auth.php se trouve dans Modele/DAO

// Inclusion du fichier contenant la logique d'authentification et génération de JWT
require_once __DIR__ . '/../Modele/DAO/authapi.php';

// Appel de la fonction de connexion qui traite la requête POST et retourne une erreur éventuelle
$error = seConnecter();

?>