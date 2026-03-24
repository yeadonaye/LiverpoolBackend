<?php
// auth.php lives in Modele/DAO
require_once __DIR__ . '/../Modele/DAO/authapi.php';
/*
if (is_jwt_valid($jwt, $secret)) {
    header('Location: /index.php');
    exit;
}
*/

$error = seConnecter();


?>