const express = require('express');
const pool    = require('../config/db');
const { authMiddleware, requireRole } = require('../middlewares/auth');

const router = express.Router();
router.use(authMiddleware);

router.get('/', async (req, res, next) => {
  try {
    const { rows } = await pool.query('SELECT * FROM equipement ORDER BY id');
    res.json(rows);
  } catch (err) { next(err); }
});

router.post('/', requireRole('administrateur'), async (req, res, next) => {
  try {
    const { num_serie, type, utilisateur_id } = req.body;
    const { rows } = await pool.query(
      'INSERT INTO equipement (num_serie, type, utilisateur_id) VALUES ($1,$2,$3) RETURNING *',
      [num_serie, type, utilisateur_id || null]
    );
    res.status(201).json(rows[0]);
  } catch (err) { next(err); }
});

router.patch('/:id', requireRole('administrateur'), async (req, res, next) => {
  try {
    const { num_serie, type, utilisateur_id } = req.body;
    const { rows } = await pool.query(
      'UPDATE equipement SET num_serie=$1, type=$2, utilisateur_id=$3 WHERE id=$4 RETURNING *',
      [num_serie, type, utilisateur_id, req.params.id]
    );
    res.json(rows[0]);
  } catch (err) { next(err); }
});

module.exports = router;
