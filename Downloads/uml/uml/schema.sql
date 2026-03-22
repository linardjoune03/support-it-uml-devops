-- ============================================================
-- Système de Support IT — schema.sql
-- PostgreSQL 16
-- ============================================================

-- Extensions
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- ============================================================
-- ENUM types
-- ============================================================
CREATE TYPE ticket_statut AS ENUM (
  'ouvert',
  'en_cours',
  'resolu',
  'ferme'
);

CREATE TYPE user_role AS ENUM (
  'employe',
  'technicien',
  'administrateur'
);

-- ============================================================
-- Table: categorie
-- ============================================================
CREATE TABLE categorie (
  id        SERIAL PRIMARY KEY,
  libelle   VARCHAR(100) NOT NULL UNIQUE
);

-- ============================================================
-- Table: utilisateur
-- (Employé + Administrateur = même table, discriminé par role)
-- ============================================================
CREATE TABLE utilisateur (
  id         SERIAL PRIMARY KEY,
  nom        VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  role       user_role    NOT NULL DEFAULT 'employe',
  created_at TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- ============================================================
-- Table: technicien
-- (héritage : un technicien EST un utilisateur)
-- ============================================================
CREATE TABLE technicien (
  id          SERIAL PRIMARY KEY,
  utilisateur_id INTEGER NOT NULL UNIQUE REFERENCES utilisateur(id) ON DELETE CASCADE,
  specialite  VARCHAR(100),
  niveau      INTEGER NOT NULL DEFAULT 1 CHECK (niveau BETWEEN 1 AND 3)
);

-- ============================================================
-- Table: equipement
-- ============================================================
CREATE TABLE equipement (
  id          SERIAL PRIMARY KEY,
  num_serie   VARCHAR(100) NOT NULL UNIQUE,
  type        VARCHAR(100) NOT NULL,
  utilisateur_id INTEGER REFERENCES utilisateur(id) ON DELETE SET NULL
);

-- ============================================================
-- Table: ticket
-- ============================================================
CREATE TABLE ticket (
  id              SERIAL PRIMARY KEY,
  titre           VARCHAR(200)  NOT NULL,
  description     TEXT          NOT NULL,
  statut          ticket_statut NOT NULL DEFAULT 'ouvert',
  date_creation   TIMESTAMPTZ   NOT NULL DEFAULT NOW(),
  date_update     TIMESTAMPTZ   NOT NULL DEFAULT NOW(),
  utilisateur_id  INTEGER NOT NULL REFERENCES utilisateur(id) ON DELETE CASCADE,
  technicien_id   INTEGER REFERENCES technicien(id) ON DELETE SET NULL,
  categorie_id    INTEGER REFERENCES categorie(id) ON DELETE SET NULL,
  equipement_id   INTEGER REFERENCES equipement(id) ON DELETE SET NULL,
  fichier_joint   VARCHAR(255)
);

-- ============================================================
-- Table: commentaire
-- (composition : appartient à un ticket)
-- ============================================================
CREATE TABLE commentaire (
  id           SERIAL PRIMARY KEY,
  contenu      TEXT        NOT NULL,
  date         TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  ticket_id    INTEGER NOT NULL REFERENCES ticket(id) ON DELETE CASCADE,
  auteur_id    INTEGER NOT NULL REFERENCES utilisateur(id) ON DELETE CASCADE
);

-- ============================================================
-- Trigger : mise à jour auto de date_update sur ticket
-- ============================================================
CREATE OR REPLACE FUNCTION update_ticket_timestamp()
RETURNS TRIGGER AS $$
BEGIN
  NEW.date_update = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_ticket_update
BEFORE UPDATE ON ticket
FOR EACH ROW EXECUTE FUNCTION update_ticket_timestamp();

-- ============================================================
-- Données de seed (dev)
-- ============================================================
INSERT INTO categorie (libelle) VALUES
  ('Matériel'),
  ('Logiciel'),
  ('Réseau'),
  ('Accès / Droits'),
  ('Autre');

INSERT INTO utilisateur (nom, email, password, role) VALUES
  ('Admin Système',  'admin@support.fr',   gen_random_uuid()::text, 'administrateur'),
  ('Alice Martin',   'alice@support.fr',    gen_random_uuid()::text, 'employe'),
  ('Bob Dupont',     'bob@support.fr',      gen_random_uuid()::text, 'employe'),
  ('Claire Leblanc', 'claire@support.fr',   gen_random_uuid()::text, 'technicien');

INSERT INTO technicien (utilisateur_id, specialite, niveau) VALUES
  (4, 'Réseau & Sécurité', 2);
