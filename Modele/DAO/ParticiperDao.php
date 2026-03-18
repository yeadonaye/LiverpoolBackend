<?php
require_once 'ModeleDao.php';
require_once __DIR__ . '/../Participer.php';

class ParticiperDao implements ModeleDao {

    private PDO $pdo;

    public function __construct(PDO $pdo){
        $this->pdo = $pdo;
    }

    public function getAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM Participer");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $participations = [];
        foreach ($rows as $r) {
            $participations[] = new Participer(
                $r["idParticipation"],
                $r["poste"],
                $r["note"],
                (bool)$r["titulaireOuPas"]
            );
        }

            return $participations;
    }

    public function getById(int $id): ?Participer{
        $stmt = $this->pdo->prepare("SELECT * FROM Participer WHERE idParticipation = :id");
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

            return new Participer(
            $row["idParticipation"],
            $row["poste"],
            $row["note"],
            (bool)$row["titulaireOuPas"]
        );
    }

    public function add(object $obj): bool {
        if (!($obj instanceof Participer)) return false;

        $sql = "INSERT INTO Participer (poste, note, titulaireOuPas)
                VALUES (:poste, :note, :titulaire)";

            return $this->pdo->prepare($sql)->execute([
            ':poste'     => $obj->getPoste(),
            ':note'      => $obj->getNote(),
            ':titulaire' => $obj->getTitulaireOuPas()
        ]);
    }

    public function update(object $obj): bool {
        if (!($obj instanceof Participer)) return false;

        $sql = "UPDATE Participer
                SET poste = :poste,
                    note = :note,
                    titulaireOuPas = :titulaire
                WHERE idParticipation = :id";

            return $this->pdo->prepare($sql)->execute([
            ':poste'     => $obj->getPoste(),
            ':note'      => $obj->getNote(),
            ':titulaire' => $obj->getTitulaireOuPas(),
            ':id'        => $obj->getIdParticipation()
        ]);
    }

    public function delete(object $obj): bool {
        if (!($obj instanceof Participer)) return false;
        $sql = "DELETE FROM Participer WHERE idParticipation = :id";
            return $this->pdo->prepare($sql)->execute([':id' => $obj->getIdParticipation()]);
    }

    // Méthodes supplémentaires pour saisie_feuille_match.php

    /**
     * Récupérer tous les participants pour un match
     */
    public function obtenirParMatch(int $matchId): array {
        $sql = "SELECT * FROM Participer WHERE Id_Match = :matchId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':matchId' => $matchId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Supprimer tous les participants pour un match
     */
    public function supprimerParMatch(int $matchId): bool {
        $sql = "DELETE FROM Participer WHERE Id_Match = :matchId";
        $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':matchId' => $matchId]);
    }

    /**
     * Ajouter un participant au match
     */
    public function ajouterParticipation(int $joueurId, int $matchId, string $poste, bool $titulaire, ?int $note = null): bool {
        $sql = "INSERT INTO Participer (Id_Joueur, Id_Match, Poste, Titulaire_ou_pas, Note)
                VALUES (:joueur, :match, :poste, :titulaire, :note)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':joueur' => $joueurId,
            ':match' => $matchId,
            ':poste' => $poste,
            ':titulaire' => $titulaire ? 1 : 0,
            ':note' => $note
        ]);
    }

    /**
     * Récupérer les commentaires pour les joueurs
     */
    public function obtenirCommentairesParMatch(int $matchId): array {
        $sql = "SELECT c.Id_Joueur, c.Description, c.Date_ 
                FROM Commentaire c 
                JOIN Participer p ON c.Id_Joueur = p.Id_Joueur 
                WHERE p.Id_Match = :matchId 
                ORDER BY c.Date_ DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':matchId' => $matchId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les notes des joueurs pour un match
     */
    public function obtenirNotesParMatch(int $matchId): array {
        $sql = "SELECT Id_Joueur, Note FROM Participer 
                WHERE Id_Match = :matchId AND Note IS NOT NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':matchId' => $matchId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
