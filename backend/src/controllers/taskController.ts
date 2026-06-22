import { Response } from 'express';
import { z } from 'zod';
import prisma from '../db';
import { AuthenticatedRequest } from '../middleware/auth';

const createTaskSchema = z.object({
  title: z.string().min(3).max(255),
  description: z.string().optional().nullable(),
  dueDate: z.string().datetime().optional().nullable(),
  caseId: z.string().uuid().optional().nullable(),
  assignedToId: z.string().uuid().optional().nullable(),
});

const updateTaskSchema = z.object({
  title: z.string().min(3).max(255).optional(),
  description: z.string().optional().nullable(),
  dueDate: z.string().datetime().optional().nullable(),
  isCompleted: z.boolean().optional(),
});

// Get all tasks for the logged in user
export const getTasks = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { caseId, isCompleted } = req.query;

    const filterConditions: any = {
      userId: req.user!.userId,
    };

    if (caseId) {
      filterConditions.caseId = caseId as string;
    }

    if (isCompleted !== undefined) {
      filterConditions.isCompleted = isCompleted === 'true';
    }

    const tasks = await prisma.task.findMany({
      where: filterConditions,
      orderBy: { createdAt: 'desc' },
      include: {
        case: {
          select: { caseNumber: true, title: true },
        },
      },
    });

    return res.status(200).json(tasks);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// Create a task
export const createTask = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const validatedData = createTaskSchema.parse(req.body);

    const taskUserId = validatedData.assignedToId || req.user!.userId;

    const newTask = await prisma.task.create({
      data: {
        title: validatedData.title,
        description: validatedData.description || null,
        dueDate: validatedData.dueDate ? new Date(validatedData.dueDate) : null,
        caseId: validatedData.caseId || null,
        userId: taskUserId,
      },
      include: {
        case: {
          select: { caseNumber: true, title: true },
        },
      },
    });

    return res.status(201).json(newTask);
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ errors: error.errors });
    }
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// Update task
export const updateTask = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const validatedData = updateTaskSchema.parse(req.body);

    const task = await prisma.task.findUnique({
      where: { id },
    });

    if (!task) {
      return res.status(404).json({ message: 'Task not found' });
    }

    // Role check: Only task owner or Admin can update
    if (task.userId !== req.user!.userId && !['SUPER_ADMIN', 'COURT_ADMIN'].includes(req.user!.role)) {
      return res.status(403).json({ message: 'Access denied' });
    }

    const updatedTask = await prisma.task.update({
      where: { id },
      data: {
        title: validatedData.title,
        description: validatedData.description !== undefined ? validatedData.description : undefined,
        dueDate: validatedData.dueDate !== undefined ? (validatedData.dueDate ? new Date(validatedData.dueDate) : null) : undefined,
        isCompleted: validatedData.isCompleted,
      },
      include: {
        case: {
          select: { caseNumber: true, title: true },
        },
      },
    });

    return res.status(200).json(updatedTask);
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ errors: error.errors });
    }
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// Delete task
export const deleteTask = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;

    const task = await prisma.task.findUnique({
      where: { id },
    });

    if (!task) {
      return res.status(404).json({ message: 'Task not found' });
    }

    // Role check: Only task owner or Admin can delete
    if (task.userId !== req.user!.userId && !['SUPER_ADMIN', 'COURT_ADMIN'].includes(req.user!.role)) {
      return res.status(403).json({ message: 'Access denied' });
    }

    await prisma.task.delete({
      where: { id },
    });

    return res.status(200).json({ message: 'Task deleted successfully' });
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};
