import { Router } from 'express';
import {
  scheduleHearing,
  getHearings,
  rescheduleHearing,
} from '../controllers/hearingController';
import { authenticateJWT, requireRole } from '../middleware/auth';

const router = Router();

// Apply JWT verification universally
router.use(authenticateJWT);

// Query Hearings - scoped by user roles
router.get('/', getHearings);

// Schedule Hearing - limited to admins, judges, and clerks
router.post(
  '/',
  requireRole(['SUPER_ADMIN', 'COURT_ADMIN', 'JUDGE', 'CLERK']),
  scheduleHearing
);

// Reschedule / Update hearing details
router.put(
  '/:id',
  requireRole(['SUPER_ADMIN', 'COURT_ADMIN', 'JUDGE', 'CLERK']),
  rescheduleHearing
);

export default router;
