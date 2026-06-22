import React, { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  FileText,
  Download,
  Search,
  Filter,
  Loader2,
  Check,
  X,
  ShieldAlert,
} from 'lucide-react';
import api from '../utils/api';
import { useAuthStore } from '../store/useAuthStore';

interface DocumentRecord {
  id: string;
  title: string;
  filePath: string;
  fileType: string;
  fileSize: number;
  approvalStatus: 'PENDING' | 'APPROVED' | 'REJECTED';
  digitalSignature: string | null;
  createdAt: string;
  case: {
    caseNumber: string;
    title: string;
  };
  uploadedBy: {
    fullName: string;
    role: string;
  };
}

const Documents: React.FC = () => {
  const queryClient = useQueryClient();
  const { user } = useAuthStore();
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('');

  // Fetch all documents
  const { data: documents, isLoading, error } = useQuery<DocumentRecord[]>({
    queryKey: ['documents'],
    queryFn: async () => {
      const res = await api.get('/documents');
      return res.data;
    },
  });

  // Approve/Reject Mutation
  const approveMutation = useMutation({
    mutationFn: async ({ docId, status }: { docId: string; status: 'APPROVED' | 'REJECTED' }) => {
      const res = await api.post(`/documents/${docId}/approve`, { status });
      return res.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['documents'] });
    },
  });

  // Digital Sign Mutation
  const signMutation = useMutation({
    mutationFn: async (docId: string) => {
      const signatureHash = 'sha256-' + Math.random().toString(36).substring(2) + Math.random().toString(36).substring(2);
      const res = await api.post(`/documents/${docId}/sign`, { signatureHash });
      return res.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['documents'] });
    },
  });

  const downloadFile = (docId: string, docTitle: string, fileType: string) => {
    const url = `${api.defaults.baseURL}/documents/${docId}/download`;
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `${docTitle}.${fileType}`);
    document.body.appendChild(link);
    link.click();
    link.parentNode?.removeChild(link);
  };

  const filteredDocs = documents?.filter((doc) => {
    const matchesSearch =
      doc.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
      doc.case.caseNumber.toLowerCase().includes(searchTerm.toLowerCase()) ||
      doc.uploadedBy.fullName.toLowerCase().includes(searchTerm.toLowerCase());
    
    const matchesStatus = statusFilter ? doc.approvalStatus === statusFilter : true;
    return matchesSearch && matchesStatus;
  });

  return (
    <div className="flex-1 flex flex-col min-h-0 bg-slate-50">
      {/* Page Header */}
      <div className="bg-white border-b border-gray-100 py-5 px-8 flex items-center justify-between shrink-0 shadow-sm">
        <div>
          <h1 className="text-2xl font-black text-gray-800 tracking-tight">Documents Registry</h1>
          <p className="text-gray-500 text-xs mt-1 font-semibold uppercase tracking-wider">
            Case Files, Pleadings & Digital Evidence Index
          </p>
        </div>
      </div>

      {/* Toolbar / Filters */}
      <div className="p-6 md:p-8 space-y-6 flex-1 overflow-y-auto">
        <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div className="relative w-full md:w-80">
            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
              <Search className="w-4 h-4" />
            </div>
            <input
              type="text"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-court-500 focus:border-court-500 transition-all font-medium text-gray-800"
              placeholder="Search by title, Case ID, or author..."
            />
          </div>

          <div className="flex items-center gap-3 w-full md:w-auto">
            <div className="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase tracking-wide">
              <Filter className="w-4 h-4 text-gray-400" />
              <span>Approval Status:</span>
            </div>
            <select
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value)}
              className="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-gray-600 focus:outline-none focus:ring-1 focus:ring-court-500"
            >
              <option value="">All Documents</option>
              <option value="PENDING">Pending Approval</option>
              <option value="APPROVED">Approved / Signed</option>
              <option value="REJECTED">Rejected</option>
            </select>
          </div>
        </div>

        {/* Data list */}
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          {isLoading ? (
            <div className="p-12 flex flex-col items-center justify-center gap-3 text-gray-500">
              <Loader2 className="w-8 h-8 text-court-600 animate-spin" />
              <span className="text-sm font-bold">Querying document registry...</span>
            </div>
          ) : error ? (
            <div className="p-12 text-center text-red-650 flex flex-col items-center gap-2">
              <ShieldAlert className="w-10 h-10 text-red-500" />
              <span className="font-bold">Error retrieving document indexes</span>
            </div>
          ) : !filteredDocs || filteredDocs.length === 0 ? (
            <div className="p-16 text-center text-gray-500 flex flex-col items-center justify-center gap-3">
              <FileText className="w-12 h-12 text-gray-300" />
              <div>
                <span className="font-bold block text-gray-700">No Document Logs Found</span>
                <span className="text-xs text-gray-400 font-semibold block mt-1">
                  Ensure cases are filed and documents uploaded.
                </span>
              </div>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-slate-50 text-gray-400 text-xs font-bold uppercase tracking-wider">
                    <th className="px-6 py-4">Document Title</th>
                    <th className="px-6 py-4">Case Registry</th>
                    <th className="px-6 py-4">Submitted By</th>
                    <th className="px-6 py-4">Date Uploaded</th>
                    <th className="px-6 py-4">Approval Status</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="text-sm font-semibold text-gray-700 divide-y divide-gray-100">
                  {filteredDocs.map((doc) => (
                    <tr key={doc.id} className="hover:bg-slate-50/50 transition-colors">
                      <td className="px-6 py-4">
                        <div className="flex items-center gap-3">
                          <div className="p-2 bg-blue-50 text-blue-600 rounded-lg">
                            <FileText className="w-4.5 h-4.5" />
                          </div>
                          <div>
                            <span className="font-bold text-gray-800 block">{doc.title}</span>
                            <span className="text-[10px] text-gray-400 font-bold uppercase block mt-0.5">
                              {doc.fileType} | {(doc.fileSize / 1024 / 1024).toFixed(2)} MB
                            </span>
                          </div>
                        </div>
                      </td>
                      <td className="px-6 py-4">
                        <span className="font-bold text-court-700 block">{doc.case.caseNumber}</span>
                        <span className="text-xs text-gray-450 block truncate max-w-[200px]">{doc.case.title}</span>
                      </td>
                      <td className="px-6 py-4">
                        <span className="font-bold text-gray-800 block">{doc.uploadedBy.fullName}</span>
                        <span className="px-1.5 py-0.5 text-[9px] font-bold rounded bg-court-50 text-court-700 uppercase mt-0.5 inline-block">
                          {doc.uploadedBy.role.replace('_', ' ')}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-xs font-bold text-gray-500">
                        {new Date(doc.createdAt).toLocaleDateString('en-IN', {
                          day: 'numeric',
                          month: 'short',
                          year: 'numeric',
                          hour: '2-digit',
                          minute: '2-digit',
                        })}
                      </td>
                      <td className="px-6 py-4">
                        <div className="flex flex-col gap-1">
                          <span
                            className={`px-2.5 py-1 text-xs font-bold rounded-full w-max ${
                              doc.approvalStatus === 'APPROVED'
                                ? 'bg-emerald-50 text-emerald-700'
                                : doc.approvalStatus === 'REJECTED'
                                ? 'bg-red-55/60 text-red-750'
                                : 'bg-amber-50 text-amber-700'
                            }`}
                          >
                            {doc.approvalStatus}
                          </span>
                          {doc.digitalSignature && (
                            <span className="text-[9px] text-indigo-600 font-bold uppercase tracking-wider">
                              ✓ Digitally Signed
                            </span>
                          )}
                        </div>
                      </td>
                      <td className="px-6 py-4 text-right">
                        <div className="flex items-center justify-end gap-2">
                          <button
                            onClick={() => downloadFile(doc.id, doc.title, doc.fileType)}
                            className="p-1.5 hover:bg-gray-100 rounded-lg text-court-600 hover:text-court-800 transition-colors"
                            title="Download Document"
                          >
                            <Download className="w-4 h-4" />
                          </button>

                          {/* Approve/Reject Buttons for Judges / Admins / Clerks */}
                          {['SUPER_ADMIN', 'COURT_ADMIN', 'JUDGE', 'CLERK'].includes(user?.role || '') &&
                            doc.approvalStatus === 'PENDING' && (
                              <>
                                <button
                                  onClick={() => approveMutation.mutate({ docId: doc.id, status: 'APPROVED' })}
                                  className="p-1.5 hover:bg-emerald-50 text-emerald-600 hover:text-emerald-700 rounded-lg transition-colors"
                                  title="Approve Document"
                                >
                                  <Check className="w-4 h-4" />
                                </button>
                                <button
                                  onClick={() => approveMutation.mutate({ docId: doc.id, status: 'REJECTED' })}
                                  className="p-1.5 hover:bg-red-50 text-red-600 hover:text-red-700 rounded-lg transition-colors"
                                  title="Reject Document"
                                >
                                  <X className="w-4 h-4" />
                                </button>
                              </>
                            )}

                          {/* Digital Signature for Judges / Lawyers / Admins */}
                          {['SUPER_ADMIN', 'COURT_ADMIN', 'JUDGE', 'LAWYER'].includes(user?.role || '') &&
                            !doc.digitalSignature && (
                              <button
                                onClick={() => signMutation.mutate(doc.id)}
                                className="px-2 py-1 text-[10px] font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 rounded-lg transition-colors"
                                title="Sign Document"
                              >
                                Sign
                              </button>
                            )}
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default Documents;
