const request = require('supertest');

jest.mock('../config/db', () => ({
  query: jest.fn(),
}));

const pool = require('../config/db');
const app  = require('../app');

describe('Auth — Register', () => {
  it('doit refuser si champs manquants', async () => {
    const res = await request(app)
      .post('/api/auth/register')
      .send({ nom: 'Test' });
    expect(res.statusCode).toBe(400);
  });
});

describe('Auth — Login', () => {
  it('doit refuser des mauvais identifiants', async () => {
    pool.query.mockResolvedValueOnce({ rows: [] });
    const res = await request(app)
      .post('/api/auth/login')
      .send({ email: 'faux@faux.fr', password: 'mauvais' });
    expect(res.statusCode).toBe(401);
  });
});

describe('Health check', () => {
  it('doit retourner status ok', async () => {
    const res = await request(app).get('/health');
    expect(res.statusCode).toBe(200);
    expect(res.body.status).toBe('ok');
  });
});
