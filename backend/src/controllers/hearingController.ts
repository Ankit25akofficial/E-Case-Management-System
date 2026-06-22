import { Response } from 'express';
import { z } from 'zod';
import prisma from '../db';
import { AuthenticatedRequest } from '../middleware/auth';
import { HearingStatus } from '../types/enums';

// Validation Schemas
const scheduleHearingSchema = z.object({
  caseId: z.string().uuid(),
  courtId: z.string().uuid(),
  hearingDate: z.string().datetime(),
  location: z.string().min(2).max(255),
  notes: z.string().optional(),
});

const rescheduleHearingSchema = z.object({
  hearingDate: z.string().datetime().optional(),
  location: z.string().min(2).max(255).optional(),
  status: z.nativeEnum(HearingStatus).optional(),
  notes: z.string().optional(),
  outcome: z.string().optional(),
});

// Helper: Check Judge Conflict (1-hour block)
const checkJudgeConflict = async (judgeId: string, requestedDateStr: string, currentHearingId?: string): Promise<boolean> => {
  const requestedDate = new Date(requestedDateStr);
  const oneHourBefore = new Date(requestedDate.getTime() - 59 * 60 * 1000);
  const oneHourAfter = new Date(requestedDate.getTime() + 59 * 60 * 1000);

  const overlappingHearing = await prisma.hearing.findFirst({
    where: {
      id: currentHearingId ? { not: currentHearingId } : undefined,
      status: HearingStatus.SCHEDULED,
      hearingDate: {
        gte: oneHourBefore,
        lte: oneHourAfter,
      },
      case: {
        judgeId: judgeId,
      },
    },
  });

  return !!overlappingHearing;
};

// 1. Schedule Hearing
export const scheduleHearing = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const validatedData = scheduleHearingSchema.parse(req.body);

    // Verify case details
    const targetCase = await prisma.case.findUnique({
      where: { id: validatedData.caseId },
      include: { court: true },
    });

    if (!targetCase) {
      return res.status(404).json({ message: 'Case registry file not found' });
    }

    // Check judge availability if a judge is assigned to the case
    if (targetCase.judgeId) {
      const isBusy = await checkJudgeConflict(targetCase.judgeId, validatedData.hearingDate);
      if (isBusy) {
        return res.status(409).json({
          message: 'Scheduling Conflict: The presiding judge is booked for another trial within this 1-hour slot.',
        });
      }
    }

    const hearing = await prisma.hearing.create({
      data: {
        caseId: validatedData.caseId,
        courtId: validatedData.courtId,
        hearingDate: new Date(validatedData.hearingDate),
        location: validatedData.location,
        notes: validatedData.notes || '',
        status: HearingStatus.SCHEDULED,
      },
    });

    // Write audit trail
    await prisma.auditLog.create({
      data: {
        userId: req.user?.userId,
        action: 'HEARING_SCHEDULE',
        details: `Scheduled hearing for case ${targetCase.caseNumber} on ${hearing.hearingDate.toISOString()}`,
        ipAddress: req.ip,
      },
    });

    return res.status(201).json(hearing);
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ errors: error.errors });
    }
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 2. Query Hearings (Calendar feeds)
export const getHearings = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { startDate, endDate } = req.query;

    const filterConditions: any = {};

    // Date range filters
    if (startDate && endDate) {
      filterConditions.hearingDate = {
        gte: new Date(startDate as string),
        lte: new Date(endDate as string),
      };
    }

    // Apply role scoping
    if (req.user?.role === 'CLIENT') {
      filterConditions.case = { clientId: req.user.userId };
    } else if (req.user?.role === 'JUDGE') {
      filterConditions.case = { judgeId: req.user.userId };
    } else if (req.user?.role === 'LAWYER') {
      filterConditions.case = { lawyerId: req.user.userId };
    }

    const hearings = await prisma.hearing.findMany({
      where: filterConditions,
      orderBy: { hearingDate: 'asc' },
      include: {
        case: {
          select: {
            caseNumber: true,
            title: true,
            judge: { select: { fullName: true } },
            client: { select: { fullName: true } },
          },
        },
        court: { select: { name: true } },
      },
    });

    return res.status(200).json(hearings);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 3. Reschedule / Update Hearing
export const rescheduleHearing = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const validatedData = rescheduleHearingSchema.parse(req.body);

    const hearing = await prisma.hearing.findUnique({
      where: { id },
      include: { case: true },
    });

    if (!hearing) {
      return res.status(404).json({ message: 'Hearing schedule not found' });
    }

    // Check judge conflict on date changes
    if (validatedData.hearingDate && hearing.case.judgeId) {
      const isBusy = await checkJudgeConflict(
        hearing.case.judgeId,
        validatedData.hearingDate,
        hearing.id
      );
      if (isBusy) {
        return res.status(409).json({
          message: 'Scheduling Conflict: The presiding judge is booked for another trial within this 1-hour slot.',
        });
      }
    }

    const updatedHearing = await prisma.hearing.update({
      where: { id },
      data: {
        hearingDate: validatedData.hearingDate ? new Date(validatedData.hearingDate) : undefined,
        location: validatedData.location,
        status: validatedData.status,
        notes: validatedData.notes,
        outcome: validatedData.outcome,
      },
    });

    await prisma.auditLog.create({
      data: {
        userId: req.user?.userId,
        action: 'HEARING_RESCHEDULE',
        details: `Updated hearing ${hearing.id} status to ${updatedHearing.status} or changed schedule slots`,
        ipAddress: req.ip,
      },
    });

    return res.status(200).json(updatedHearing);
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ errors: error.errors });
    }
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};
