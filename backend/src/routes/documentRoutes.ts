import { Router } from 'express';
import {
  uploadDocument,
  downloadDocument,
  approveDocument,
  signDocument,
  getDocuments,
} from '../controllers/documentController';
import { authenticateJWT, requireRole } from '../middleware/auth';
import { uploadMiddleware } from '../middleware/upload';

const router = Router();

// Apply JWT verification universally
router.use(authenticateJWT);

// Get all documents
router.get('/', getDocuments);

// Upload Document - expects form-data with keys 'file', 'caseId', and 'title'
router.post(
  '/upload',
  uploadMiddleware.single('file'),
  uploadDocument
);

// Secure file download feed
router.get('/:id/download', downloadDocument);

// Document approval reviews - Judges, Clerks, Admins
router.post(
  '/:id/approve',
  requireRole(['SUPER_ADMIN', 'COURT_ADMIN', 'JUDGE', 'CLERK']),
  approveDocument
);

// Apply digital signature authorization - Judges, Lawyers, Admins
router.post(
  '/:id/sign',
  requireRole(['SUPER_ADMIN', 'COURT_ADMIN', 'JUDGE', 'LAWYER']),
  signDocument
);

export default router;
