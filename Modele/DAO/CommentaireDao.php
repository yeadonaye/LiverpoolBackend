<?php

require_once __DIR__ . "/../Commentaire.php";
require_once "ModeleDao.php";

class CommentaireDao implements ModeleDao {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(): array {
        $sql = 'SELECT * FROM Commentaire ORDER BY Id_Commentaire';
        $stmt = $this->pdo->query($sql);

        $commentaires = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $commentaires[] = new Commentaire(
                $row['Id_Commentaire'],
                $row['Description'],
                $row['Date_Commentaire'],
                $row['Id_Joueur']
            );
        }

        return $commentaires;
    }

    public function getById(int $id): ?Commentaire {
        $sql = "SELECT * FROM Commentaire WHERE Id_Commentaire = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        return new Commentaire(
            $row['Id_Commentaire'],
            $row['Description'],
            $row['Date_Commentaire'],
            $row['Id_Joueur']
        );
    }

    public function getByJoueur(int $id): array {
        $sql = "SELECT * FROM Commentaire WHERE Id_Joueur = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $commentaires = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $commentaires[] = new Commentaire(
                $row['Id_Commentaire'],
                $row['Description'],
                $row['Date_Commentaire'],
                $row['Id_Joueur']
            );
        }

        return $commentaires;
    }

    public function add(object $obj): bool {
        if (!$obj instanceof Commentaire) return false;

        $sql = "INSERT INTO Commentaire
                (Description, Date_Commentaire, Id_Joueur)
                VALUES(:description, :date_, :idJoueur)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':description' => $obj->getDescription(),
            ':date_' => $obj->getDate() ?: date('Y-m-d'),
            ':idJoueur' => $obj->getIdJoueur()
        ]);
    }

    public function update(object $obj): bool {
        if (!$obj instanceof Commentaire) return false;

        $sql = "UPDATE Commentaire SET 
                    Description = :description,
                    Date_Commentaire = :date_,
                    Id_Joueur = :idJoueur
                WHERE Id_Commentaire = :id";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':description' => $obj->getDescription(),
            ':date_' => $obj->getDate() ?: date('Y-m-d'),
            ':idJoueur' => $obj->getIdJoueur(),
            ':id' => $obj->getIdCommentaire()
        ]);
    }

    public function delete(object $obj): bool {
        if (!$obj instanceof Commentaire) return false;

        $sql = "DELETE FROM Commentaire WHERE Id_Commentaire = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':id' => $obj->getIdCommentaire()
        ]);
    }
}
