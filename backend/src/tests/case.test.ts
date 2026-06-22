import request from 'supertest';
import app from '../server';

describe('Case Endpoints Integration Tests', () => {
  it('should return 401 Unauthorized when retrieving cases without a JWT token', async () => {
    const res = await request(app).get('/api/v1/cases');
    expect(res.status).toBe(401);
    expect(res.body.message).toMatch(/unauthorized|token|missing|header/i);
  });
});
