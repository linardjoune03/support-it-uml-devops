# Support IT — Système de gestion de tickets d'incidents

Application fullstack de gestion de tickets d'incidents informatiques, développée dans le cadre d'un projet de fin de module UML & Développement Fullstack.

## Stack technique

- **Backend** : Node.js / Express
- **Base de données** : PostgreSQL 16
- **Frontend** : PHP (appelle l'API REST via JWT)
- **Authentification** : JWT (JSON Web Tokens)
- **Documentation API** : Swagger UI
- **Tests** : Jest + Supertest
- **Déploiement** : Docker + Docker Compose

## Fonctionnalités

- Création et gestion de tickets d'incidents
- Système de rôles : Employé, Technicien, Administrateur
- Assignation de techniciens aux tickets
- Commentaires sur les tickets
- Gestion du parc d'équipements
- Upload de fichiers joints
- Statistiques globales (admin)
- Documentation API interactive (Swagger)

## Prérequis

- [Node.js 18+](https://nodejs.org/)
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (recommandé)
- ou PostgreSQL 16 installé localement
- PHP 8+ avec cURL (pour le frontend)

---

## Installation et lancement

### Option 1 — Avec Docker (recommandé)

```bash
# Cloner le projet
git clone https://github.com/linardjoune03/support-it-uml-devops.git
cd support-it-uml-devops

# Lancer l'API + la base de données
docker-compose up --build
```

L'API est disponible sur `http://localhost:3000`

### Option 2 — Sans Docker

**1. Installer les dépendances**
```bash
npm install
```

**2. Configurer les variables d'environnement**
```bash
cp .env.example .env
# Éditer .env avec vos paramètres de base de données
```

**3. Créer la base de données PostgreSQL**
```bash
psql -U postgres -c "CREATE DATABASE support_it;"
psql -U postgres -d support_it -f schema.sql
```

**4. Lancer le serveur**
```bash
# Production
npm start

# Développement (rechargement automatique)
npm run dev
```

L'API est disponible sur `http://localhost:3000`

---

## Lancer le frontend PHP

Dans un terminal séparé :

```bash
cd php_frontend
php -S localhost:8080
```

L'application est disponible sur `http://localhost:8080`

---

## Créer un compte administrateur

Via Swagger UI sur `http://localhost:3000/api-docs` → `POST /api/auth/register`

```json
{
  "nom": "Admin",
  "email": "admin@support.fr",
  "password": "votre_mot_de_passe",
  "role": "administrateur"
}
```

---

## Tests

```bash
npm test
```

Rapport de couverture généré automatiquement dans le dossier `coverage/`.

---

## Documentation API

Swagger UI disponible sur `http://localhost:3000/api-docs`

Endpoints disponibles :
- `POST /api/auth/register` — Créer un compte
- `POST /api/auth/login` — Se connecter
- `GET/POST /api/tickets` — Gestion des tickets
- `GET/POST /api/comments/:ticketId` — Commentaires
- `GET/POST /api/equipements` — Équipements
- `GET /api/categories` — Catégories
- `GET /api/users` — Utilisateurs (admin)
- `GET /api/stats` — Statistiques (admin)

---

## Structure du projet

```
support-it-uml-devops/
├── src/
│   ├── app.js                  # Point d'entrée
│   ├── config/
│   │   ├── db.js               # Connexion PostgreSQL
│   │   └── swagger.js          # Configuration Swagger
│   ├── middlewares/
│   │   └── auth.js             # Vérification JWT
│   ├── routes/
│   │   ├── auth.js             # Login / Register
│   │   ├── tickets.js          # CRUD tickets
│   │   ├── comments.js         # Commentaires
│   │   ├── users.js            # Utilisateurs
│   │   ├── equipements.js      # Équipements
│   │   ├── categories.js       # Catégories
│   │   └── stats.js            # Statistiques
│   └── tests/
│       └── auth.test.js        # Tests Jest
├── php_frontend/               # Frontend PHP
│   ├── api_helper.php          # Fonctions appel API
│   ├── login.php
│   ├── index.php
│   ├── creer_ticket.php
│   ├── voir_ticket.php
│   ├── admin.php
│   └── logout.php
├── schema.sql                  # Schéma base de données
├── Dockerfile
├── docker-compose.yml
├── .env.example
└── package.json
```

---

## Variables d'environnement

Copier `.env.example` en `.env` et remplir les valeurs :

| Variable | Description | Valeur par défaut |
|---|---|---|
| `PORT` | Port du serveur | `3000` |
| `DB_HOST` | Hôte PostgreSQL | `localhost` |
| `DB_PORT` | Port PostgreSQL | `5432` |
| `DB_NAME` | Nom de la base | `support_it` |
| `DB_USER` | Utilisateur PostgreSQL | `postgres` |
| `DB_PASSWORD` | Mot de passe PostgreSQL | — |
| `JWT_SECRET` | Clé secrète JWT | — |
| `FRONTEND_URL` | URL du frontend | `http://localhost:8080` |

---

## Auteurs

Projet réalisé dans le cadre du module UML & Développement Fullstack.
