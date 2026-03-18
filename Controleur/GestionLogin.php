<?php
// auth.php lives in Modele/DAO
require_once __DIR__ . '/../Modele/DAO/auth.php';

if (isAuthenticated()) {
    header('Location: /index.php');
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? '/index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = $_POST['identifiant'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (authenticate($identifiant, $password)) {
        header('Location: ' . $redirect);
        exit;
    } else {
        $error = 'Identifiant ou mot de passe incorrect!';
    }
}
?>