import { Router } from 'express';
import {
  sendMessage,
  getMessages,
  getConversations,
  getUsersList,
} from '../controllers/messageController';
import { authenticateJWT } from '../middleware/auth';

const router = Router();

// Apply authentication guard
router.use(authenticateJWT);

// Retrieve active list of conversations
router.get('/conversations', getConversations);

// Retrieve available directories for chats
router.get('/users', getUsersList);

// Retrieve full chat thread
router.get('/:partnerId', getMessages);

// Send message
router.post('/', sendMessage);

export default router;
