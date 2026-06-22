import { Response } from 'express';
import { z } from 'zod';
import prisma from '../db';
import { AuthenticatedRequest } from '../middleware/auth';
import { CaseStatus, CasePriority } from '../types/enums';
import { cacheGet, cacheSet, cacheInvalidatePrefix } from '../utils/cacheService';

// Validation Schemas
const createCaseSchema = z.object({
  title: z.string().min(5).max(255),
  description: z.string().optional(),
  category: z.string().min(2).max(100),
  courtId: z.string().uuid(),
  clientId: z.string().uuid(),
  judgeId: z.string().uuid().optional().nullable(),
  lawyerId: z.string().uuid().optional().nullable(),
  priority: z.nativeEnum(CasePriority).default(CasePriority.MEDIUM),
});

const updateCaseSchema = z.object({
  title: z.string().min(5).max(255).optional(),
  description: z.string().optional(),
  category: z.string().min(2).max(100).optional(),
  status: z.nativeEnum(CaseStatus).optional(),
  priority: z.nativeEnum(CasePriority).optional(),
  closingDate: z.string().datetime().optional().nullable(),
});

const transferCaseSchema = z.object({
  courtId: z.string().uuid().optional(),
  judgeId: z.string().uuid().optional().nullable(),
});

// Helper: Generate Unique Case Number
const generateCaseNumber = async (): Promise<string> => {
  const year = new Date().getFullYear();
  const randomNum = Math.floor(1000 + Math.random() * 9000);
  const caseNumber = `ECMS-${year}-${randomNum}`;
  
  // Verify uniqueness
  const exists = await prisma.case.findUnique({
    where: { caseNumber },
  });
  
  if (exists) {
    return generateCaseNumber();
  }
  
  return caseNumber;
};

// 1. Create Case
export const createCase = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const validatedData = createCaseSchema.parse(req.body);

    const caseNumber = await generateCaseNumber();
    const status = validatedData.judgeId ? CaseStatus.ASSIGNED : CaseStatus.PENDING;

    const newCase = await prisma.case.create({
      data: {
        caseNumber,
        title: validatedData.title,
        description: validatedData.description,
        category: validatedData.category,
        status,
        priority: validatedData.priority,
        courtId: validatedData.courtId,
        clientId: validatedData.clientId,
        judgeId: validatedData.judgeId || null,
        lawyerId: validatedData.lawyerId || null,
      },
    });

    // Write audit trail
    await prisma.auditLog.create({
      data: {
        userId: req.user?.userId,
        action: 'CASE_CREATE',
        details: `Created case registry ${newCase.caseNumber}`,
        ipAddress: req.ip,
      },
    });

    await cacheInvalidatePrefix('cases:list');

    return res.status(201).json(newCase);
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ errors: error.errors });
    }
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 2. Query Cases
export const getCases = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { status, category, priority, client, judge, page = 1, limit = 10 } = req.query;

    const skip = (Number(page) - 1) * Number(limit);
    const take = Number(limit);

    // Dynamic Filter Build
    const filterConditions: any = {};
    if (status) filterConditions.status = status as CaseStatus;
    if (category) filterConditions.category = category as string;
    if (priority) filterConditions.priority = priority as CasePriority;
    
    // User contextual constraints
    if (req.user?.role === 'CLIENT') {
      filterConditions.clientId = req.user.userId;
    } else if (req.user?.role === 'JUDGE') {
      filterConditions.judgeId = req.user.userId;
    } else if (client) {
      filterConditions.clientId = client as string;
    } else if (judge) {
      filterConditions.judgeId = judge as string;
    }

    const cacheKey = `cases:list:${JSON.stringify(req.query)}:role:${req.user?.role}:uid:${req.user?.userId}`;
    const cachedData = await cacheGet(cacheKey);
    if (cachedData) {
      return res.status(200).json(cachedData);
    }

    const [cases, total] = await prisma.$transaction([
      prisma.case.findMany({
        where: filterConditions,
        skip,
        take,
        orderBy: { filingDate: 'desc' },
        include: {
          client: { select: { fullName: true, email: true } },
          judge: { select: { fullName: true, email: true } },
          court: { select: { name: true, location: true } },
        },
      }),
      prisma.case.count({ where: filterConditions }),
    ]);

    const responsePayload = {
      cases,
      pagination: {
        total,
        page: Number(page),
        limit: Number(limit),
        totalPages: Math.ceil(total / take),
      },
    };

    await cacheSet(cacheKey, responsePayload, 60);

    return res.status(200).json(responsePayload);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 3. Get Case By ID
