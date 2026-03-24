<?php

    require_once __DIR__ . "/../Match.php";
    require_once "ModeleDao.php";

    class MatchDao implements ModeleDao {

        private PDO $pdo;

        public function __construct(PDO $pdo) {
            $this->pdo= $pdo;
        }

        public function getAll(): array{
            $sql = "SELECT * FROM `Match_` ORDER BY Date_Rencontre DESC, Heure DESC";
            $stmt = $this->pdo->query($sql);
            
            $matchs = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $matchs[] = new Match_(
                    $row['Id_Match'],
                    $row['Date_Rencontre'],
                    $row['Heure'],
                    $row['Nom_Equipe_Adverse'],
                    $row['Lieu'],
                    $orw['Resultat'] ?? '',
                    $row['Score_Adversaire'] ?? '',
                    $row['Score_Nous'] ?? ''
                );
            }
            return $matchs;
        }

        public function getById(int $id): ?Match_{
            $sql = "SELECT * FROM `Match_` WHERE Id_Match = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) return null;
            
            return new Match_(
                $row['Id_Match'],
                $row['Date_Rencontre'],
                $row['Heure'],
                $row['Nom_Equipe_Adverse'],
                $row['Lieu'],
                $row['Resultat'] ?? '',
                $row['Score_Adversaire'] ?? '',
                $row['Score_Nous'] ?? ''
            );
        }

        public function add(object $obj): bool{
            if (!($obj instanceof Match_)) return false;
            
            $sql = "INSERT INTO `Match_` (Nom_Equipe_Adverse, Date_Rencontre, Heure, Lieu, Resultat, Score_Adversaire, Score_Nous) 
                    VALUES (:nom, :date, :heure, :lieu, :resultat, :score_adversaire, :score_nous)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nom' => $obj->getNomEquipeAdverse(),
                ':date' => $obj->getDateRencontre(),
                ':heure' => $obj->getHeure(),
                ':lieu' => $obj->getLieu(),
                ':resultat' => $obj->getResultat(),
                ':score_adversaire' => $obj->getScoreAdversaire(),
                ':score_nous' => $obj->getScoreNous()
            ]);
        }

        public function update(object $obj): bool{
            if (!($obj instanceof Match_)) return false;
            
            $sql = "UPDATE `Match_` 
                    SET Nom_Equipe_Adverse = :nom,
                        Date_Rencontre = :date,
                        Heure = :heure,
                        Lieu = :lieu,
                        Resultat = :resultat,
                        Score_Adversaire = :score_adversaire,
                        Score_Nous = :score_nous
                    WHERE Id_Match = :id";

            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                ':nom' => $obj->getNomEquipeAdverse(),
                ':date' => $obj->getDateRencontre(),
                ':heure' => $obj->getHeure(),
                ':lieu' => $obj->getLieu(),
                ':resultat' => $obj->getResultat(),
                ':score_adversaire' => $obj->getScoreAdversaire(),
                ':score_nous' => $obj->getScoreNous(),
                ':id' => $obj->getIdMatch()
            ]);
        }

        public function delete(object $obj): bool{
            if (!($obj instanceof Match_)) return false;
            $sql = "DELETE FROM `Match_` WHERE Id_Match = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $obj->getIdMatch()]);
        }

        public function getCompositionsByMatchId(int $matchId): array{
            $sql = "SELECT Titulaire_ou_pas FROM Participer WHERE Id_Match = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $matchId]);
            
            $participations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $titulaires = 0;
            $remplacants = 0;
            foreach ($participations as $p) {
                if ($p['Titulaire_ou_pas']) {
                    $titulaires++;
                } else {
                    $remplacants++;
                }
            }
            
            return ['titulaires' => $titulaires, 'remplacants' => $remplacants];
        }

        public function getGlobalStats(): array {
            $sql = "
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN Score_Nous > Score_Adversaire THEN 1 ELSE 0 END) as victoires,
                    SUM(CASE WHEN Score_Nous < Score_Adversaire THEN 1 ELSE 0 END) as defaites,
                    SUM(CASE WHEN Score_Nous = Score_Adversaire THEN 1 ELSE 0 END) as nuls,
                    SUM(Score_Nous) as buts,
                    SUM(Score_Adversaire) as butsEncaisses
                FROM `Match_`
                WHERE Score_Nous IS NOT NULL AND Score_Adversaire IS NOT NULL
            ";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?? [
                'total' => 0,
                'victoires' => 0,
                'defaites' => 0,
                'nuls' => 0,
                'buts' => 0,
                'butsEncaisses' => 0
            ];
        }

        public function getMatchesOrderedByDate(): array {
            $sql = "
                SELECT Id_Match FROM `Match_` 
                ORDER BY Date_Rencontre DESC, Heure DESC
            ";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
    }
?>