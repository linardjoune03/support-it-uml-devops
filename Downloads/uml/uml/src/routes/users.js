const express = require('express');
const pool    = require('../config/db');
const { authMiddleware, requireRole } = require('../middlewares/auth');

const router = express.Router();
router.use(authMiddleware);

router.get('/', requireRole('administrateur'), async (req, res, next) => {
  try {
    const { rows } = await pool.query(
      'SELECT id, nom, email, role, created_at FROM utilisateur ORDER BY created_at DESC'
    );
    res.json(rows);
  } catch (err) { next(err); }
});

router.get('/me', async (req, res, next) => {
  try {
    const { rows } = await pool.query(
      'SELECT id, nom, email, role FROM utilisateur WHERE id = $1',
      [req.user.id]
    );
    res.json(rows[0]);
  } catch (err) { next(err); }
});

router.get('/techniciens', requireRole('administrateur'), async (req, res, next) => {
  try {
    const { rows } = await pool.query(
      `SELECT t.id, u.nom, u.email, t.specialite, t.niveau
       FROM technicien t JOIN utilisateur u ON u.id = t.utilisateur_id`
    );
    res.json(rows);
  } catch (err) { next(err); }
});

module.exports = router;
