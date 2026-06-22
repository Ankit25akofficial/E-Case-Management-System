import React, { useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Gavel,
  Calendar,
  Clock,
  FileText,
  Upload,
  Download,
  User,
  ArrowLeftRight,
  TrendingUp,
  ChevronLeft,
  Loader2,
  ShieldAlert,
  X,
  AlertCircle,
  Brain,
  Sparkles,
  Trash2,
  Plus,
  CheckSquare,
  Square,
} from 'lucide-react';
import api from '../utils/api';
import { useAuthStore } from '../store/useAuthStore';

interface Document {
  id: string;
  title: string;
  filePath: string;
  fileType: string;
  fileSize: number;
  approvalStatus: 'PENDING' | 'APPROVED' | 'REJECTED';
  createdAt: string;
}

interface Hearing {
  id: string;
  hearingDate: string;
  location: string;
  status: 'SCHEDULED' | 'COMPLETED' | 'POSTPONED' | 'CANCELLED';
  notes: string;
}

interface TimelineEvent {
  date: string;
  type: 'FILING' | 'HEARING' | 'DOCUMENT' | 'ACTION';
  title: string;
  description: string;
}

const CaseDetails: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const { user } = useAuthStore();

  const [activeTab, setActiveTab] = useState<'details' | 'timeline' | 'documents' | 'hearings' | 'notes' | 'tasks'>('details');
  const [isUploadModalOpen, setIsUploadModalOpen] = useState(false);
  const [isTransferModalOpen, setIsTransferModalOpen] = useState(false);

  // Document Form state
  const [docTitle, setDocTitle] = useState('');
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [uploadError, setUploadError] = useState<string | null>(null);
  const [isUploading, setIsUploading] = useState(false);

  // Transfer Form state
  const [transferCourtId, setTransferCourtId] = useState('');
  const [transferJudgeId, setTransferJudgeId] = useState('');
  const [transferError, setTransferError] = useState<string | null>(null);

  // Note form state
  const [newNoteText, setNewNoteText] = useState('');

  // Task form state
  const [newTaskTitle, setNewTaskTitle] = useState('');

  // AI Assistant state
  const [aiResult, setAiResult] = useState<string | null>(null);
  const [aiModelUsed, setAiModelUsed] = useState<string | null>(null);
  const [isAiLoading, setIsAiLoading] = useState(false);
  const [aiErrorText, setAiErrorText] = useState<string | null>(null);


  // 1. Fetch Case Details
  const { data: targetCase, isLoading, error } = useQuery({
    queryKey: ['case', id],
    queryFn: async () => {
      const res = await api.get(`/cases/${id}`);
      return res.data;
    },
  });

  // 2. Fetch Case Timeline
  const { data: timelineData } = useQuery<TimelineEvent[]>({
    queryKey: ['case-timeline', id],
    queryFn: async () => {
      const res = await api.get(`/cases/${id}/timeline`);
      return res.data;
    },
    enabled: !!targetCase,
  });

  // 3. Document upload handler
  const handleDocUpload = async (e: React.FormEvent) => {
    e.preventDefault();
    setUploadError(null);

    if (!docTitle || !selectedFile) {
      setUploadError('Document title and file are required');
      return;
    }

    setIsUploading(true);
    const formData = new FormData();
    formData.append('file', selectedFile);
    formData.append('caseId', id || '');
    formData.append('title', docTitle);

    try {
      await api.post('/documents/upload', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      queryClient.invalidateQueries({ queryKey: ['case', id] });
      queryClient.invalidateQueries({ queryKey: ['case-timeline', id] });
      setIsUploadModalOpen(false);
      setDocTitle('');
      setSelectedFile(null);
    } catch (err: any) {
      setUploadError(err.response?.data?.message || 'Upload failed');
    } finally {
      setIsUploading(false);
    }
  };

  // 4. Case Escalation Mutator
  const escalateMutation = useMutation({
    mutationFn: async () => {
      const res = await api.post(`/cases/${id}/escalate`);
      return res.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['case', id] });
      queryClient.invalidateQueries({ queryKey: ['case-timeline', id] });
    },
  });

  // 5. Transfer Case Mutator
  const transferMutation = useMutation({
    mutationFn: async (transferPayload: { courtId: string; judgeId?: string }) => {
      const res = await api.post(`/cases/${id}/transfer`, transferPayload);
      return res.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['case', id] });
      queryClient.invalidateQueries({ queryKey: ['case-timeline', id] });
      setIsTransferModalOpen(false);
    },
    onError: (err: any) => {
      setTransferError(err.response?.data?.message || 'Transfer failed');
    },
  });

  const handleTransferSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setTransferError(null);

    if (!transferCourtId) {
      setTransferError('Court ID is required');
      return;
    }

    transferMutation.mutate({
      courtId: transferCourtId,
      judgeId: transferJudgeId || undefined,
    });
  };

  // Note creation mutation
  const addNoteMutation = useMutation({
    mutationFn: async (note: string) => {
      const res = await api.post(`/cases/${id}/notes`, { note });
      return res.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['case', id] });
      queryClient.invalidateQueries({ queryKey: ['case-timeline', id] });
      setNewNoteText('');
    },
  });

  // Note deletion mutation
  const deleteNoteMutation = useMutation({
    mutationFn: async (noteId: string) => {
      const res = await api.delete(`/cases/notes/${noteId}`);
      return res.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['case', id] });
      queryClient.invalidateQueries({ queryKey: ['case-timeline', id] });
    },
  });

  const handleAddNote = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newNoteText.trim()) return;
    addNoteMutation.mutate(newNoteText.trim());
  };

  // Fetch case-specific tasks
  const { data: caseTasks } = useQuery<any[]>({
    queryKey: ['case-tasks', id],
    queryFn: async () => {
      const res = await api.get(`/tasks?caseId=${id}`);
      return res.data;
    },
    enabled: !!id,
  });

  // Create case-specific task
  const createCaseTaskMutation = useMutation({
    mutationFn: async (title: string) => {
      const res = await api.post('/tasks', { title, caseId: id });
      return res.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['case-tasks', id] });
      setNewTaskTitle('');
    },
  });

  // Toggle case-specific task
  const toggleCaseTaskMutation = useMutation({
    mutationFn: async ({ taskId, isCompleted }: { taskId: string; isCompleted: boolean }) => {
      const res = await api.put(`/tasks/${taskId}`, { isCompleted });
      return res.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['case-tasks', id] });
    },
  });

  // Delete case-specific task
  const deleteCaseTaskMutation = useMutation({
    mutationFn: async (taskId: string) => {
      const res = await api.delete(`/tasks/${taskId}`);
      return res.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['case-tasks', id] });
    },
  });

  const handleAddCaseTask = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newTaskTitle.trim()) return;
    createCaseTaskMutation.mutate(newTaskTitle.trim());
  };

  // AI assistant handlers
  const handleGenerateSummary = async () => {
    setIsAiLoading(true);
    setAiErrorText(null);
    setAiResult(null);
    try {
      const res = await api.post('/ai/summarize-case', { caseId: id });
      setAiResult(res.data.summary);
      setAiModelUsed(res.data.model);
    } catch (err: any) {
      setAiErrorText(err.response?.data?.message || 'Failed to generate summary');
    } finally {
      setIsAiLoading(false);
    }
  };

  const handleGenerateInsights = async () => {
    setIsAiLoading(true);
    setAiErrorText(null);
    setAiResult(null);
    try {
      const res = await api.post('/ai/legal-insights', { caseId: id });
      setAiResult(res.data.insights);
      setAiModelUsed(res.data.model);
    } catch (err: any) {
      setAiErrorText(err.response?.data?.message || 'Failed to generate insights');
    } finally {
      setIsAiLoading(false);
    }
  };


  const downloadFile = (docId: string, docTitle: string, fileType: string) => {
    // Standard file download trigger from backend endpoint
    const url = `${api.defaults.baseURL}/documents/${docId}/download`;
    // Trigger download
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `${docTitle}.${fileType}`);
    document.body.appendChild(link);
    link.click();
    link.parentNode?.removeChild(link);
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-slate-50 flex items-center justify-center gap-3">
        <Loader2 className="w-8 h-8 text-court-600 animate-spin" />
        <span className="text-sm font-bold text-gray-500">Querying registry databases...</span>
      </div>
    );
  }

  if (error || !targetCase) {
    return (
      <div className="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-4 text-center">
        <ShieldAlert className="w-12 h-12 text-red-500 mb-2" />
        <h2 className="text-xl font-bold text-gray-800">Access Denied or Case Not Found</h2>
        <button
          onClick={() => navigate('/dashboard')}
          className="mt-4 px-4 py-2 bg-court-700 text-white font-bold rounded-xl text-sm"
        >
          Return to Dashboard
        </button>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-slate-50 flex flex-col">
      {/* Top Breadcrumb Nav */}
      <div className="bg-white border-b border-gray-100 py-4 px-6 flex items-center gap-4 shrink-0 shadow-sm">
        <button
          onClick={() => navigate('/cases')}
          className="p-1.5 hover:bg-gray-100 rounded-lg text-gray-500 hover:text-gray-700 transition-colors"
        >
          <ChevronLeft className="w-5 h-5" />
        </button>
        <div className="h-6 w-px bg-gray-200" />
        <div>
          <h2 className="font-black text-gray-800 text-lg flex items-center gap-2">
            <span>{targetCase.caseNumber}</span>
            <span
              className={`px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider ${
                targetCase.status === 'CLOSED'
                  ? 'bg-emerald-50 text-emerald-700'
                  : 'bg-blue-50 text-blue-700'
              }`}
            >
              {targetCase.status}
            </span>
          </h2>
          <p className="text-xs text-gray-400 font-semibold">{targetCase.title}</p>
        </div>
      </div>

      {/* Main View Grid */}
      <div className="flex-1 p-6 md:p-8 grid grid-cols-1 lg:grid-cols-3 gap-8 overflow-y-auto">
        {/* Left Side: Navigation / Tab Content */}
        <div className="lg:col-span-2 flex flex-col gap-6">
          {/* Navigation Tabs */}
          <div className="bg-white p-1.5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-2 overflow-x-auto">
            {(['details', 'timeline', 'documents', 'hearings', 'notes', 'tasks'] as const).map((tab) => (
              <button
                key={tab}
                onClick={() => setActiveTab(tab)}
                className={`flex-1 py-2 px-4 rounded-lg font-bold text-sm capitalize transition-all shrink-0 ${
                  activeTab === tab
                    ? 'bg-court-950 text-white shadow-md'
                    : 'text-gray-500 hover:text-gray-800 hover:bg-slate-50'
                }`}
              >
                {tab}
              </button>
            ))}
          </div>

          {/* Tab Display Screens */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex-1 min-h-[300px]">
            {/* Details Tab */}
            {activeTab === 'details' && (
              <div className="space-y-6">
                <div>
                  <h3 className="font-bold text-gray-800 text-lg border-b border-gray-100 pb-3">Suit Description</h3>
                  <p className="text-gray-600 text-sm mt-3 leading-relaxed whitespace-pre-line font-medium">
                    {targetCase.description || 'No claims summary submitted at filing.'}
                  </p>
                </div>

                <div className="grid grid-cols-2 gap-6 pt-4">
                  <div>
                    <span className="text-xs font-bold text-gray-400 uppercase tracking-wider block">Category Type</span>
                    <span className="font-bold text-sm text-gray-800 block mt-1">{targetCase.category}</span>
                  </div>
                  <div>
                    <span className="text-xs font-bold text-gray-400 uppercase tracking-wider block">Filing Priority</span>
                    <span className="font-bold text-sm text-gray-800 block mt-1">{targetCase.priority}</span>
                  </div>
                </div>
              </div>
            )}

            {/* Timeline Tab */}
            {activeTab === 'timeline' && (
              <div className="space-y-6">
                <h3 className="font-bold text-gray-800 text-lg border-b border-gray-100 pb-3">Case History Log</h3>
                
                <div className="relative border-l-2 border-gray-100 ml-4 pl-6 space-y-6 mt-4">
                  {timelineData?.map((event, idx) => (
                    <div key={idx} className="relative">
                      {/* Timeline Dot */}
                      <div className="absolute top-1.5 -left-[31px] w-4.5 h-4.5 rounded-full bg-white border-2 border-court-500 flex items-center justify-center shadow-sm">
                        <div className="w-1.5 h-1.5 rounded-full bg-court-500" />
                      </div>
                      <div>
                        <span className="text-[10px] font-bold text-gray-400 tracking-wider">
                          {new Date(event.date).toLocaleDateString('en-IN', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                          })}
                        </span>
                        <h4 className="font-bold text-sm text-gray-800 mt-0.5">{event.title}</h4>
                        <p className="text-xs text-gray-500 font-semibold mt-1">{event.description}</p>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Documents Tab */}
            {activeTab === 'documents' && (
              <div className="space-y-6">
                <div className="flex items-center justify-between border-b border-gray-100 pb-3">
                  <h3 className="font-bold text-gray-800 text-lg">Case Files & Submissions</h3>
                  <button
                    onClick={() => setIsUploadModalOpen(true)}
                    className="py-1.5 px-3 bg-court-50 hover:bg-court-100 text-court-700 font-bold rounded-lg text-xs flex items-center gap-1.5 transition-colors"
                  >
                    <Upload className="w-3.5 h-3.5" />
                    <span>Upload Document</span>
                  </button>
                </div>

                <div className="divide-y divide-gray-50 mt-4">
                  {targetCase.documents.map((doc: Document) => (
                    <div key={doc.id} className="py-4 flex items-center justify-between gap-4">
                      <div className="flex items-start gap-3">
                        <div className="p-2.5 rounded-xl bg-blue-50 text-blue-600 shrink-0 mt-0.5">
                          <FileText className="w-5 h-5" />
                        </div>
                        <div>
                          <span className="font-bold text-sm text-gray-800 block">{doc.title}</span>
                          <span className="text-xs text-gray-400 font-semibold block mt-0.5 uppercase">
                            {doc.fileType} | {(doc.fileSize / 1024 / 1024).toFixed(2)} MB
                          </span>
                        </div>
                      </div>
                      <button
                        onClick={() => downloadFile(doc.id, doc.title, doc.fileType)}
                        className="p-2 hover:bg-gray-50 rounded-xl text-court-600 hover:text-court-800 transition-colors shrink-0"
                      >
                        <Download className="w-5 h-5" />
                      </button>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Hearings Tab */}
            {activeTab === 'hearings' && (
              <div className="space-y-6">
                <h3 className="font-bold text-gray-800 text-lg border-b border-gray-100 pb-3">Scheduled Hearings</h3>
                
                <div className="space-y-4 mt-4">
                  {targetCase.hearings.map((h: Hearing) => (
                    <div key={h.id} className="p-4 rounded-xl border border-gray-100 bg-slate-50/50 flex gap-4">
                      <div className="p-3 bg-court-50 text-court-700 rounded-xl flex items-center justify-center shrink-0">
                        <Calendar className="w-6 h-6" />
                      </div>
                      <div>
                        <span className="font-bold text-sm text-gray-800 block">
                          {new Date(h.hearingDate).toLocaleDateString('en-IN', {
                            weekday: 'long',
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                          })}
                        </span>
                        <span className="text-xs text-gray-500 font-semibold mt-1 block">
                          Court Hall: {h.location}
                        </span>
                        <span className="px-2 py-0.5 text-[9px] font-bold bg-amber-100 text-amber-800 rounded-full inline-block mt-2">
                          {h.status}
                        </span>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Notes Tab */}
            {activeTab === 'notes' && (
              <div className="space-y-6">
                <h3 className="font-bold text-gray-800 text-lg border-b border-gray-100 pb-3">Case Notes & Remarks</h3>

                {/* Add Note Form */}
                <form onSubmit={handleAddNote} className="space-y-3 mt-4">
                  <textarea
                    value={newNoteText}
                    onChange={(e) => setNewNoteText(e.target.value)}
                    placeholder="Type an official remark or note for this case..."
                    rows={3}
                    className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-court-500 font-medium"
                    required
                  />
                  <div className="flex justify-end">
                    <button
                      type="submit"
                      disabled={addNoteMutation.isPending}
                      className="py-2 px-4 bg-court-700 hover:bg-court-800 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors"
                    >
                      {addNoteMutation.isPending ? <Loader2 className="w-3.5 h-3.5 animate-spin" /> : <Plus className="w-3.5 h-3.5" />}
                      <span>Add Case Note</span>
                    </button>
                  </div>
                </form>

                {/* Notes Feed */}
                <div className="space-y-4 mt-6">
                  {targetCase.notes && targetCase.notes.length > 0 ? (
                    targetCase.notes.map((note: any) => (
                      <div key={note.id} className="p-4 rounded-xl border border-gray-100 bg-slate-50/30 flex justify-between gap-4 animate-fade-in">
                        <div className="space-y-1">
                          <div className="flex items-center gap-2">
                            <span className="font-bold text-sm text-gray-800">{note.author.fullName}</span>
                            <span className="px-2 py-0.5 text-[9px] font-bold rounded bg-court-50 text-court-700 uppercase">
                              {note.author.role.replace('_', ' ')}
                            </span>
                            <span className="text-[10px] font-semibold text-gray-400">
                              {new Date(note.createdAt).toLocaleDateString('en-IN', {
                                day: 'numeric',
                                month: 'short',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                              })}
                            </span>
                          </div>
                          <p className="text-sm text-gray-650 font-medium whitespace-pre-line leading-relaxed">
                            {note.note}
                          </p>
                        </div>
                        {(note.userId === user?.id || ['SUPER_ADMIN', 'COURT_ADMIN'].includes(user?.role || '')) && (
                          <button
                            onClick={() => deleteNoteMutation.mutate(note.id)}
                            className="p-1.5 hover:bg-red-50 text-gray-400 hover:text-red-600 rounded-lg transition-colors shrink-0 self-start"
                          >
                            <Trash2 className="w-4 h-4" />
                          </button>
                        )}
                      </div>
                    ))
                  ) : (
                    <p className="text-sm text-gray-400 italic text-center py-6 font-medium">No notes recorded for this case registry.</p>
                  )}
                </div>
              </div>
            )}

            {/* Tasks Tab */}
            {activeTab === 'tasks' && (
              <div className="space-y-6">
                <h3 className="font-bold text-gray-800 text-lg border-b border-gray-100 pb-3">Case Follow-Up Tasks</h3>

                {/* Add Task Form */}
                <form onSubmit={handleAddCaseTask} className="flex gap-2 mt-4">
                  <input
                    type="text"
                    value={newTaskTitle}
                    onChange={(e) => setNewTaskTitle(e.target.value)}
                    placeholder="New case duty/task..."
                    className="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-1 focus:ring-court-500"
                    required
                  />
                  <button
                    type="submit"
                    disabled={createCaseTaskMutation.isPending}
                    className="px-4 py-2 bg-court-700 hover:bg-court-800 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shrink-0"
                  >
                    {createCaseTaskMutation.isPending ? <Loader2 className="w-3.5 h-3.5 animate-spin" /> : <Plus className="w-3.5 h-3.5" />}
                    <span>Add Task</span>
                  </button>
                </form>

                {/* Tasks List */}
                <div className="space-y-2 mt-6">
                  {caseTasks && caseTasks.length > 0 ? (
                    caseTasks.map((task: any) => (
                      <div key={task.id} className="flex items-center justify-between gap-3 p-3 rounded-xl hover:bg-slate-50 border border-gray-100 transition-colors animate-fade-in">
                        <button
                          onClick={() => toggleCaseTaskMutation.mutate({ taskId: task.id, isCompleted: !task.isCompleted })}
                          className="flex items-center gap-3 text-left flex-1"
                        >
                          {task.isCompleted ? (
                            <CheckSquare className="w-5 h-5 text-emerald-600 shrink-0" />
                          ) : (
                            <Square className="w-5 h-5 text-gray-400 shrink-0" />
                          )}
                          <span className={`text-sm font-bold ${task.isCompleted ? 'text-gray-400 line-through font-medium' : 'text-gray-700'}`}>
                            {task.title}
                          </span>
                        </button>
                        <button
                          onClick={() => deleteCaseTaskMutation.mutate(task.id)}
                          className="p-1.5 hover:bg-red-50 hover:text-red-600 rounded-lg text-gray-400 transition-colors shrink-0"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    ))
                  ) : (
                    <p className="text-sm text-gray-400 italic text-center py-6 font-medium">No tasks logged for this case.</p>
                  )}
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Right Side: Case Metadata / Actions Panel */}
        <div className="flex flex-col gap-6">
          {/* Metadata Card */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
            <h3 className="font-bold text-gray-800 text-lg border-b border-gray-50 pb-3">Registry Information</h3>
            
            <div className="space-y-4">
              {/* Client */}
              <div className="flex items-center gap-3">
                <div className="p-2 bg-slate-50 rounded-lg text-gray-500 shrink-0">
                  <User className="w-5 h-5" />
                </div>
                <div>
                  <span className="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Filing Client</span>
                  <span className="font-bold text-sm text-gray-800 block mt-0.5">{targetCase.client.fullName}</span>
                </div>
              </div>

              {/* Judge */}
              <div className="flex items-center gap-3">
                <div className="p-2 bg-slate-50 rounded-lg text-gray-500 shrink-0">
                  <Gavel className="w-5 h-5" />
                </div>
                <div>
                  <span className="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Presiding Judge</span>
                  <span className="font-bold text-sm text-gray-800 block mt-0.5">
                    {targetCase.judge?.fullName || 'Registry Pending Assignment'}
                  </span>
                </div>
              </div>

              {/* Court Location */}
              <div className="flex items-center gap-3">
                <div className="p-2 bg-slate-50 rounded-lg text-gray-500 shrink-0">
                  <Clock className="w-5 h-5" />
                </div>
                <div>
                  <span className="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Filing Registry</span>
                  <span className="font-bold text-sm text-gray-800 block mt-0.5">{targetCase.court.name}</span>
                </div>
              </div>
            </div>
          </div>

          {/* Action buttons */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
            <h3 className="font-bold text-gray-800 text-lg border-b border-gray-50 pb-3">Case Actions</h3>

            {/* Client Escalation */}
            {user?.role === 'CLIENT' && targetCase.priority !== 'URGENT' && (
              <button
                onClick={() => escalateMutation.mutate()}
                disabled={escalateMutation.isPending}
                className="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 rounded-xl font-bold text-sm transition-all"
              >
                {escalateMutation.isPending ? (
                  <Loader2 className="w-4.5 h-4.5 animate-spin" />
                ) : (
                  <>
                    <TrendingUp className="w-4.5 h-4.5" />
                    <span>Escalate to Urgent</span>
                  </>
                )}
              </button>
            )}

            {/* Admin Transfer Case */}
            {(user?.role === 'SUPER_ADMIN' || user?.role === 'COURT_ADMIN') && (
              <button
                onClick={() => setIsTransferModalOpen(true)}
                className="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-court-50 hover:bg-court-100 text-court-700 border border-court-200 rounded-xl font-bold text-sm transition-all"
              >
                <ArrowLeftRight className="w-4.5 h-4.5" />
                <span>Transfer / Reassign Case</span>
              </button>
            )}
          </div>

          {/* AI Case Intelligence Card */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
            <div className="flex items-center gap-2 border-b border-gray-50 pb-3">
              <Brain className="w-5 h-5 text-indigo-600" />
              <h3 className="font-bold text-gray-800 text-lg">AI Assistant</h3>
            </div>

            <div className="grid grid-cols-2 gap-2">
              <button
                onClick={handleGenerateSummary}
                disabled={isAiLoading}
                className="flex items-center justify-center gap-1.5 py-2 px-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl font-bold text-xs transition-all disabled:opacity-50"
              >
                <Sparkles className="w-3.5 h-3.5" />
                <span>Summarize</span>
              </button>

              <button
                onClick={handleGenerateInsights}
                disabled={isAiLoading || ['CLIENT'].includes(user?.role || '')}
                className="flex items-center justify-center gap-1.5 py-2 px-3 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-xl font-bold text-xs transition-all disabled:opacity-50"
              >
                <Brain className="w-3.5 h-3.5" />
                <span>Analyze Case</span>
              </button>
            </div>

            {/* AI Results Output Area */}
            {isAiLoading && (
              <div className="flex flex-col items-center justify-center py-6 gap-2 border border-dashed border-gray-100 rounded-xl bg-slate-50/50 animate-pulse">
                <Loader2 className="w-6 h-6 text-indigo-600 animate-spin" />
                <span className="text-xs font-bold text-gray-400">AI is synthesis compiling...</span>
              </div>
            )}

            {aiErrorText && (
              <div className="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded-xl flex gap-2 text-xs">
                <AlertCircle className="w-4 h-4 shrink-0" />
                <span className="font-semibold">{aiErrorText}</span>
              </div>
            )}

            {aiResult && !isAiLoading && (
              <div className="border border-indigo-100/60 bg-indigo-50/20 p-4 rounded-xl space-y-3 animate-fade-in">
                <div className="flex items-center justify-between border-b border-indigo-100/40 pb-2">
                  <span className="text-[10px] font-bold text-indigo-700 uppercase tracking-wide">Analysis Report</span>
                  <span className="text-[9px] font-bold px-1.5 py-0.5 bg-indigo-100/50 text-indigo-800 rounded">
                    {aiModelUsed}
                  </span>
                </div>
                <div className="text-xs text-gray-650 font-medium whitespace-pre-wrap leading-relaxed max-h-80 overflow-y-auto pr-1">
                  {aiResult}
                </div>
              </div>
            )}
          </div>
        </div>
      </div>


      {/* Document Upload Modal */}
      {isUploadModalOpen && (
        <div className="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-md bg-white rounded-2xl shadow-xl p-6 border border-gray-100 animate-scale-in">
            <div className="flex items-center justify-between pb-4 border-b border-gray-100">
              <h3 className="text-lg font-bold text-gray-800">Upload Case File</h3>
              <button onClick={() => setIsUploadModalOpen(false)} className="text-gray-400 hover:text-gray-600">
                <X className="w-5 h-5" />
              </button>
            </div>

            {uploadError && (
              <div className="mt-3 bg-red-50 border-l-4 border-red-500 text-red-700 p-2.5 rounded-lg flex gap-2 text-xs">
                <AlertCircle className="w-4.5 h-4.5 shrink-0" />
                <span>{uploadError}</span>
              </div>
            )}

            <form onSubmit={handleDocUpload} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Document Title</label>
                <input
                  type="text"
                  value={docTitle}
                  onChange={(e) => setDocTitle(e.target.value)}
                  className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-court-500"
                  placeholder="e.g. Evidence Affidavit"
                  required
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Select File</label>
                <input
                  type="file"
                  onChange={(e) => setSelectedFile(e.target.files ? e.target.files[0] : null)}
                  className="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-court-50 file:text-court-700 hover:file:bg-court-100"
                  required
                />
              </div>

              <div className="pt-4 flex justify-end gap-2">
                <button
                  type="button"
                  onClick={() => setIsUploadModalOpen(false)}
                  className="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={isUploading}
                  className="px-4 py-2 bg-court-700 hover:bg-court-800 text-white rounded-xl text-xs font-bold flex items-center gap-1.5"
                >
                  {isUploading ? <Loader2 className="w-4 h-4 animate-spin" /> : <span>Upload File</span>}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Transfer / Reassign Modal */}
      {isTransferModalOpen && (
        <div className="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-md bg-white rounded-2xl shadow-xl p-6 border border-gray-100 animate-scale-in">
            <div className="flex items-center justify-between pb-4 border-b border-gray-100">
              <h3 className="text-lg font-bold text-gray-800">Reassign Case File</h3>
              <button onClick={() => setIsTransferModalOpen(false)} className="text-gray-400 hover:text-gray-600">
                <X className="w-5 h-5" />
              </button>
            </div>

            {transferError && (
              <div className="mt-3 bg-red-50 border-l-4 border-red-500 text-red-700 p-2.5 rounded-lg flex gap-2 text-xs">
                <AlertCircle className="w-4.5 h-4.5 shrink-0" />
                <span>{transferError}</span>
              </div>
            )}

            <form onSubmit={handleTransferSubmit} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Target Court (UUID)</label>
                <input
                  type="text"
                  value={transferCourtId}
                  onChange={(e) => setTransferCourtId(e.target.value)}
                  className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-court-500"
                  placeholder="Enter court uuid string"
                  required
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Judge ID (UUID, Optional)</label>
                <input
                  type="text"
                  value={transferJudgeId}
                  onChange={(e) => setTransferJudgeId(e.target.value)}
                  className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-court-500"
                  placeholder="Enter judge uuid or leave blank"
                />
              </div>

              <div className="pt-4 flex justify-end gap-2">
                <button
                  type="button"
                  onClick={() => setIsTransferModalOpen(false)}
                  className="px-4 py-2 bg-gray-100 hover:bg-gray-250 text-gray-700 rounded-xl text-xs font-bold"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={transferMutation.isPending}
                  className="px-4 py-2 bg-court-700 hover:bg-court-800 text-white rounded-xl text-xs font-bold flex items-center gap-1.5"
                >
                  {transferMutation.isPending ? (
                    <Loader2 className="w-4 h-4 animate-spin" />
                  ) : (
                    <span>Confirm Assignment</span>
                  )}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default CaseDetails;