export const getCaseById = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;

    const cacheKey = `cases:details:${id}`;
    const cachedData = await cacheGet(cacheKey);
    if (cachedData) {
      // Role-based Access Control Check
      if (req.user?.role === 'CLIENT' && cachedData.clientId !== req.user.userId) {
        return res.status(403).json({ message: 'Access denied' });
      }
      if (req.user?.role === 'JUDGE' && cachedData.judgeId !== req.user.userId) {
        return res.status(403).json({ message: 'Access denied' });
      }
      return res.status(200).json(cachedData);
    }

    const targetCase = await prisma.case.findUnique({
      where: { id },
      include: {
        client: { select: { fullName: true, email: true } },
        judge: { select: { fullName: true, email: true } },
        lawyer: { select: { fullName: true, email: true } },
        court: { select: { name: true, location: true } },
        hearings: { orderBy: { hearingDate: 'asc' } },
        documents: { orderBy: { createdAt: 'desc' } },
        notes: {
          orderBy: { createdAt: 'desc' },
          include: { author: { select: { fullName: true, role: true } } },
        },
      },
    });

    if (!targetCase) {
      return res.status(404).json({ message: 'Case not found' });
    }

    // Role-based Access Control Check
    if (req.user?.role === 'CLIENT' && targetCase.clientId !== req.user.userId) {
      return res.status(403).json({ message: 'Access denied' });
    }
    if (req.user?.role === 'JUDGE' && targetCase.judgeId !== req.user.userId) {
      return res.status(403).json({ message: 'Access denied' });
    }

    await cacheSet(cacheKey, targetCase, 60);

    return res.status(200).json(targetCase);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 4. Update Case
export const updateCase = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const validatedData = updateCaseSchema.parse(req.body);

    const targetCase = await prisma.case.findUnique({ where: { id } });

    if (!targetCase) {
      return res.status(404).json({ message: 'Case not found' });
    }

    // Guard update properties
    const updatedCase = await prisma.case.update({
      where: { id },
      data: {
        ...validatedData,
        closingDate: validatedData.closingDate ? new Date(validatedData.closingDate) : undefined,
      },
    });

    await prisma.auditLog.create({
      data: {
        userId: req.user?.userId,
        action: 'CASE_UPDATE',
        details: `Updated case ${updatedCase.caseNumber} - status: ${updatedCase.status}`,
        ipAddress: req.ip,
      },
    });

    await cacheInvalidatePrefix('cases');

    return res.status(200).json(updatedCase);
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ errors: error.errors });
    }
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 5. Transfer Case
export const transferCase = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const validatedData = transferCaseSchema.parse(req.body);

    const targetCase = await prisma.case.findUnique({ where: { id } });

    if (!targetCase) {
      return res.status(404).json({ message: 'Case not found' });
    }

    const updatedCase = await prisma.case.update({
      where: { id },
      data: {
        courtId: validatedData.courtId,
        judgeId: validatedData.judgeId,
        status: validatedData.judgeId ? CaseStatus.ASSIGNED : CaseStatus.PENDING,
      },
    });

    await prisma.auditLog.create({
      data: {
        userId: req.user?.userId,
        action: 'CASE_TRANSFER',
        details: `Transferred case ${targetCase.caseNumber} to court ${updatedCase.courtId} and judge ${updatedCase.judgeId}`,
        ipAddress: req.ip,
      },
    });

    await cacheInvalidatePrefix('cases');

    return res.status(200).json(updatedCase);
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ errors: error.errors });
    }
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 6. Escalate Case
export const escalateCase = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;

    const targetCase = await prisma.case.findUnique({ where: { id } });

    if (!targetCase) {
      return res.status(404).json({ message: 'Case not found' });
    }

    const updatedCase = await prisma.case.update({
      where: { id },
      data: {
        priority: CasePriority.URGENT,
      },
    });

    await prisma.auditLog.create({
      data: {
        userId: req.user?.userId,
        action: 'CASE_ESCALATE',
        details: `Escalated case ${targetCase.caseNumber} to URGENT priority`,
        ipAddress: req.ip,
      },
    });

    await cacheInvalidatePrefix('cases');

    return res.status(200).json(updatedCase);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 7. Get Case Timeline
