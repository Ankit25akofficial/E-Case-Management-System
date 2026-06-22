import React, { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import {
  Shield,
  Search,
  Filter,
  ShieldAlert,
  Loader2,
  Clock,
  Terminal,
  Globe,
} from 'lucide-react';
import api from '../utils/api';
import { useAuthStore } from '../store/useAuthStore';

interface AuditRecord {
  id: string;
  action: string;
  details: string;
  ipAddress: string | null;
  createdAt: string;
  user: {
    fullName: string;
    email: string;
    role: string;
  } | null;
}

const Audits: React.FC = () => {
  const { user: currentUser } = useAuthStore();
  const [searchTerm, setSearchTerm] = useState('');
  const [actionFilter, setActionFilter] = useState('');

  // 1. Fetch Audit Logs
  const { data: logs, isLoading, error } = useQuery<AuditRecord[]>({
    queryKey: ['admin-audits'],
    queryFn: async () => {
      const res = await api.get('/admin/audit-logs');
      return res.data;
    },
  });

  if (currentUser?.role !== 'SUPER_ADMIN' && currentUser?.role !== 'COURT_ADMIN') {
    return (
      <div className="flex-1 flex flex-col items-center justify-center p-8 text-center bg-slate-50 min-h-screen">
        <ShieldAlert className="w-16 h-16 text-red-500 mb-4" />
        <h2 className="text-xl font-bold text-gray-800">Access Restricted</h2>
        <p className="text-gray-500 text-sm max-w-md mt-2 font-medium">
          Only platform administrators (SUPER ADMIN or COURT ADMIN) have authorization to query the System Audit Trail logs.
        </p>
      </div>
    );
  }

  const filteredLogs = logs?.filter((l) => {
    const matchesSearch =
      l.details.toLowerCase().includes(searchTerm.toLowerCase()) ||
      l.action.toLowerCase().includes(searchTerm.toLowerCase()) ||
      (l.user?.fullName.toLowerCase().includes(searchTerm.toLowerCase()) ?? false);
    
    const matchesAction = actionFilter ? l.action === actionFilter : true;
    return matchesSearch && matchesAction;
  });

  // Extract unique action types for filter options
  const uniqueActions = Array.from(new Set(logs?.map((l) => l.action) || []));

  return (
    <div className="flex-1 flex flex-col min-h-0 bg-slate-50">
      {/* Header */}
      <div className="bg-white border-b border-gray-100 py-5 px-8 flex items-center justify-between shrink-0 shadow-sm">
        <div>
          <h1 className="text-2xl font-black text-gray-800 tracking-tight">System Audits</h1>
          <p className="text-gray-500 text-xs mt-1 font-semibold uppercase tracking-wider">
            Cryptographic Audit Trail, Database Updates & Session Logins
          </p>
        </div>
      </div>

      {/* Grid view */}
      <div className="p-6 md:p-8 space-y-6 flex-1 overflow-y-auto">
        {/* Filters */}
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
              placeholder="Search actions, details, or user..."
            />
          </div>

          <div className="flex items-center gap-3 w-full md:w-auto">
            <div className="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase tracking-wide">
              <Filter className="w-4 h-4 text-gray-400" />
              <span>Action Category:</span>
            </div>
            <select
              value={actionFilter}
              onChange={(e) => setActionFilter(e.target.value)}
              className="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-gray-600 focus:outline-none focus:ring-1 focus:ring-court-500"
            >
              <option value="">All Categories</option>
              {uniqueActions.map((act) => (
                <option key={act} value={act}>
                  {act.replace(/_/g, ' ')}
                </option>
              ))}
            </select>
          </div>
        </div>

        {/* Timeline Log Grid */}
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          {isLoading ? (
            <div className="p-12 flex flex-col items-center justify-center gap-3 text-gray-500">
              <Loader2 className="w-8 h-8 text-court-600 animate-spin" />
              <span className="text-sm font-bold">Retrieving security audits...</span>
            </div>
          ) : error ? (
            <div className="p-12 text-center text-red-650 flex flex-col items-center gap-2">
              <ShieldAlert className="w-10 h-10 text-red-500" />
              <span className="font-bold">Error loading system logs</span>
            </div>
          ) : !filteredLogs || filteredLogs.length === 0 ? (
            <div className="p-16 text-center text-gray-500 flex flex-col items-center justify-center gap-3">
              <Shield className="w-12 h-12 text-gray-300" />
              <div>
                <span className="font-bold block text-gray-700">No Audits Recorded</span>
                <span className="text-xs text-gray-400 font-semibold block mt-1">
                  System logs will register on database mutations or auth attempts.
                </span>
              </div>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-slate-50 text-gray-400 text-xs font-bold uppercase tracking-wider">
                    <th className="px-6 py-4">Security Action</th>
                    <th className="px-6 py-4">Transaction Details</th>
                    <th className="px-6 py-4">Actor</th>
                    <th className="px-6 py-4">Timestamp</th>
                    <th className="px-6 py-4">Network IP</th>
                  </tr>
                </thead>
                <tbody className="text-sm font-semibold text-gray-700 divide-y divide-gray-100">
                  {filteredLogs.map((log) => (
                    <tr key={log.id} className="hover:bg-slate-50/50 transition-colors">
                      <td className="px-6 py-4">
                        <div className="flex items-center gap-2">
                          <Terminal className="w-4 h-4 text-court-500" />
                          <span className="px-2 py-0.5 text-xs font-black bg-court-50 text-court-700 rounded uppercase tracking-wider">
                            {log.action}
                          </span>
                        </div>
                      </td>
                      <td className="px-6 py-4">
                        <span className="text-xs font-semibold text-gray-700 leading-relaxed block max-w-md">
                          {log.details}
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        {log.user ? (
                          <div className="flex items-center gap-2">
                            <div className="w-7 h-7 rounded-full bg-court-50 text-court-750 flex items-center justify-center font-bold text-xs uppercase shrink-0">
                              {log.user.fullName.substring(0, 2)}
                            </div>
                            <div>
                              <span className="font-bold text-gray-800 block text-xs">{log.user.fullName}</span>
                              <span className="text-[9px] text-gray-450 font-bold uppercase tracking-wider block">
                                {log.user.role.replace('_', ' ')}
                              </span>
                            </div>
                          </div>
                        ) : (
                          <span className="text-xs text-gray-400 font-semibold italic">System Process</span>
                        )}
                      </td>
                      <td className="px-6 py-4 text-xs font-bold text-gray-500">
                        <div className="flex items-center gap-1.5">
                          <Clock className="w-3.5 h-3.5 text-gray-400" />
                          <span>
                            {new Date(log.createdAt).toLocaleDateString('en-IN', {
                              day: 'numeric',
                              month: 'short',
                              year: 'numeric',
                              hour: '2-digit',
                              minute: '2-digit',
                              second: '2-digit',
                            })}
                          </span>
                        </div>
                      </td>
                      <td className="px-6 py-4 text-xs font-semibold text-gray-550">
                        <div className="flex items-center gap-1">
                          <Globe className="w-3.5 h-3.5 text-gray-300" />
                          <span>{log.ipAddress || '::1 (Local)'}</span>
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

export default Audits;
