-- Fichier : my_data_base.sql

-- 1. On supprime les tables si elles existent déjà (pratique pour recommencer)
DROP TABLE IF EXISTS Commentaire CASCADE;
DROP TABLE IF EXISTS Ticket CASCADE;
DROP TABLE IF EXISTS Technicien CASCADE;
DROP TABLE IF EXISTS Equipement CASCADE;
DROP TABLE IF EXISTS Categorie CASCADE;
DROP TABLE IF EXISTS Utilisateur CASCADE;

-- 2. Création des tables de base
CREATE TABLE Utilisateur (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL -- Ex: 'Employe', 'Admin'
);

CREATE TABLE Categorie (
    id SERIAL PRIMARY KEY,
    libelle VARCHAR(100) NOT NULL
);

CREATE TABLE Equipement (
    id SERIAL PRIMARY KEY,
    numSerie VARCHAR(100) UNIQUE NOT NULL,
    type VARCHAR(100) NOT NULL
);

-- 3. Création des tables liées
-- L'Héritage (Triangle UML) : Le technicien EST un utilisateur
CREATE TABLE Technicien (
    id_utilisateur INT PRIMARY KEY REFERENCES Utilisateur(id) ON DELETE CASCADE,
    specialite VARCHAR(100),
    niveau INT
);

-- Le Ticket (Le centre de ton diagramme)
CREATE TABLE Ticket (
    id SERIAL PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    statut VARCHAR(50) DEFAULT 'Ouvert', 
    dateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Les traits (Associations) de ton diagramme UML :
    id_auteur INT NOT NULL REFERENCES Utilisateur(id),           -- L'employé qui crée (1..*)
    id_categorie INT NOT NULL REFERENCES Categorie(id),          -- La catégorie (1..*)
    id_technicien INT REFERENCES Technicien(id_utilisateur),     -- Le technicien (0..1 : peut être vide)
    id_equipement INT REFERENCES Equipement(id)                  -- L'équipement (0..1 : peut être vide)
);

-- Le Commentaire (Composition UML : Losange noir)
CREATE TABLE Commentaire (
    id SERIAL PRIMARY KEY,
    contenu TEXT NOT NULL,
    dateEnvoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- ON DELETE CASCADE = Le losange noir ! Si on supprime le ticket, le commentaire disparaît.
    id_ticket INT NOT NULL REFERENCES Ticket(id) ON DELETE CASCADE 
);


-- 4. Insertion de données de test
-- On crée un Utilisateur (Employé)
INSERT INTO Utilisateur (nom, email, mot_de_passe, role) 
VALUES ('Lina', 'lina@example.com', 'password123', 'Employe');

-- On crée un deuxième Utilisateur qui sera Technicien
INSERT INTO Utilisateur (nom, email, mot_de_passe, role) 
VALUES ('john', 'john@support.com', 'admin789', 'Technicien');

-- On lie cet utilisateur à la table Technicien (L'Héritage UML)
INSERT INTO Technicien (id_utilisateur, specialite, niveau) 
VALUES (2, 'Réseau', 5);

-- On crée une catégorie et un équipement
INSERT INTO Categorie (libelle) VALUES ('Panne Matérielle');
INSERT INTO Equipement (numSerie, type) VALUES ('PC-LINA-01', 'Ordinateur Portable');

-- ENFIN : On crée le premier Ticket (Le lien entre tout le monde !)
INSERT INTO Ticket (titre, description, id_auteur, id_categorie, id_technicien, id_equipement)
VALUES ('Écran bleu', 'Mon PC ne s''allume plus ce matin', 1, 1, 2, 1);