-- Création de la base de données
CREATE DATABASE IF NOT EXISTS gestion_joueurs;
USE gestion_joueurs;

-- Désactiver temporairement les contraintes de clé étrangère
SET FOREIGN_KEY_CHECKS = 0;

-- Supprimer les tables si elles existent déjà (dans l'ordre inverse des dépendances)
DROP TABLE IF EXISTS Participer;
DROP TABLE IF EXISTS Commentaire;
DROP TABLE IF EXISTS Match_;
DROP TABLE IF EXISTS Joueur;

-- Réactiver les contraintes de clé étrangère
SET FOREIGN_KEY_CHECKS = 1;

-- Table Joueur
CREATE TABLE Joueur (
    Id_Joueur INT AUTO_INCREMENT,
    Num_Licence INT NOT NULL,
    Nom VARCHAR(50),
    Prenom VARCHAR(50),
    Date_Naissance DATE,
    Taille DECIMAL(15,2),
    Poids DECIMAL(15,2),
    Statut VARCHAR(50),
    PRIMARY KEY(Id_Joueur),
    UNIQUE(Num_Licence)
);

-- Table Match_ 
CREATE TABLE `Match_` (
    Id_Match INT AUTO_INCREMENT,
    Date_Rencontre DATE,
    Heure TIME,
    Nom_Equipe_Adverse VARCHAR(50),
    Lieu VARCHAR(50),
    Score_Adversaire INT,
    Score_Nous INT,,
    PRIMARY KEY(Id_Match)
);

-- Table Commentaire
CREATE TABLE Commentaire (
    Id_Commentaire INT AUTO_INCREMENT,
    Description TEXT,
    Date_ DATE,
    Id_Joueur INT NOT NULL,
    PRIMARY KEY(Id_Commentaire),
    FOREIGN KEY(Id_Joueur) REFERENCES Joueur(Id_Joueur) ON DELETE CASCADE
);

-- Table Participer (table de liaison entre Joueur et Match_)
CREATE TABLE Participer (
    Id_Joueur INT,
    Id_Match INT,
    Id_participation INT AUTO_INCREMENT,
    Poste VARCHAR(50),
    Note INT CHECK (Note >= 0 AND Note <= 5) COMMENT 'Note sur 5 étoiles',
    Titulaire_ou_pas BOOLEAN,
    PRIMARY KEY(Id_Joueur, Id_Match),
    UNIQUE(Id_participation),
    FOREIGN KEY(Id_Joueur) REFERENCES Joueur(Id_Joueur) ON DELETE CASCADE,
    FOREIGN KEY(Id_Match) REFERENCES `Match_`(Id_Match) ON DELETE CASCADE
);