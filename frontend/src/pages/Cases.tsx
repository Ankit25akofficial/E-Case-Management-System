import React, { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useNavigate } from 'react-router-dom';
import {
  Gavel,
  FolderOpen,
  Filter,
  PlusCircle,
  Search,
  X,
  AlertCircle,
  ChevronRight,
  Loader2,
} from 'lucide-react';
import api from '../utils/api';
import { useAuthStore } from '../store/useAuthStore';

interface Case {
  id: string;
  caseNumber: string;
  title: string;
  description: string;
  category: string;
  status: 'PENDING' | 'ASSIGNED' | 'IN_PROGRESS' | 'CLOSED' | 'ARCHIVED';
  priority: 'LOW' | 'MEDIUM' | 'HIGH' | 'URGENT';
  filingDate: string;
  court: { name: string };
  client: { fullName: string };
}

const Cases: React.FC = () => {
  const queryClient = useQueryClient();
  const navigate = useNavigate();
  const { user } = useAuthStore();

  // Filters State
  const [statusFilter, setStatusFilter] = useState('');
  const [priorityFilter, setPriorityFilter] = useState('');
  const [searchTerm, setSearchTerm] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);

  // New Case Form State
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [category, setCategory] = useState('Civil');
  const [priority, setPriority] = useState('MEDIUM');
  const [formError, setFormError] = useState<string | null>(null);

  // Query Cases from API
  const { data, isLoading, error } = useQuery({
    queryKey: ['cases', statusFilter, priorityFilter],
    queryFn: async () => {
      const params: any = {};
      if (statusFilter) params.status = statusFilter;
      if (priorityFilter) params.priority = priorityFilter;
      const res = await api.get('/cases', { params });
      return res.data;
    },
  });

  // Mutation to File a New Case
  const fileCaseMutation = useMutation({
    mutationFn: async (newCaseData: any) => {
      const res = await api.post('/cases', newCaseData);
      return res.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['cases'] });
      setIsModalOpen(false);
      // Reset form
      setTitle('');
      setDescription('');
      setCategory('Civil');
      setPriority('MEDIUM');
    },
    onError: (err: any) => {
      setFormError(err.response?.data?.message || 'Failed to file case. Please check values.');
    },
  });

  const handleFileCaseSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormError(null);

    if (!title) {
      setFormError('Case title is required');
      return;
    }

    try {
      // We need a court ID. Let's query backend or find a court.
      // To make it robust, let's create Delhi High Court or find it.
      // In this demo flow, let's assume we can retrieve the courts list
      const casesRes = await api.get('/cases');
      // If there is any case already created, extract courtId
      let courtId = '';
      if (casesRes.data?.cases?.length > 0) {
        courtId = casesRes.data.cases[0].courtId;
      } else {
        // We'll query DB or use Delhi High Court name to search/create
        // For seed user login safety, we fallback to delhi court seed setup
        // Let's mock a standard seed courtId we create via admin or fallback
        // We can create a default court on seed, let's use a fallback uuid of Delhi court
        courtId = '30000000-0000-0000-0000-000000000000'; // Delhi High Court Seed ID
      }

      fileCaseMutation.mutate({
        title,
        description,
        category,
        priority,
        courtId,
        clientId: user?.id,
      });
    } catch (err: any) {
      setFormError('Failed to parse court details.');
    }
  };

  const filteredCases = data?.cases?.filter((c: Case) => {
    const term = searchTerm.toLowerCase();
    return (
      c.caseNumber.toLowerCase().includes(term) ||
      c.title.toLowerCase().includes(term) ||
      c.category.toLowerCase().includes(term)
    );
  });

  return (
    <div className="min-h-screen bg-slate-50 flex flex-col">
      {/* Top Breadcrumb Header */}
      <div className="bg-white border-b border-gray-100 py-5 px-8 flex items-center justify-between shrink-0">
        <div>
          <h1 className="text-2xl font-black text-gray-800 tracking-tight">Case Registries</h1>
          <p className="text-gray-500 text-xs mt-1 font-semibold uppercase tracking-wider">
            Index & Registry Status Directories
          </p>
        </div>
        {user?.role === 'CLIENT' && (
          <button
            onClick={() => setIsModalOpen(true)}
            className="py-2.5 px-4 bg-court-700 hover:bg-court-800 text-white font-bold rounded-xl shadow-md transition-all flex items-center gap-2 text-sm"
          >
            <PlusCircle className="w-4.5 h-4.5" />
            <span>File New Case</span>
          </button>
        )}
      </div>

      {/* Workspace Area */}
      <div className="flex-1 p-6 md:p-8 space-y-6 overflow-y-auto">
        {/* Filters Toolbar */}
        <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
          {/* Search */}
          <div className="relative w-full md:w-80">
            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
              <Search className="w-4 h-4" />
            </div>
            <input
              type="text"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-court-500 focus:border-court-500 transition-all font-medium text-gray-800"
              placeholder="Search by ID, title, or type..."
            />
          </div>

          {/* Filtering Dropdowns */}
          <div className="flex items-center gap-3 w-full md:w-auto">
            <div className="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase tracking-wide">
              <Filter className="w-4 h-4 text-gray-400" />
              <span>Filters:</span>
            </div>
            <select
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value)}
              className="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-gray-600 focus:outline-none focus:ring-1 focus:ring-court-500"
            >
              <option value="">All Statuses</option>
              <option value="PENDING">Pending</option>
              <option value="ASSIGNED">Assigned</option>
              <option value="IN_PROGRESS">In Progress</option>
              <option value="CLOSED">Closed</option>
            </select>
            <select
              value={priorityFilter}
              onChange={(e) => setPriorityFilter(e.target.value)}
              className="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-gray-600 focus:outline-none focus:ring-1 focus:ring-court-500"
            >
              <option value="">All Priorities</option>
              <option value="LOW">Low</option>
              <option value="MEDIUM">Medium</option>
              <option value="HIGH">High</option>
              <option value="URGENT">Urgent</option>
            </select>
          </div>
        </div>

        {/* Data Grid Card */}
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          {isLoading ? (
            <div className="p-12 flex flex-col items-center justify-center gap-3 text-gray-500">
              <Loader2 className="w-8 h-8 text-court-600 animate-spin" />
              <span className="text-sm font-bold">Querying case databases...</span>
            </div>
          ) : error ? (
            <div className="p-12 text-center text-red-600 flex flex-col items-center gap-2">
              <AlertCircle className="w-10 h-10" />
              <span className="font-bold">Error loading cases from registries</span>
            </div>
          ) : !filteredCases || filteredCases.length === 0 ? (
            <div className="p-16 text-center text-gray-500 flex flex-col items-center justify-center gap-3">
              <FolderOpen className="w-12 h-12 text-gray-300" />
              <div>
                <span className="font-bold block text-gray-700">No Case Records Found</span>
                <span className="text-xs text-gray-400 font-semibold block mt-1">
                  Adjust filters or create a case entry to see data
                </span>
              </div>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-slate-50 text-gray-400 text-xs font-bold uppercase tracking-wider">
                    <th className="px-6 py-4">Case Registry ID</th>
                    <th className="px-6 py-4">Title</th>
                    <th className="px-6 py-4">Category</th>
                    <th className="px-6 py-4">Filing Court</th>
                    <th className="px-6 py-4">Priority</th>
                    <th className="px-6 py-4">Status</th>
                    <th className="px-6 py-4">Action</th>
                  </tr>
                </thead>
                <tbody className="text-sm font-semibold text-gray-700 divide-y divide-gray-100">
                  {filteredCases.map((c: Case) => (
                    <tr
                      key={c.id}
                      className="hover:bg-slate-50/50 transition-colors cursor-pointer"
                      onClick={() => navigate(`/cases/${c.id}`)}
                    >
                      <td className="px-6 py-4 text-court-700 font-black">{c.caseNumber}</td>
                      <td className="px-6 py-4 truncate max-w-xs">{c.title}</td>
                      <td className="px-6 py-4">{c.category}</td>
                      <td className="px-6 py-4 text-xs font-bold text-gray-500">{c.court?.name || 'Delhi Court'}</td>
                      <td className="px-6 py-4">
                        <span
                          className={`px-2.5 py-1 text-xs font-bold rounded-full ${
                            c.priority === 'URGENT'
                              ? 'bg-red-100 text-red-700'
                              : c.priority === 'HIGH'
                              ? 'bg-orange-50 text-orange-700'
                              : c.priority === 'MEDIUM'
                              ? 'bg-blue-50 text-blue-700'
                              : 'bg-gray-100 text-gray-700'
                          }`}
                        >
                          {c.priority}
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        <span
                          className={`px-2.5 py-1 text-xs font-bold rounded-full ${
                            c.status === 'CLOSED'
                              ? 'bg-emerald-50 text-emerald-700'
                              : c.status === 'IN_PROGRESS'
                              ? 'bg-blue-50 text-blue-700'
                              : c.status === 'ASSIGNED'
                              ? 'bg-purple-50 text-purple-700'
                              : 'bg-amber-50 text-amber-700'
                          }`}
                        >
                          {c.status.replace('_', ' ')}
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        <button className="p-1 hover:bg-gray-100 rounded-lg text-gray-400 hover:text-gray-600 transition-colors">
                          <ChevronRight className="w-5 h-5" />
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>

      {/* Slide Drawer Modal for File Case */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-end">
          <div className="w-full max-w-lg bg-white h-full shadow-2xl flex flex-col p-6 border-l border-gray-100 animate-slide-in">
            {/* Modal Header */}
            <div className="flex items-center justify-between pb-5 border-b border-gray-100">
              <div className="flex items-center gap-2">
                <Gavel className="w-6 h-6 text-court-700" />
                <h3 className="text-xl font-bold text-gray-800">File Case Registry</h3>
              </div>
              <button
                onClick={() => setIsModalOpen(false)}
                className="p-1.5 hover:bg-gray-100 rounded-lg text-gray-400 hover:text-gray-600 transition-colors"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Error alerts */}
            {formError && (
              <div className="mt-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded-lg flex items-start gap-2 text-sm">
                <AlertCircle className="w-5 h-5 shrink-0 mt-0.5" />
                <span>{formError}</span>
              </div>
            )}

            {/* Form */}
            <form onSubmit={handleFileCaseSubmit} className="flex-1 overflow-y-auto py-5 space-y-5">
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1.5">Case Title / Suit Name</label>
                <input
                  type="text"
                  value={title}
                  onChange={(e) => setTitle(e.target.value)}
                  className="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-court-500 text-sm font-semibold text-gray-800"
                  placeholder="e.g. Verma vs. Land Corp"
                  required
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-1.5">Category</label>
                  <select
                    value={category}
                    onChange={(e) => setCategory(e.target.value)}
                    className="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-court-500 text-sm font-semibold text-gray-800"
                  >
                    <option value="Civil">Civil Suit</option>
                    <option value="Criminal">Criminal Case</option>
                    <option value="Corporate">Corporate Dispute</option>
                    <option value="Family">Family Suit</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-1.5">Filing Priority</label>
                  <select
                    value={priority}
                    onChange={(e) => setPriority(e.target.value)}
                    className="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-court-500 text-sm font-semibold text-gray-800"
                  >
                    <option value="LOW">Low</option>
                    <option value="MEDIUM">Medium</option>
                    <option value="HIGH">High</option>
                    <option value="URGENT">Urgent</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1.5">Description of Claims</label>
                <textarea
                  value={description}
                  onChange={(e) => setDescription(e.target.value)}
                  className="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-court-500 text-sm font-semibold text-gray-800 h-32 resize-none"
                  placeholder="Provide brief details outlining claims, legal grounds..."
                />
              </div>
            </form>

            {/* Form Footer Actions */}
            <div className="pt-5 border-t border-gray-100 flex items-center justify-end gap-3 shrink-0">
              <button
                type="button"
                onClick={() => setIsModalOpen(false)}
                className="py-2.5 px-4 bg-gray-100 hover:bg-gray-250 text-gray-700 font-bold rounded-xl text-sm transition-all"
              >
                Cancel
              </button>
              <button
                type="submit"
                onClick={handleFileCaseSubmit}
                disabled={fileCaseMutation.isPending}
                className="py-2.5 px-5 bg-court-700 hover:bg-court-800 text-white font-bold rounded-xl text-sm shadow-md transition-all flex items-center gap-2"
              >
                {fileCaseMutation.isPending ? (
                  <Loader2 className="w-4.5 h-4.5 animate-spin" />
                ) : (
                  <span>Submit Filing</span>
                )}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default Cases;
