<?php
require_once __DIR__ . '/../../Modele/DAO/auth.php';
require_once __DIR__ . '/../../Modele/DAO/MatchDao.php';
require_once __DIR__ . '/../../Modele/Match.php';
requireAuth();

$pdo = getDBConnection();
$matchDao = new MatchDao($pdo);
$match = [];
$error = '';
$success = '';

$id = $_GET['id'] ?? null;

if (!$id) {
    $error = 'Aucun match spécifié';
} else {
    try {
        $matchObj = $matchDao->getById((int)$id);
        if ($matchObj) {
            // Convert object to array for template
            $match = [
                'Id_Match' => $matchObj->getIdMatch(),
                'Date_Rencontre' => $matchObj->getDateRencontre(),
                'Heure' => $matchObj->getHeure(),
                'Nom_Equipe_Adverse' => $matchObj->getNomEquipeAdverse(),
                'Lieu' => $matchObj->getLieu(),
                'Score_Adversaire' => $matchObj->getScoreAdversaire(),
                'Score_Nous' => $matchObj->getScoreNous()
            ];
        } else {
            $error = 'Match non trouvé';
        }
    } catch (Exception $e) {
        $error = 'Erreur lors du chargement du match';
    }
}

// Show success message after redirect (Post-Redirect-Get)
if (isset($_GET['success']) && $_GET['success'] === 'modified') {
    $success = 'Match modifié avec succès!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomEquipeAdverse = $_POST['nomEquipeAdverse'] ?? '';
    $dateRencontre = $_POST['dateRencontre'] ?? '';
    $heure = $_POST['heure'] ?? '';
    $lieu = $_POST['lieu'] ?? '';
    $scoreNous = $_POST['scoreNous'] ?? '';
    $scoreAdverse = $_POST['scoreAdverse'] ?? '';

    if (empty($nomEquipeAdverse) || empty($dateRencontre) || empty($heure)) {
        $error = 'Les champs avec * sont obligatoires';
    } else {
        try {
            // Modification
            $matchObj = new Match_(
                $id,
                $dateRencontre,
                $heure,
                $nomEquipeAdverse,
                $lieu,
                $scoreAdverse,
                $scoreNous
            );
            $matchDao->update($matchObj);
            $success = 'Match modifié avec succès!';
            
            // Update $match array for display
            $match = [
                'Id_Match' => $id,
                'Date_Rencontre' => $dateRencontre,
                'Heure' => $heure,
                'Nom_Equipe_Adverse' => $nomEquipeAdverse,
                'Lieu' => $lieu,
                'Score_Adversaire' => $scoreAdverse,
                'Score_Nous' => $scoreNous
            ];
        } catch (Exception $e) {
            $error = 'Erreur lors de l\'enregistrement: ' . $e->getMessage();
        }
    }
}
?>
