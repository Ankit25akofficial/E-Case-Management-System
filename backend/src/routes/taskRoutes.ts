import { Router } from 'express';
import {
  getTasks,
  createTask,
  updateTask,
  deleteTask,
} from '../controllers/taskController';
import { authenticateJWT } from '../middleware/auth';

const router = Router();

// Apply JWT authentication globally for all task routes
router.use(authenticateJWT);

router.get('/', getTasks);
router.post('/', createTask);
router.put('/:id', updateTask);
router.delete('/:id', deleteTask);

export default router;
