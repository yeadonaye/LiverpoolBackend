<?php
class Joueur {
    private int $idJoueur;
    private int $numLicence;
    private string $nom;
    private string $prenom;
    private string $dateNaissance;
    private float $taille;
    private int $poids;
    private string $statut;

    public function __construct(int $idJoueur, int $numLicence, string $nom, string $prenom, 
    string $dateNaissance, float $taille, int $poids, string $statut){
        $this->idJoueur = $idJoueur;
        $this->numLicence = $numLicence;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->dateNaissance = $dateNaissance;
        $this->taille = $taille;
        $this->poids = $poids;
        $this->statut = $statut;
    }

    // getters

    public function getIdJoueur(): int {
        return $this->idJoueur;
    }

    public function getNumLicence(): int {
        return $this->numLicence;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function getPrenom(): string {
        return $this->prenom;
    }

    public function getDateNaissance(): string {
        return $this->dateNaissance;
    }

    public function getTaille(): float {
        return $this->taille;
    }

    public function getPoids(): int {
        return $this->poids;
    }
    public function getStatut(): string {
        return $this->statut;
    }

    //setters

    public function setNumLicence(int $numLicence): void {
        $this->numLicence = $numLicence;
    }

    public function setNom(string $nom): void {
        $this->nom = $nom;
    }

    public function setPrenom(string $prenom): void {
        $this->prenom = $prenom;
    }

    public function setDateNaissance(string $dateNaissance): void {
        $this->dateNaissance = $dateNaissance;
    }

    public function setTaille(float $taille): void {
        $this->taille = $taille;
    }

    public function setPoids(int $poids): void {
        $this->poids = $poids;
    }

    public function setStatut(string $statut): void {
        $this->statut = $statut;
    }

}
?>