import { Router } from 'express';
import {
  createCase,
  getCases,
  getCaseById,
  updateCase,
  transferCase,
  escalateCase,
  getCaseTimeline,
  createCaseNote,
  deleteCaseNote,
} from '../controllers/caseController';
import { authenticateJWT, requireRole } from '../middleware/auth';

const router = Router();

// Apply JWT authentication globally for all case routes
router.use(authenticateJWT);

// Create Case - Clients and Administrators can file cases
router.post(
  '/',
  requireRole(['SUPER_ADMIN', 'COURT_ADMIN', 'CLIENT']),
  createCase
);

// Query Cases - Filters dynamically according to role parameters
router.get('/', getCases);

// Case Details
router.get('/:id', getCaseById);

// Update Case metadata - Admins, Clerks, and Judges
router.put(
  '/:id',
  requireRole(['SUPER_ADMIN', 'COURT_ADMIN', 'JUDGE', 'CLERK']),
  updateCase
);

// Transfer Case - Administrators only
router.post(
  '/:id/transfer',
  requireRole(['SUPER_ADMIN', 'COURT_ADMIN']),
  transferCase
);

// Escalate Case - Clients, Lawyers, or Admins
router.post(
  '/:id/escalate',
  requireRole(['SUPER_ADMIN', 'COURT_ADMIN', 'CLIENT', 'LAWYER']),
  escalateCase
);

// Retrieve Case Event Log Timeline
router.get('/:id/timeline', getCaseTimeline);

// Case Notes
router.post('/:id/notes', createCaseNote);
router.delete('/notes/:noteId', deleteCaseNote);

export default router;

