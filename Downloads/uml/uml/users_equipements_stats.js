// ============================================================
// routes/users.js
// ============================================================
const express = require('express');
const pool    = require('../config/db');
const { authMiddleware, requireRole } = require('../middlewares/auth');

const usersRouter = express.Router();
usersRouter.use(authMiddleware);

usersRouter.get('/', requireRole('administrateur'), async (req, res, next) => {
  try {
    const { rows } = await pool.query(
      'SELECT id, nom, email, role, created_at FROM utilisateur ORDER BY created_at DESC'
    );
    res.json(rows);
  } catch (err) { next(err); }
});

usersRouter.get('/me', async (req, res, next) => {
  try {
    const { rows } = await pool.query(
      'SELECT id, nom, email, role FROM utilisateur WHERE id = $1',
      [req.user.id]
    );
    res.json(rows[0]);
  } catch (err) { next(err); }
});

usersRouter.get('/techniciens', requireRole('administrateur'), async (req, res, next) => {
  try {
    const { rows } = await pool.query(
      `SELECT t.id, u.nom, u.email, t.specialite, t.niveau
       FROM technicien t JOIN utilisateur u ON u.id = t.utilisateur_id`
    );
    res.json(rows);
  } catch (err) { next(err); }
});

module.exports = usersRouter;


// ============================================================
// routes/equipements.js — export séparé en bas
// ============================================================
const equipRouter = express.Router();
equipRouter.use(authMiddleware);

equipRouter.get('/', async (req, res, next) => {
  try {
    const { rows } = await pool.query('SELECT * FROM equipement ORDER BY id');
    res.json(rows);
  } catch (err) { next(err); }
});

equipRouter.post('/', requireRole('administrateur'), async (req, res, next) => {
  try {
    const { num_serie, type, utilisateur_id } = req.body;
    const { rows } = await pool.query(
      'INSERT INTO equipement (num_serie, type, utilisateur_id) VALUES ($1,$2,$3) RETURNING *',
      [num_serie, type, utilisateur_id || null]
    );
    res.status(201).json(rows[0]);
  } catch (err) { next(err); }
});

equipRouter.patch('/:id', requireRole('administrateur'), async (req, res, next) => {
  try {
    const { num_serie, type, utilisateur_id } = req.body;
    const { rows } = await pool.query(
      'UPDATE equipement SET num_serie=$1, type=$2, utilisateur_id=$3 WHERE id=$4 RETURNING *',
      [num_serie, type, utilisateur_id, req.params.id]
    );
    res.json(rows[0]);
  } catch (err) { next(err); }
});

module.exports.equipementsRouter = equipRouter;


// ============================================================
// routes/stats.js — export séparé en bas
// ============================================================
const statsRouter = express.Router();
statsRouter.use(authMiddleware, requireRole('administrateur'));

/**
 * @swagger
 * /api/stats:
 *   get:
 *     summary: Statistiques globales (admin)
 *     tags: [Stats]
 */
statsRouter.get('/', async (req, res, next) => {
  try {
    const [total, parStatut, parCategorie, parTechnicien] = await Promise.all([
      pool.query('SELECT COUNT(*) FROM ticket'),
      pool.query('SELECT statut, COUNT(*) FROM ticket GROUP BY statut'),
      pool.query(`SELECT c.libelle, COUNT(t.id) FROM categorie c
                  LEFT JOIN ticket t ON t.categorie_id = c.id GROUP BY c.libelle`),
      pool.query(`SELECT u.nom, COUNT(t.id) AS tickets_assignes
                  FROM technicien tech
                  JOIN utilisateur u ON u.id = tech.utilisateur_id
                  LEFT JOIN ticket t ON t.technicien_id = tech.id
                  GROUP BY u.nom ORDER BY tickets_assignes DESC`),
    ]);

    res.json({
      total:         parseInt(total.rows[0].count),
      par_statut:    parStatut.rows,
      par_categorie: parCategorie.rows,
      par_technicien: parTechnicien.rows,
    });
  } catch (err) { next(err); }
});

module.exports.statsRouter = statsRouter;