import { generateAccessToken, generateRefreshToken, verifyAccessToken, verifyRefreshToken } from '../utils/auth';

describe('Auth Utility Tests', () => {
  const mockUser = {
    id: 'user-uuid-12345',
    username: 'testuser',
    role: 'CLIENT',
  };

  it('should generate a valid access token and parse payload correctly', () => {
    const accessToken = generateAccessToken(mockUser);
    expect(accessToken).toBeDefined();
    expect(typeof accessToken).toBe('string');

    const decoded = verifyAccessToken(accessToken);
    expect(decoded.userId).toBe(mockUser.id);
    expect(decoded.username).toBe(mockUser.username);
    expect(decoded.role).toBe(mockUser.role);
  });

  it('should generate a valid refresh token and parse payload correctly', () => {
    const refreshToken = generateRefreshToken(mockUser);
    expect(refreshToken).toBeDefined();
    expect(typeof refreshToken).toBe('string');

    const decoded = verifyRefreshToken(refreshToken);
    expect(decoded.userId).toBe(mockUser.id);
    expect(decoded.username).toBe(mockUser.username);
    expect(decoded.role).toBe(mockUser.role);
  });

  it('should throw an error when verifying an invalid token', () => {
    expect(() => verifyAccessToken('invalid-token-string')).toThrow();
  });
});
