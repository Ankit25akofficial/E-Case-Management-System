import Redis from 'ioredis';

let redisClient: Redis | null = null;
const memoryCache = new Map<string, { value: any; expiresAt: number }>();

// Initialize Redis if configured
const redisUrl = process.env.REDIS_URL || process.env.REDIS_HOST;
if (redisUrl) {
  try {
    redisClient = new Redis(redisUrl, {
      maxRetriesPerRequest: 1,
      connectTimeout: 2000, // Fail fast to activate in-memory fallback
      retryStrategy: () => null, // Do not reconnect infinitely if offline
    });

    redisClient.on('error', (err) => {
      console.warn('[Cache] Redis connection error, using in-memory cache fallback:', err.message);
      redisClient = null; // Mark as disabled on connection error
    });

    redisClient.on('connect', () => {
      console.log('[Cache] Redis client connected successfully');
    });
  } catch (err) {
    console.warn('[Cache] Redis initialization failed, using in-memory cache:', err);
    redisClient = null;
  }
} else {
  console.log('[Cache] No Redis configurations found, utilizing local in-memory cache store');
}

/**
 * Get value from cache
 */
export const cacheGet = async (key: string): Promise<any | null> => {
  if (redisClient) {
    try {
      const val = await redisClient.get(key);
      return val ? JSON.parse(val) : null;
    } catch (err) {
      console.warn('[Cache] Redis get failed, reading from in-memory fallback:', err);
    }
  }

  // Memory Fallback
  const cached = memoryCache.get(key);
  if (!cached) return null;

  if (Date.now() > cached.expiresAt) {
    memoryCache.delete(key);
    return null;
  }

  return cached.value;
};

/**
 * Set value in cache with TTL
 */
export const cacheSet = async (key: string, value: any, ttlSeconds: number = 60): Promise<void> => {
  if (redisClient) {
    try {
      await redisClient.set(key, JSON.stringify(value), 'EX', ttlSeconds);
      return;
    } catch (err) {
      console.warn('[Cache] Redis set failed, storing in-memory:', err);
    }
  }

  // Memory Fallback
  memoryCache.set(key, {
    value,
    expiresAt: Date.now() + ttlSeconds * 1000,
  });
};

/**
 * Delete key from cache
 */
export const cacheDel = async (key: string): Promise<void> => {
  if (redisClient) {
    try {
      await redisClient.del(key);
      return;
    } catch (err) {
      console.warn('[Cache] Redis del failed, removing in-memory:', err);
    }
  }

  memoryCache.delete(key);
};

/**
 * Invalidate cache keys matching prefix
 */
export const cacheInvalidatePrefix = async (prefix: string): Promise<void> => {
  if (redisClient) {
    try {
      const keys = await redisClient.keys(`${prefix}*`);
      if (keys.length > 0) {
        await redisClient.del(...keys);
      }
      return;
    } catch (err) {
      console.warn('[Cache] Redis prefix invalidation failed, clearing memory cache:', err);
    }
  }

  // Memory fallback prefix matching
  for (const key of memoryCache.keys()) {
    if (key.startsWith(prefix)) {
      memoryCache.delete(key);
    }
  }
};
