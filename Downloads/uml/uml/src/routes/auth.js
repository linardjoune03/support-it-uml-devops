// const express = require('express');
// const bcrypt  = require('bcrypt');
// const jwt     = require('jsonwebtoken');
// const pool    = require('../config/db');

// const router = express.Router();
// const JWT_SECRET = process.env.JWT_SECRET || 'dev_secret_change_in_prod';

// router.post('/register', async (req, res, next) => {
//   try {
//     const { nom, email, password, role } = req.body;
//     if (!nom || !email || !password) return res.status(400).json({ error: 'Champs requis manquants' });
//     const hash = await bcrypt.hash(password, 10);
//     const { rows } = await pool.query(
//       'INSERT INTO utilisateur (nom, email, password, role) VALUES ($1,$2,$3,$4) RETURNING id, nom, email, role',
//       [nom, email, hash, role || 'employe']
//     );
//     res.status(201).json(rows[0]);
//   } catch (err) { next(err); }
// });

// router.post('/login', async (req, res, next) => {
//   try {
//     const { email, password } = req.body;
//     const { rows } = await pool.query('SELECT * FROM utilisateur WHERE email = $1', [email]);
//     const user = rows[0];
//     if (!user || !(await bcrypt.compare(password, user.password)))
//       return res.status(401).json({ error: 'Identifiants invalides' });
//     const token = jwt.sign({ id: user.id, role: user.role }, JWT_SECRET, { expiresIn: '8h' });
//     res.json({ token, user: { id: user.id, nom: user.nom, email: user.email, role: user.role } });
//   } catch (err) { next(err); }
// });

// module.exports = router;
const express = require('express');
const bcrypt  = require('bcrypt');
const jwt     = require('jsonwebtoken');
const pool    = require('../config/db');

const router = express.Router();
const JWT_SECRET = process.env.JWT_SECRET || 'dev_secret_change_in_prod';

/**
 * @swagger
 * /api/auth/register:
 *   post:
 *     summary: Créer un compte utilisateur
 *     tags: [Auth]
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             required: [nom, email, password]
 *             properties:
 *               nom:
 *                 type: string
 *                 example: Admin
 *               email:
 *                 type: string
 *                 example: admin@support.fr
 *               password:
 *                 type: string
 *                 example: yourpassword
 *               role:
 *                 type: string
 *                 enum: [employe, technicien, administrateur]
 *                 example: administrateur
 *     responses:
 *       201:
 *         description: Utilisateur créé
 *       400:
 *         description: Champs requis manquants
 */
router.post('/register', async (req, res, next) => {
  try {
    const { nom, email, password, role } = req.body;
    if (!nom || !email || !password) return res.status(400).json({ error: 'Champs requis manquants' });
    const hash = await bcrypt.hash(password, 10);
    const { rows } = await pool.query(
      'INSERT INTO utilisateur (nom, email, password, role) VALUES ($1,$2,$3,$4) RETURNING id, nom, email, role',
      [nom, email, hash, role || 'employe']
    );
    res.status(201).json(rows[0]);
  } catch (err) { next(err); }
});

/**
 * @swagger
 * /api/auth/login:
 *   post:
 *     summary: Se connecter et obtenir un token JWT
 *     tags: [Auth]
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             required: [email, password]
 *             properties:
 *               email:
 *                 type: string
 *                 example: admin@support.fr
 *               password:
 *                 type: string
 *                 example: yourpassword
 *     responses:
 *       200:
 *         description: Connexion réussie, retourne le token JWT
 *       401:
 *         description: Identifiants invalides
 */
router.post('/login', async (req, res, next) => {
  try {
    const { email, password } = req.body;
    const { rows } = await pool.query('SELECT * FROM utilisateur WHERE email = $1', [email]);
    const user = rows[0];
    if (!user || !(await bcrypt.compare(password, user.password)))
      return res.status(401).json({ error: 'Identifiants invalides' });
    const token = jwt.sign({ id: user.id, role: user.role }, JWT_SECRET, { expiresIn: '8h' });
    res.json({ token, user: { id: user.id, nom: user.nom, email: user.email, role: user.role } });
  } catch (err) { next(err); }
});

module.exports = router;
