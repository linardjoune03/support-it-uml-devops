// ============================================================
// routes/comments.js
// ============================================================
const express = require('express');
const pool    = require('../config/db');
const { authMiddleware } = require('../middlewares/auth');

const router = express.Router();
router.use(authMiddleware);

/**
 * @swagger
 * /api/comments/{ticketId}:
 *   get:
 *     summary: Commentaires d'un ticket
 *     tags: [Commentaires]
 */
router.get('/:ticketId', async (req, res, next) => {
  try {
    const { rows } = await pool.query(
      `SELECT c.*, u.nom AS auteur
       FROM commentaire c
       JOIN utilisateur u ON u.id = c.auteur_id
       WHERE c.ticket_id = $1
       ORDER BY c.date ASC`,
      [req.params.ticketId]
    );
    res.json(rows);
  } catch (err) { next(err); }
});

/**
 * @swagger
 * /api/comments/{ticketId}:
 *   post:
 *     summary: Ajouter un commentaire
 *     tags: [Commentaires]
 */
router.post('/:ticketId', async (req, res, next) => {
  try {
    const { contenu } = req.body;
    if (!contenu) return res.status(400).json({ error: 'Contenu requis' });
    const { rows } = await pool.query(
      'INSERT INTO commentaire (contenu, ticket_id, auteur_id) VALUES ($1,$2,$3) RETURNING *',
      [contenu, req.params.ticketId, req.user.id]
    );
    res.status(201).json(rows[0]);
  } catch (err) { next(err); }
});

router.delete('/:id', async (req, res, next) => {
  try {
    await pool.query('DELETE FROM commentaire WHERE id = $1 AND auteur_id = $2', [req.params.id, req.user.id]);
    res.status(204).send();
  } catch (err) { next(err); }
});

module.exports = router;