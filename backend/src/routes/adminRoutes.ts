import { Router } from 'express';
import { getAuditLogs, getUsersFull, toggleUserStatus } from '../controllers/adminController';
import { authenticateJWT, requireRole } from '../middleware/auth';

const router = Router();

// Apply authentication + Admin role universally
router.use(authenticateJWT);
router.use(requireRole(['SUPER_ADMIN', 'COURT_ADMIN']));

// Audit logs
router.get('/audit-logs', getAuditLogs);

// Users lists & updates
router.get('/users', getUsersFull);
router.put('/users/:id/status', toggleUserStatus);

export default router;
