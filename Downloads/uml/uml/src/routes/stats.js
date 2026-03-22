const express = require('express');
const pool    = require('../config/db');
const { authMiddleware, requireRole } = require('../middlewares/auth');

const router = express.Router();
router.use(authMiddleware, requireRole('administrateur'));

router.get('/', async (req, res, next) => {
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
      total:          parseInt(total.rows[0].count),
      par_statut:     parStatut.rows,
      par_categorie:  parCategorie.rows,
      par_technicien: parTechnicien.rows,
    });
  } catch (err) { next(err); }
});

module.exports = router;
