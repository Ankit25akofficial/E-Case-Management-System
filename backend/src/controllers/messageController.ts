import { Response } from 'express';
import prisma from '../db';
import { AuthenticatedRequest } from '../middleware/auth';
import { sendToUser } from '../socket';

// 1. Send Message
export const sendMessage = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { receiverId, content } = req.body;
    const senderId = req.user?.userId;

    if (!receiverId || !content) {
      return res.status(400).json({ message: 'Receiver ID and content are required' });
    }

    if (!senderId) {
      return res.status(401).json({ message: 'Sender not authenticated' });
    }

    // Verify receiver exists
    const receiver = await prisma.user.findUnique({
      where: { id: receiverId },
    });

    if (!receiver) {
      return res.status(404).json({ message: 'Receiver user not found' });
    }

    const message = await prisma.message.create({
      data: {
        senderId,
        receiverId,
        content,
      },
      include: {
        sender: { select: { fullName: true, role: true } },
      },
    });

    // Notify online receiver via WebSocket
    sendToUser(receiverId, 'new_message', message);

    return res.status(201).json(message);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 2. Get Message Thread
export const getMessages = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { partnerId } = req.params;
    const userId = req.user?.userId;

    if (!userId) {
      return res.status(401).json({ message: 'User not authenticated' });
    }

    const messages = await prisma.message.findMany({
      where: {
        OR: [
          { senderId: userId, receiverId: partnerId },
          { senderId: partnerId, receiverId: userId },
        ],
      },
      orderBy: { createdAt: 'asc' },
      include: {
        sender: { select: { fullName: true, role: true } },
      },
    });

    // Mark messages as read
    await prisma.message.updateMany({
      where: {
        senderId: partnerId,
        receiverId: userId,
        isRead: false,
      },
      data: { isRead: true },
    });

    return res.status(200).json(messages);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 3. Get Conversations List
export const getConversations = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const userId = req.user?.userId;

    if (!userId) {
      return res.status(401).json({ message: 'User not authenticated' });
    }

    // Get all messages where user is sender or receiver
    const messages = await prisma.message.findMany({
      where: {
        OR: [{ senderId: userId }, { receiverId: userId }],
      },
      orderBy: { createdAt: 'desc' },
      include: {
        sender: { select: { id: true, fullName: true, role: true } },
        receiver: { select: { id: true, fullName: true, role: true } },
      },
    });

    // Aggregate unique conversation partners
    const conversationMap = new Map<string, any>();

    for (const msg of messages) {
      const partner = msg.senderId === userId ? msg.receiver : msg.sender;
      
      if (!conversationMap.has(partner.id)) {
        conversationMap.set(partner.id, {
          partner: {
            id: partner.id,
            fullName: partner.fullName,
            role: partner.role,
          },
          lastMessage: {
            content: msg.content,
            createdAt: msg.createdAt,
            senderId: msg.senderId,
            isRead: msg.isRead,
          },
        });
      }
    }

    const conversations = Array.from(conversationMap.values());

    return res.status(200).json(conversations);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 4. Get Directory of Users to Chat With
export const getUsersList = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const userId = req.user?.userId;
    const { role } = req.query;

    if (!userId) {
      return res.status(401).json({ message: 'User not authenticated' });
    }

    const filterConditions: any = {
      id: { not: userId },
      isActive: true,
    };

    if (role) {
      filterConditions.role = role;
    }

    const users = await prisma.user.findMany({
      where: filterConditions,
      select: {
        id: true,
        fullName: true,
        username: true,
        role: true,
      },
      orderBy: { fullName: 'asc' },
    });

    return res.status(200).json(users);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};
