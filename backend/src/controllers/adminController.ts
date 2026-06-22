import { Response } from 'express';
import prisma from '../db';
import { AuthenticatedRequest } from '../middleware/auth';

// 1. Get Audit Logs
export const getAuditLogs = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const logs = await prisma.auditLog.findMany({
      include: {
        user: {
          select: {
            id: true,
            fullName: true,
            email: true,
            role: true,
          },
        },
      },
      orderBy: {
        createdAt: 'desc',
      },
    });

    return res.status(200).json(logs);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 2. Get Users List with Full details
export const getUsersFull = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const users = await prisma.user.findMany({
      include: {
        profile: true,
        _count: {
          select: {
            clientCases: true,
            judgeCases: true,
            lawyerCases: true,
          },
        },
      },
      orderBy: {
        fullName: 'asc',
      },
    });

    // Formatted details
    const formattedUsers = users.map((u) => ({
      id: u.id,
      username: u.username,
      email: u.email,
      fullName: u.fullName,
      role: u.role,
      isActive: u.isActive,
      loginAttempts: u.loginAttempts,
      lockoutUntil: u.lockoutUntil,
      createdAt: u.createdAt,
      profile: u.profile,
      casesCount: u._count.clientCases + u._count.judgeCases + u._count.lawyerCases,
    }));

    return res.status(200).json(formattedUsers);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 3. Toggle User Active Status
export const toggleUserStatus = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const { isActive } = req.body;

    if (typeof isActive !== 'boolean') {
      return res.status(400).json({ message: 'isActive boolean value required' });
    }

    const targetUser = await prisma.user.findUnique({ where: { id } });

    if (!targetUser) {
      return res.status(404).json({ message: 'User not found' });
    }

    const updatedUser = await prisma.user.update({
      where: { id },
      data: { isActive },
    });

    // Audit Log
    await prisma.auditLog.create({
      data: {
        userId: req.user?.userId,
        action: 'USER_TOGGLE_STATUS',
        details: `Toggled user "${targetUser.fullName}" status to: ${isActive ? 'Active' : 'Inactive'}`,
        ipAddress: req.ip,
      },
    });

    return res.status(200).json({
      id: updatedUser.id,
      fullName: updatedUser.fullName,
      isActive: updatedUser.isActive,
    });
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};
