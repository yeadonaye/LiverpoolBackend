<?php
require_once __DIR__ . '/../../Modele/DAO/auth.php';
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
requireAuth();

$pdo = getDBConnection();
$joueurDao = new JoueurDao($pdo);
$joueurs = [];
$error = '';

try {
    $joueursObjects = $joueurDao->getAll();
    // Convert Joueur objects to arrays for template compatibility
    foreach ($joueursObjects as $joueur) {
        $joueurs[] = [
            'Id_Joueur' => $joueur->getIdJoueur(),
            'Num_Licence' => $joueur->getNumLicence(),
            'Nom' => $joueur->getNom(),
            'Prenom' => $joueur->getPrenom(),
            'Date_Naissance' => $joueur->getDateNaissance(),
            'Taille' => $joueur->getTaille(),
            'Poids' => $joueur->getPoids(),
            'Statut' => $joueur->getStatut()
        ];
    }
} catch (Exception $e) {
    $error = 'Erreur lors du chargement des joueurs: ' . $e->getMessage();
}
?>
