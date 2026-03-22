const express = require('express');
const pool    = require('../config/db');
const { authMiddleware } = require('../middlewares/auth');

const router = express.Router();
router.use(authMiddleware);

/**
 * @swagger
 * /api/categories:
 *   get:
 *     summary: Lister toutes les catégories
 *     tags: [Categories]
 *     responses:
 *       200:
 *         description: Liste des catégories
 */
router.get('/', async (req, res, next) => {
  try {
    const { rows } = await pool.query('SELECT * FROM categorie ORDER BY libelle ASC');
    res.json(rows);
  } catch (err) { next(err); }
});

/**
 * @swagger
 * /api/categories:
 *   post:
 *     summary: Créer une catégorie (admin)
 *     tags: [Categories]
 */
router.post('/', async (req, res, next) => {
  try {
    const { libelle } = req.body;
    if (!libelle) return res.status(400).json({ error: 'Libellé requis' });
    const { rows } = await pool.query(
      'INSERT INTO categorie (libelle) VALUES ($1) RETURNING *',
      [libelle]
    );
    res.status(201).json(rows[0]);
  } catch (err) { next(err); }
});

module.exports = router;
