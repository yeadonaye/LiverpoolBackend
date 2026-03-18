<?php
require_once __DIR__ . '/../../Modele/DAO/auth.php';
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/Joueur.php';
requireAuth();

$pdo = getDBConnection();
$joueurDao = new JoueurDao($pdo);
$joueur = [];
$statuts = ['Actif', 'Blessé', 'Suspendue', 'Absent'];
$error = '';
$success = '';

$id = $_GET['id'] ?? null;

if (!$id) {
    $error = 'Aucun joueur spécifié';
} else {
    try {
        $joueurObj = $joueurDao->getById((int)$id);
        if ($joueurObj) {
            // Convert object to array for template
            $joueur = [
                'Id_Joueur' => $joueurObj->getIdJoueur(),
                'Num_Licence' => $joueurObj->getNumLicence(),
                'Nom' => $joueurObj->getNom(),
                'Prenom' => $joueurObj->getPrenom(),
                'Date_Naissance' => $joueurObj->getDateNaissance(),
                'Taille' => $joueurObj->getTaille(),
                'Poids' => $joueurObj->getPoids(),
                'Statut' => $joueurObj->getStatut()
            ];
        } else {
            $error = 'Joueur non trouvé';
        }
    } catch (Exception $e) {
        $error = 'Erreur lors du chargement du joueur';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numLicence = $_POST['numLicence'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $dateNaissance = $_POST['dateNaissance'] ?? '';
    $taille = $_POST['taille'] ?? '';
    $poids = $_POST['poids'] ?? '';
    $statut = $_POST['statut'] ?? '';

    // Basic required fields
    if (empty($numLicence) || empty($nom) || empty($prenom) || empty($statut)) {
        $error = 'Le numéro de licence, le nom, le prénom et le statut sont obligatoires';
    } else {
        // Validate taille and poids if provided
        if (!$error && $taille !== '') {
            if (!is_numeric($taille) || (float)$taille <= 0 || (float)$taille > 3) {
                $error = 'La taille doit être un nombre entre 0 et 3 mètres.';
            }
        }

        if (!$error && $poids !== '') {
            if (!is_numeric($poids) || (float)$poids <= 0) {
                $error = 'Le poids doit être un nombre positif.';
            }
        }

        // Check uniqueness constraints
        if (!$error) {
            try {
                // Check Num_Licence uniqueness
                $existing = $joueurDao->getByNumLicence($numLicence);
                if ($existing && $existing->getIdJoueur() != $id) {
                    $error = 'Ce numéro de licence est déjà utilisé par un autre joueur.';
                }
            } catch (Exception $e) {
                $error = 'Erreur lors de la vérification des données: ' . $e->getMessage();
            }
        }

        if (!$error) {
            try {
                // Convert values to proper types
                $taille_value = !empty($taille) ? (float)$taille : 0.0;
                $poids_value = !empty($poids) ? (int)$poids : 0;
                $dateNaissance_value = !empty($dateNaissance) ? $dateNaissance : '';
                
                // Modification
                $joueurObj = new Joueur(
                    (int)$id,
                    (int)$numLicence,
                    $nom,
                    $prenom,
                    $dateNaissance_value,
                    $taille_value,
                    $poids_value,
                    $statut
                );
                $joueurDao->update($joueurObj);
                header('Location: /Vue/Afficher/liste_joueurs.php?success=modified');
                exit;
            } catch (Exception $e) {
                $error = 'Erreur lors de l\'enregistrement: ' . $e->getMessage();
            }
        }
    }
}
?>
