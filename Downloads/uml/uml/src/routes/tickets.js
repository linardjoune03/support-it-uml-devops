const express = require('express');
const multer  = require('multer');
const pool    = require('../config/db');
const { authMiddleware, requireRole } = require('../middlewares/auth');

const router  = express.Router();
const upload  = multer({ dest: 'uploads/' });

router.use(authMiddleware);

/**
 * @swagger
 * /api/tickets:
 *   get:
 *     summary: Lister les tickets
 *     tags: [Tickets]
 *     parameters:
 *       - in: query
 *         name: statut
 *         schema: { type: string }
 *       - in: query
 *         name: categorie_id
 *         schema: { type: integer }
 *     responses:
 *       200: { description: Liste des tickets }
 */
router.get('/', async (req, res, next) => {
  try {
    const { statut, categorie_id } = req.query;
    const conditions = [];
    const values = [];

    // Un employé ne voit que ses propres tickets
    if (req.user.role === 'employe') {
      conditions.push(`t.utilisateur_id = $${values.length + 1}`);
      values.push(req.user.id);
    }
    if (statut) {
      conditions.push(`t.statut = $${values.length + 1}`);
      values.push(statut);
    }
    if (categorie_id) {
      conditions.push(`t.categorie_id = $${values.length + 1}`);
      values.push(categorie_id);
    }

    const where = conditions.length ? 'WHERE ' + conditions.join(' AND ') : '';
    const { rows } = await pool.query(
      `SELECT t.*, u.nom AS auteur, c.libelle AS categorie, tech_u.nom AS technicien_nom
       FROM ticket t
       LEFT JOIN utilisateur u ON u.id = t.utilisateur_id
       LEFT JOIN categorie c ON c.id = t.categorie_id
       LEFT JOIN technicien tech ON tech.id = t.technicien_id
       LEFT JOIN utilisateur tech_u ON tech_u.id = tech.utilisateur_id
       ${where}
       ORDER BY t.date_creation DESC`,
      values
    );
    res.json(rows);
  } catch (err) { next(err); }
});

/**
 * @swagger
 * /api/tickets/{id}:
 *   get:
 *     summary: Détail d'un ticket
 *     tags: [Tickets]
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema: { type: integer }
 *     responses:
 *       200: { description: Ticket trouvé }
 *       404: { description: Non trouvé }
 */
router.get('/:id', async (req, res, next) => {
  try {
    const { rows } = await pool.query(
      `SELECT t.*, u.nom AS auteur, c.libelle AS categorie
       FROM ticket t
       LEFT JOIN utilisateur u ON u.id = t.utilisateur_id
       LEFT JOIN categorie c ON c.id = t.categorie_id
       WHERE t.id = $1`,
      [req.params.id]
    );
    if (!rows[0]) return res.status(404).json({ error: 'Ticket introuvable' });
    res.json(rows[0]);
  } catch (err) { next(err); }
});

/**
 * @swagger
 * /api/tickets:
 *   post:
 *     summary: Créer un ticket
 *     tags: [Tickets]
 */
router.post('/', upload.single('fichier'), async (req, res, next) => {
  try {
    const { titre, description, categorie_id, equipement_id } = req.body;
    if (!titre || !description) {
      return res.status(400).json({ error: 'Titre et description requis' });
    }
    const fichier_joint = req.file ? req.file.path : null;
    const { rows } = await pool.query(
      `INSERT INTO ticket (titre, description, categorie_id, equipement_id, utilisateur_id, fichier_joint)
       VALUES ($1,$2,$3,$4,$5,$6) RETURNING *`,
      [titre, description, categorie_id || null, equipement_id || null, req.user.id, fichier_joint]
    );
    res.status(201).json(rows[0]);
  } catch (err) { next(err); }
});

/**
 * @swagger
 * /api/tickets/{id}/statut:
 *   patch:
 *     summary: Mettre à jour le statut
 *     tags: [Tickets]
 */
router.patch('/:id/statut', requireRole('technicien', 'administrateur'), async (req, res, next) => {
  try {
    const { statut } = req.body;
    const validStatuts = ['ouvert', 'en_cours', 'resolu', 'ferme'];
    if (!validStatuts.includes(statut)) {
      return res.status(400).json({ error: 'Statut invalide' });
    }
    const { rows } = await pool.query(
      'UPDATE ticket SET statut = $1 WHERE id = $2 RETURNING *',
      [statut, req.params.id]
    );
    if (!rows[0]) return res.status(404).json({ error: 'Ticket introuvable' });
    res.json(rows[0]);
  } catch (err) { next(err); }
});

/**
 * @swagger
 * /api/tickets/{id}/assigner:
 *   patch:
 *     summary: Assigner un technicien à un ticket
 *     tags: [Tickets]
 */
router.patch('/:id/assigner', requireRole('administrateur'), async (req, res, next) => {
  try {
    const { technicien_id } = req.body;
    const { rows } = await pool.query(
      'UPDATE ticket SET technicien_id = $1, statut = \'en_cours\' WHERE id = $2 RETURNING *',
      [technicien_id, req.params.id]
    );
    if (!rows[0]) return res.status(404).json({ error: 'Ticket introuvable' });
    res.json(rows[0]);
  } catch (err) { next(err); }
});

/**
 * @swagger
 * /api/tickets/{id}:
 *   delete:
 *     summary: Supprimer un ticket
 *     tags: [Tickets]
 */
router.delete('/:id', requireRole('administrateur'), async (req, res, next) => {
  try {
    const { rowCount } = await pool.query('DELETE FROM ticket WHERE id = $1', [req.params.id]);
    if (!rowCount) return res.status(404).json({ error: 'Ticket introuvable' });
    res.status(204).send();
  } catch (err) { next(err); }
});

module.exports = router;