export const getCaseTimeline = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;

    const targetCase = await prisma.case.findUnique({
      where: { id },
      include: {
        hearings: true,
        documents: { include: { uploadedBy: { select: { fullName: true } } } },
      },
    });

    if (!targetCase) {
      return res.status(404).json({ message: 'Case not found' });
    }

    // Assemble timeline events dynamically
    const timelineEvents: any[] = [];

    // Case Filing event
    timelineEvents.push({
      date: targetCase.filingDate,
      type: 'FILING',
      title: 'Case Filed',
      description: `Case filed with registration number ${targetCase.caseNumber}`,
    });

    // Hearings
    targetCase.hearings.forEach((hearing) => {
      timelineEvents.push({
        date: hearing.hearingDate,
        type: 'HEARING',
        title: `Hearing Scheduled: ${hearing.status.toLowerCase()}`,
        description: `Hearing at room location/hall. Status is currently ${hearing.status}.`,
      });
    });

    // Documents
    targetCase.documents.forEach((doc) => {
      timelineEvents.push({
        date: doc.createdAt,
        type: 'DOCUMENT',
        title: 'Document Uploaded',
        description: `"${doc.title}" uploaded by ${doc.uploadedBy.fullName}`,
      });
    });

    // Sort by date ascending
    timelineEvents.sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime());

    return res.status(200).json(timelineEvents);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 8. Add Case Note
export const createCaseNote = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params; // caseId
    const { note } = req.body;

    if (!note || typeof note !== 'string' || note.trim().length === 0) {
      return res.status(400).json({ message: 'Note content is required' });
    }

    const targetCase = await prisma.case.findUnique({ where: { id } });
    if (!targetCase) {
      return res.status(404).json({ message: 'Case not found' });
    }

    // Role check: Only case client, assigned judge, lawyer, or admins can comment
    if (req.user?.role === 'CLIENT' && targetCase.clientId !== req.user.userId) {
      return res.status(403).json({ message: 'Access denied to this case' });
    }
    if (req.user?.role === 'JUDGE' && targetCase.judgeId !== req.user.userId) {
      return res.status(403).json({ message: 'Access denied to this case' });
    }
    if (req.user?.role === 'LAWYER' && targetCase.lawyerId !== req.user.userId) {
      return res.status(403).json({ message: 'Access denied to this case' });
    }

    const newNote = await prisma.caseNote.create({
      data: {
        caseId: id,
        userId: req.user!.userId,
        note: note.trim(),
      },
      include: {
        author: {
          select: { fullName: true, role: true },
        },
      },
    });

    await cacheInvalidatePrefix(`cases:details:${id}`);
    await cacheInvalidatePrefix('cases:list');

    return res.status(201).json(newNote);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 9. Delete Case Note
export const deleteCaseNote = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { noteId } = req.params;

    const targetNote = await prisma.caseNote.findUnique({ where: { id: noteId } });
    if (!targetNote) {
      return res.status(404).json({ message: 'Note not found' });
    }

    // Auth check: note author or Admin
    if (targetNote.userId !== req.user?.userId && !['SUPER_ADMIN', 'COURT_ADMIN'].includes(req.user?.role || '')) {
      return res.status(403).json({ message: 'Access denied. You cannot delete this note' });
    }

    await prisma.caseNote.delete({ where: { id: noteId } });

    await cacheInvalidatePrefix(`cases:details:${targetNote.caseId}`);
    await cacheInvalidatePrefix('cases:list');

    return res.status(200).json({ message: 'Note deleted successfully' });
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

