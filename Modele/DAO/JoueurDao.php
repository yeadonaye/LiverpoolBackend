<?php
require_once 'ModeleDao.php';
require_once __DIR__ . '/../Joueur.php';

// Classe DAO contenant toutes la logique de la table Joueur
class JoueurDao implements ModeleDao {

    private PDO $pdo;

    // Contructeur principale
    public function __construct(PDO $pdo){
        $this->pdo = $pdo;
    }

    // Récupère tous les joueurs de la bd
    public function getAll(): array{
        $sql = "SELECT * FROM Joueur ORDER BY Nom";
        $stmt = $this->pdo->query($sql);

        $joueurs = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $joueurs[] = new Joueur(
                $row['Id_Joueur'],
                $row['Num_Licence'],
                $row['Nom'],
                $row['Prenom'],
                $row['Date_Naissance'],
                $row['Taille'],
                $row['Poids'],
                $row['Statut']
            );
        }
        return $joueurs;
    }

    // Récupère un joueur par son id
    public function getById(int $id): ?Joueur{  
        $sql = "SELECT * FROM Joueur where Id_Joueur = :id ";
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row =$stmt->fetch(PDO::FETCH_ASSOC);

        if(!$row) return null;

        return new Joueur(
            $row['Id_Joueur'],
                $row['Num_Licence'],
                $row['Nom'],
                $row['Prenom'],
                $row['Date_Naissance'],
                $row['Taille'],
                $row['Poids'],
                $row['Statut']
        );
    }

    // Ajoute un joueur à la liste
    public function add(object $obj): bool{
        if (!($obj instanceof Joueur)) return false;
    
        $sql = "INSERT INTO Joueur
                (Num_Licence, Nom, Prenom, Date_Naissance, Taille, Poids, Statut)
                VALUES (:numLicence, :nom, :prenom, :dateNaissance, :taille, :poids, :statut)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':numLicence' => $obj->getNumLicence(),
            ':nom'  => $obj->getNom(),
            ':prenom' => $obj->getPrenom(),
            ':dateNaissance' => $obj->getDateNaissance(),
            ':taille' => $obj->getTaille(),
            ':poids' => $obj->getPoids(),
            ':statut' => $obj->getStatut()
        ]);
    }

    // met à jour un joueur parmi la liste
    public function update(object $obj): bool{
        if(!$obj instanceof Joueur) return false;

        $sql = "UPDATE Joueur SET 
                    Num_Licence = :numLicence,
                    Nom = :nom,
                    Prenom = :prenom,
                    Date_Naissance = :dateNaissance,
                    Taille = :taille,
                    Poids = :poids,
                    Statut = :statut
                WHERE Id_Joueur = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':numLicence' => $obj->getNumLicence(),
            ':nom'        => $obj->getNom(),
            ':prenom'     => $obj->getPrenom(),
            ':dateNaissance' => $obj->getDateNaissance(),
            ':taille'     => $obj->getTaille(),
            ':poids'      => $obj->getPoids(),
            ':statut'     => $obj->getStatut(),
            ':id'         => $obj->getIdJoueur()
        ]);
        
    }

    // supprime un joueur parmi la liste
    public function delete(object $obj): bool{
        if(!$obj instanceof Joueur) return false;
        $sql = "DELETE FROM Joueur WHERE Id_Joueur = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([':id' => $obj->getIdJoueur()]);
    }

    // Recupere un joueur par son numéro de licence
    public function getByNumLicence(string $numLicence): ?Joueur { // ?Joueur pour indiquer aue l'objet peut etre null
        $sql = "SELECT * FROM Joueur WHERE Num_Licence = :numLicence";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':numLicence', $numLicence);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) return null;
        
        return new Joueur(
            $row['Id_Joueur'],
            $row['Num_Licence'],
            $row['Nom'],
            $row['Prenom'],
            $row['Date_Naissance'],
            $row['Taille'],
            $row['Poids'],
            $row['Statut']
        );
    }

    // Méthodes supplémentaires

    /**
      * Récupère tous les joueurs actifs (non blessés / non suspendus / non absents)
     */
    public function getActifs(): array {
          $sql = "SELECT * FROM Joueur WHERE Statut NOT IN ('Blessé', 'Suspendue', 'Absent') ORDER BY Nom, Prenom";
        $stmt = $this->pdo->query($sql);
        
        $joueurs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $joueurs[] = new Joueur(
                $row['Id_Joueur'],
                $row['Num_Licence'],
                $row['Nom'],
                $row['Prenom'],
                $row['Date_Naissance'],
                $row['Taille'],
                $row['Poids'],
                $row['Statut']
            );
        }
        return $joueurs;
    }

    /**
     * Récupère le nombre total de joueurs
     */
    public function compterTotalJoueurs(): int {
        $sql = "SELECT COUNT(*) as count FROM Joueur";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Récupère tous les joueurs avec leurs stats
     */
    public function getTousAvecStatistiques(): array {
        // Poste préféré: poste le plus fréquent dans Participer pour ce joueur
        $sql = "
            SELECT 
                j.Id_Joueur,
                j.Num_Licence,
                j.Nom,
                j.Prenom,
                j.Statut,
                (
                    SELECT p.Poste 
                    FROM Participer p 
                    WHERE p.Id_Joueur = j.Id_Joueur 
                    GROUP BY p.Poste 
                    ORDER BY COUNT(*) DESC 
                    LIMIT 1
                ) AS Poste
            FROM Joueur j
            ORDER BY j.Nom, j.Prenom
        ";
        $stmt = $this->pdo->query($sql);
        
        $joueurs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $joueurs[] = $row;
        }
        return $joueurs;
    }

    /**
     * Récupère le nombre de titularisations pour un joueur
     */
    public function compterTitularisations(int $joueurId): int {
        $sql = "SELECT COUNT(*) FROM Participer p JOIN `Match_` m ON p.Id_Match = m.Id_Match 
                WHERE p.Id_Joueur = :id AND p.Titulaire_ou_pas = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $joueurId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Récupère le nombre de remplaçages pour un joueur
     */
    public function compterRemplacements(int $joueurId): int {
        $sql = "SELECT COUNT(*) FROM Participer p JOIN `Match_` m ON p.Id_Match = m.Id_Match 
                WHERE p.Id_Joueur = :id AND p.Titulaire_ou_pas = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $joueurId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Récupère la note moyenne pour un joueur
     */
    public function obtenirNoteMoyenne(int $joueurId): ?float {
        $sql = "SELECT AVG(Note) as avgNote FROM Participer WHERE Id_Joueur = :id AND Note IS NOT NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $joueurId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $avg = $result['avgNote'] ?? null;
        return $avg !== null ? round($avg, 2) : null;
    }

    /**
     * Récupère le nombre de participations pour un joueur
     */
    public function compterParticipations(int $joueurId): int {
        $sql = "SELECT COUNT(DISTINCT p.Id_Match) FROM Participer p 
                JOIN `Match_` m ON p.Id_Match = m.Id_Match 
                WHERE p.Id_Joueur = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $joueurId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Récupère le pourcentage de victoires quand le joueur a participé
     */
    public function pourcentageVictoiresLorsParticipation(int $joueurId): ?float {
        $participations = $this->compterParticipations($joueurId);
        
        if ($participations === 0) return null;
        
        $sql = "SELECT COUNT(DISTINCT p.Id_Match) FROM Participer p 
                JOIN `Match_` m ON p.Id_Match = m.Id_Match 
                WHERE p.Id_Joueur = :id AND 
                (m.Score_Nous IS NOT NULL AND m.Score_Adversaire IS NOT NULL AND m.Score_Nous > m.Score_Adversaire)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $joueurId]);
        $wins = (int)$stmt->fetchColumn();
        
        return round(($wins / $participations) * 100, 1);
    }

    /**
     * Récupère le nombre de sélections consécutives pour un joueur
     */
    public function compterSelectionsConsecutives(int $joueurId, array $matchesOrdered): int {
        $consecutive = 0;
        $sql = "SELECT COUNT(*) FROM Participer WHERE Id_Match = :mid AND Id_Joueur = :id";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($matchesOrdered as $matchId) {
            $stmt->execute([':mid' => $matchId, ':id' => $joueurId]);
            $count = (int)$stmt->fetchColumn();
            if ($count > 0) {
                $consecutive++;
            } else {
                break;
            }
        }
        
        return $consecutive;
    }

}
?>