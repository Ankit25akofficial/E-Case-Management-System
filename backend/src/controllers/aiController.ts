import { Response } from 'express';
import prisma from '../db';
import { AuthenticatedRequest } from '../middleware/auth';

// Helper function to query Gemini API
const callGeminiAPI = async (prompt: string): Promise<string> => {
  const apiKey = process.env.GEMINI_API_KEY;
  if (!apiKey) {
    throw new Error('GEMINI_API_KEY is not configured');
  }

  const url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=${apiKey}`;
  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      contents: [
        {
          parts: [
            {
              text: prompt,
            },
          ],
        },
      ],
    }),
  });

  if (!response.ok) {
    const errorText = await response.text();
    throw new Error(`Gemini API request failed: ${response.status} - ${errorText}`);
  }

  const data = await response.json() as any;
  const generatedText = data?.candidates?.[0]?.content?.parts?.[0]?.text;
  if (!generatedText) {
    throw new Error('Invalid response structure received from Gemini API');
  }

  return generatedText;
};

// Generates fallback summaries when AI key is missing
const generateCaseSummaryFallback = (targetCase: any): string => {
  const notesText = targetCase.notes.length > 0 
    ? targetCase.notes.map((n: any) => `- [${n.author.fullName} (${n.author.role})]: "${n.note}"`).join('\n')
    : 'No notes added.';

  const hearingsText = targetCase.hearings.length > 0
    ? targetCase.hearings.map((h: any) => `- Date: ${new Date(h.hearingDate).toLocaleDateString()}, Status: ${h.status}, Location: ${h.location}`).join('\n')
    : 'No hearings scheduled.';

  return `### ⚖️ CASE SUMMARY BRIEF (SIMULATED AI OVERVIEW)
**Case Number:** ${targetCase.caseNumber}
**Title:** ${targetCase.title}
**Category:** ${targetCase.category} | **Status:** ${targetCase.status} | **Priority:** ${targetCase.priority}

---

#### 1. Executive Summary
This case was filed on **${new Date(targetCase.filingDate).toLocaleDateString()}**.
* **Client:** ${targetCase.client.fullName}
* **Presiding Judge:** ${targetCase.judge?.fullName || 'Unassigned'}
* **Legal Counsel:** ${targetCase.lawyer?.fullName || 'Not Retained'}
* **Court Branch:** ${targetCase.court.name} (${targetCase.court.location})

#### 2. Hearings Status
${hearingsText}

#### 3. Administrative Notes Log
${notesText}

---
*Note: This is a structured fallback summary generated locally because GEMINI_API_KEY is not configured in the backend environment. Configure the key to enable live LLM synthesis.*`;
};

const generateLegalInsightsFallback = (targetCase: any): string => {
  const isUrgent = targetCase.priority === 'URGENT' || targetCase.priority === 'HIGH';
  const hasNotes = targetCase.notes.length > 0;
  const isAssigned = !!targetCase.judgeId;

  return `### 🧠 LEGAL INSIGHTS & STRATEGIC RECOMMENDATIONS (SIMULATED)
**Analysis for Case Number:** ${targetCase.caseNumber}

---

#### 1. Risk Assessment
* **Urgency Rating:** ${targetCase.priority}. ${isUrgent ? '⚠️ High risk of scheduling lag due to prioritized case classification. Ensure active hearings are set.' : 'Standard operational case priority.'}
* **Judicial Assignment:** ${isAssigned ? `Assigned to Judge ${targetCase.judge?.fullName}.` : '❌ Warning: Case lacks a presiding judge. Reassignment or court admin action required.'}
* **Documentation Level:** ${targetCase.documents.length} files uploaded.

#### 2. Strategic Action Items
* **For Clerks:** ${!isAssigned ? 'Flag case for immediate judicial assignment to clear pending status.' : 'Ensure hearing schedules match court room calendars.'}
* **For Lawyers/Counsel:** ${hasNotes ? 'Review case notes to prepare response briefings.' : 'Submit initial evidence and witness lists.'}
* **For Judge:** ${targetCase.hearings.length === 0 ? 'Schedule preliminary hearing to frame issues.' : 'Review approval queue for outstanding evidence documents.'}

---
*Note: This is a structured fallback analysis generated locally because GEMINI_API_KEY is not configured in the backend environment.*`;
};

// 1. Summarize Case
export const summarizeCase = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { caseId } = req.body;

    if (!caseId) {
      return res.status(400).json({ message: 'caseId is required' });
    }

    const targetCase = await prisma.case.findUnique({
      where: { id: caseId },
      include: {
        client: { select: { fullName: true } },
        judge: { select: { fullName: true } },
        lawyer: { select: { fullName: true } },
        court: { select: { name: true, location: true } },
        hearings: true,
        documents: true,
        notes: {
          orderBy: { createdAt: 'desc' },
          include: { author: { select: { fullName: true, role: true } } },
        },
      },
    });

    if (!targetCase) {
      return res.status(404).json({ message: 'Case not found' });
    }

    // Role access checks
    if (req.user?.role === 'CLIENT' && targetCase.clientId !== req.user.userId) {
      return res.status(403).json({ message: 'Access denied' });
    }
    if (req.user?.role === 'JUDGE' && targetCase.judgeId !== req.user.userId) {
      return res.status(403).json({ message: 'Access denied' });
    }

    const hasApiKey = !!process.env.GEMINI_API_KEY;

    if (!hasApiKey) {
      const simulatedSummary = generateCaseSummaryFallback(targetCase);
      return res.status(200).json({ summary: simulatedSummary, model: 'local-fallback' });
    }

    // Build the AI Prompt
    const notesStr = targetCase.notes.map(n => `[${n.author.fullName} (${n.author.role}) on ${n.createdAt.toISOString()}]: "${n.note}"`).join('\n');
    const hearingsStr = targetCase.hearings.map(h => `- Hearing on ${h.hearingDate.toISOString()} at ${h.location} (Status: ${h.status}, Notes: ${h.notes || 'None'}, Outcome: ${h.outcome || 'None'})`).join('\n');
    const docsStr = targetCase.documents.map(d => `- File: "${d.title}" (${d.fileType}, Size: ${d.fileSize} bytes, Status: ${d.approvalStatus})`).join('\n');

    const prompt = `You are a professional legal AI assistant. Summarize the following case details into a structured judicial summary for court personnel.
    
Case Number: ${targetCase.caseNumber}
Title: ${targetCase.title}
Description: ${targetCase.description || 'No description provided.'}
Category: ${targetCase.category}
Status: ${targetCase.status}
Priority: ${targetCase.priority}
Filing Date: ${targetCase.filingDate.toISOString()}
Court Room: ${targetCase.court.name} (Location: ${targetCase.court.location})
Client: ${targetCase.client.fullName}
Judge: ${targetCase.judge?.fullName || 'Not assigned'}
Lawyer: ${targetCase.lawyer?.fullName || 'Not retained'}

Hearings History:
${hearingsStr || 'No hearings scheduled.'}

Case Notes Feed:
${notesStr || 'No notes added.'}

Documents Inventory:
${docsStr || 'No documents uploaded.'}

Please return a beautiful, highly structured Markdown briefing with the following sections:
1. Executive Briefing (a clear 2-3 sentence overview)
2. Critical Court Entities (Clients, representation, assigned Judge)
3. Hearings & Action Items Log (Chronological review of scheduled sessions and outcomes)
4. Synthesis of Internal Notes (Summary of notes and ongoing discussion issues)`;

    const summary = await callGeminiAPI(prompt);
    return res.status(200).json({ summary, model: 'gemini-1.5-flash' });
  } catch (error: any) {
    console.error('[AI Summarize Case Error]:', error.message || error);
    return res.status(500).json({ message: 'Failed to generate case summary', error: error.message });
  }
};

// 2. Legal Insights
export const getLegalInsights = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { caseId } = req.body;

    if (!caseId) {
      return res.status(400).json({ message: 'caseId is required' });
    }

    const targetCase = await prisma.case.findUnique({
      where: { id: caseId },
      include: {
        client: { select: { fullName: true } },
        judge: { select: { fullName: true } },
        lawyer: { select: { fullName: true } },
        court: { select: { name: true, location: true } },
        hearings: true,
        documents: true,
        notes: {
          orderBy: { createdAt: 'desc' },
          include: { author: { select: { fullName: true, role: true } } },
        },
      },
    });

    if (!targetCase) {
      return res.status(404).json({ message: 'Case not found' });
    }

    // Role access checks
    if (req.user?.role === 'CLIENT' && targetCase.clientId !== req.user.userId) {
      return res.status(403).json({ message: 'Access denied' });
    }
    if (req.user?.role === 'JUDGE' && targetCase.judgeId !== req.user.userId) {
      return res.status(403).json({ message: 'Access denied' });
    }

    const hasApiKey = !!process.env.GEMINI_API_KEY;

    if (!hasApiKey) {
      const simulatedInsights = generateLegalInsightsFallback(targetCase);
      return res.status(200).json({ insights: simulatedInsights, model: 'local-fallback' });
    }

    const notesStr = targetCase.notes.map(n => `[${n.author.fullName} (${n.author.role})]: "${n.note}"`).join('\n');
    const hearingsStr = targetCase.hearings.map(h => `- Hearing on ${h.hearingDate.toISOString()} at ${h.location} (Status: ${h.status}, Outcome: ${h.outcome || 'Pending'})`).join('\n');

    const prompt = `You are a senior judicial legal analyst AI. Conduct a strategic analysis of this court case.

Case Details:
Case Number: ${targetCase.caseNumber}
Title: ${targetCase.title}
Category: ${targetCase.category}
Status: ${targetCase.status}
Priority: ${targetCase.priority}
Judge: ${targetCase.judge?.fullName || 'Not assigned'}
Client: ${targetCase.client.fullName}
Documents Count: ${targetCase.documents.length}

Hearings:
${hearingsStr || 'None'}

Case Notes:
${notesStr || 'None'}

Provide a detailed, structured Markdown brief covering:
1. Operational Risk Assessment (evaluate potential timeline delays, scheduling gridlocks, or lacking personnel)
2. Strategic Directives (specific operational tasks recommended for the Client, the Legal Counsel, and the Presiding Judge)
3. Procedural Checklist (immediate filings or calendar adjustments recommended based on status)`;

    const insights = await callGeminiAPI(prompt);
    return res.status(200).json({ insights, model: 'gemini-1.5-flash' });
  } catch (error: any) {
    console.error('[AI Legal Insights Error]:', error.message || error);
    return res.status(500).json({ message: 'Failed to generate legal insights', error: error.message });
  }
};
