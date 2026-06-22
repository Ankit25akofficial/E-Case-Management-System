import { Response } from 'express';
import path from 'path';
import fs from 'fs';
import prisma from '../db';
import { AuthenticatedRequest } from '../middleware/auth';
import { DocApprovalStatus } from '../types/enums';

// 1. Upload Document
export const uploadDocument = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { caseId, title } = req.body;
    const file = req.file;

    if (!file) {
      return res.status(400).json({ message: 'No file uploaded' });
    }

    if (!caseId || !title) {
      // Remove temp file if validation fails
      fs.unlinkSync(file.path);
      return res.status(400).json({ message: 'Case ID and document title are required' });
    }

    // Verify case exists
    const targetCase = await prisma.case.findUnique({
      where: { id: caseId },
    });

    if (!targetCase) {
      fs.unlinkSync(file.path);
      return res.status(404).json({ message: 'Case not found' });
    }

    // Create Document record
    const document = await prisma.document.create({
      data: {
        caseId,
        userId: req.user?.userId || '',
        title,
        filePath: file.filename, // Local filename
        fileType: path.extname(file.originalname).substring(1),
        fileSize: file.size,
        approvalStatus: DocApprovalStatus.PENDING,
      },
    });

    // Write audit log
    await prisma.auditLog.create({
      data: {
        userId: req.user?.userId,
        action: 'DOCUMENT_UPLOAD',
        details: `Uploaded document "${document.title}" for case ${targetCase.caseNumber}`,
        ipAddress: req.ip,
      },
    });

    return res.status(201).json(document);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 2. Download Document
export const downloadDocument = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;

    const document = await prisma.document.findUnique({
      where: { id },
      include: { case: true },
    });

    if (!document) {
      return res.status(404).json({ message: 'Document not found' });
    }

    // Access control check
    if (req.user?.role === 'CLIENT' && document.case.clientId !== req.user.userId) {
      return res.status(403).json({ message: 'Access denied' });
    }
    if (req.user?.role === 'JUDGE' && document.case.judgeId !== req.user.userId) {
      return res.status(403).json({ message: 'Access denied' });
    }

    const fileDirectory = path.join(__dirname, '../../../uploads');
    const fullPath = path.join(fileDirectory, document.filePath);

    if (!fs.existsSync(fullPath)) {
      return res.status(404).json({ message: 'Physical file not found on server storage' });
    }

    return res.download(fullPath, `${document.title}.${document.fileType}`);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 3. Approve / Reject Document
export const approveDocument = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const { status } = req.body; // PENDING, APPROVED, REJECTED

    if (!status || !Object.values(DocApprovalStatus).includes(status)) {
      return res.status(400).json({ message: 'Invalid approval status parameter' });
    }

    const document = await prisma.document.findUnique({
      where: { id },
      include: { case: true },
    });

    if (!document) {
      return res.status(404).json({ message: 'Document not found' });
    }

    const updatedDocument = await prisma.document.update({
      where: { id },
      data: {
        approvalStatus: status as DocApprovalStatus,
      },
    });

    await prisma.auditLog.create({
      data: {
        userId: req.user?.userId,
        action: 'DOCUMENT_APPROVE',
        details: `Set status of document "${document.title}" (Case: ${document.case.caseNumber}) to ${status}`,
        ipAddress: req.ip,
      },
    });

    return res.status(200).json(updatedDocument);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 4. Digitally Sign Document
export const signDocument = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const { signatureHash } = req.body;

    if (!signatureHash) {
      return res.status(400).json({ message: 'Signature hash is required' });
    }

    const document = await prisma.document.findUnique({
      where: { id },
      include: { case: true },
    });

    if (!document) {
      return res.status(404).json({ message: 'Document not found' });
    }

    const updatedDocument = await prisma.document.update({
      where: { id },
      data: {
        digitalSignature: signatureHash,
        approvalStatus: DocApprovalStatus.APPROVED, // Signing implies approval in court filing
      },
    });

    await prisma.auditLog.create({
      data: {
        userId: req.user?.userId,
        action: 'DOCUMENT_SIGN',
        details: `Digitally signed document "${document.title}" (Case: ${document.case.caseNumber})`,
        ipAddress: req.ip,
      },
    });

    return res.status(200).json(updatedDocument);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};

// 5. Get All Documents (filtered by role access)
export const getDocuments = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const whereClause: any = {};
    
    if (req.user?.role === 'CLIENT') {
      whereClause.case = { clientId: req.user.userId };
    } else if (req.user?.role === 'LAWYER') {
      whereClause.case = { lawyerId: req.user.userId };
    } else if (req.user?.role === 'JUDGE') {
      whereClause.case = { judgeId: req.user.userId };
    }

    const documents = await prisma.document.findMany({
      where: whereClause,
      include: {
        case: {
          select: {
            caseNumber: true,
            title: true,
          },
        },
        uploadedBy: {
          select: {
            fullName: true,
            role: true,
          },
        },
      },
      orderBy: {
        createdAt: 'desc',
      },
    });

    return res.status(200).json(documents);
  } catch (error) {
    console.error(error);
    return res.status(500).json({ message: 'Internal server error' });
  }
};
