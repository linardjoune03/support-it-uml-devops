require('dotenv').config();
const express = require('express');
const cors    = require('cors');
const swaggerUi   = require('swagger-ui-express');
const swaggerSpec = require('./config/swagger');

const authRouter        = require('./routes/auth');
const ticketsRouter     = require('./routes/tickets');
const categoriesRouter = require('./routes/categories');
const usersRouter       = require('./routes/users');
const commentsRouter    = require('./routes/comments');
const equipementsRouter = require('./routes/equipements');
const statsRouter       = require('./routes/stats');

const app = express();

app.use(cors({ origin: process.env.FRONTEND_URL || 'http://localhost:5173' }));
app.use(express.json());
app.use('/uploads', express.static('uploads'));

// Swagger UI
app.use('/api-docs', swaggerUi.serve, swaggerUi.setup(swaggerSpec));

// Routes
app.use('/api/auth',        authRouter);
app.use('/api/tickets',     ticketsRouter);
app.use('/api/categories',  categoriesRouter);
app.use('/api/users',       usersRouter);
app.use('/api/comments',    commentsRouter);
app.use('/api/equipements', equipementsRouter);
app.use('/api/stats',       statsRouter);

// Health check
app.get('/health', (_, res) => res.json({ status: 'ok' }));

// Gestion erreurs globale
app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(err.status || 500).json({ error: err.message || 'Erreur serveur' });
});

if (require.main === module) {
  const PORT = process.env.PORT || 3000;
  app.listen(PORT, () => console.log(`API démarrée sur http://localhost:${PORT}`));
}

module.exports = app;
