import { Router } from 'express';
import {
  summarizeCase,
  getLegalInsights,
} from '../controllers/aiController';
import { authenticateJWT, requireRole } from '../middleware/auth';

const router = Router();

// Apply JWT authentication globally for all AI routes
router.use(authenticateJWT);

router.post(
  '/summarize-case',
  requireRole(['SUPER_ADMIN', 'COURT_ADMIN', 'JUDGE', 'LAWYER', 'CLIENT']),
  summarizeCase
);

router.post(
  '/legal-insights',
  requireRole(['SUPER_ADMIN', 'COURT_ADMIN', 'JUDGE', 'LAWYER']),
  getLegalInsights
);

export default router;
