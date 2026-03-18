<?php
require_once __DIR__ . '/../../Modele/DAO/auth.php';
require_once __DIR__ . '/../../Modele/DAO/JoueurDao.php';
require_once __DIR__ . '/../../Modele/Joueur.php';
requireAuth();

$pdo = getDBConnection();
$joueurDao = new JoueurDao($pdo);
$joueur = []; // Initialize as empty array for template compatibility
$statuts = ['Actif', 'Blessé', 'Suspendue', 'Absent'];
$error = '';
$success = '';

// Helpers de date
$toFrDate = static function (?string $date) {
    if (!$date) return '';
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d ? $d->format('d/m/Y') : $date;
};

$toDbDate = static function (?string $dateFr) {
    if (!$dateFr) return '';
    $d = DateTime::createFromFormat('d/m/Y', $dateFr);
    return $d ? $d->format('Y-m-d') : '';
};

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $joueurObj = $joueurDao->getById((int)$id);
        if ($joueurObj) {
            // Convert object to array for template
            $joueur = [
                'Id_Joueur' => $joueurObj->getIdJoueur(),
                'Num_Licence' => $joueurObj->getNumLicence(),
                'Nom' => $joueurObj->getNom(),
                'Prenom' => $joueurObj->getPrenom(),
                'Date_Naissance' => $toFrDate($joueurObj->getDateNaissance()),
                'Taille' => $joueurObj->getTaille(),
                'Poids' => $joueurObj->getPoids(),
                'Statut' => $joueurObj->getStatut()
            ];
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
        // Validate taille if provided
        if (!$error && $taille !== '') {
            if (!is_numeric($taille) || (float)$taille <= 0 || (float)$taille > 3) {
                $error = 'La taille doit être un nombre positif entre 0 et 3 mètres.';
            }
        }

        // Validate poids if provided
        if (!$error && $poids !== '') {
            if (!is_numeric($poids) || (float)$poids <= 0) {
                $error = 'Le poids doit être un nombre positif.';
            }
        }

        // Validate statut is in allowed values
        if (!$error && !in_array($statut, $statuts)) {
            $error = 'Le statut sélectionné est invalide.';
        }

        // Validate numLicence format
        if (!$error && !preg_match('/^[0-9A-Za-z\-]+$/', $numLicence)) {
            $error = 'Le numéro de licence doit contenir uniquement des chiffres, lettres et tirets.';
        }

        /*
        // Validate custom ID if provided for new players
        if (!$error && !$id && !empty($idJoueur)) {
            if (!is_numeric($idJoueur) || (int)$idJoueur <= 0) {
                $error = 'L\'ID du joueur doit être un nombre positif.';
            } else {
                // Check if ID already exists
                try {
                    $existingById = $joueurDao->getById((int)$idJoueur);
                    if ($existingById) {
                        $error = 'Cet ID est déjà utilisé par un autre joueur.';
                    }
                } catch (Exception $e) {
                    // ID doesn't exist, which is good
                }
            }
        }
        */

        // Check uniqueness constraints
        if (!$error) {
            try {
                // Check Num_Licence uniqueness
                $existing = $joueurDao->getByNumLicence($numLicence);
                if ($existing && (!$id || $existing->getIdJoueur() != $id)) {
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
                $dateNaissance_value = !empty($dateNaissance) ? $toDbDate($dateNaissance) : '';
                if ($dateNaissance !== '' && $dateNaissance_value === '') {
                    $error = 'Date de naissance invalide (format jj/mm/aaaa)';
                }
                
                if (!$error && $id) {
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
                    $success = 'Joueur modifié avec succès!';
                } elseif (!$error) {
                    // Ajout - utiliser l'ID personnalisé ou 0 pour auto-génération
                    $idToUse = !empty($idJoueur) ? (int)$idJoueur : 0;
                    $joueurObj = new Joueur(
                        $idToUse,
                        (int)$numLicence,
                        $nom,
                        $prenom,
                        $dateNaissance_value,
                        $taille_value,
                        $poids_value,
                        $statut
                    );
                    $joueurDao->add($joueurObj);
                    // Redirection automatique vers la liste des joueurs
                    header('Location: /Vue/Afficher/liste_joueurs.php');
                    exit;
                }
            } catch (Exception $e) {
                $error = 'Erreur lors de l\'enregistrement: ' . $e->getMessage();
            }
        }
    }
}
?>
