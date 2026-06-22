import React, { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Users as UsersIcon,
  Search,
  Filter,
  ShieldAlert,
  Loader2,
  CheckCircle,
  XCircle,
  ToggleLeft,
  ToggleRight,
  Shield,
  Activity,
  Gavel,
  User,
} from 'lucide-react';
import api from '../utils/api';
import { useAuthStore } from '../store/useAuthStore';

interface UserRecord {
  id: string;
  username: string;
  email: string;
  fullName: string;
  role: 'SUPER_ADMIN' | 'COURT_ADMIN' | 'JUDGE' | 'LAWYER' | 'CLIENT' | 'CLERK' | 'STAFF';
  isActive: boolean;
  loginAttempts: number;
  lockoutUntil: string | null;
  createdAt: string;
  casesCount: number;
  profile?: {
    phoneNumber?: string;
    specialty?: string;
  };
}

const Users: React.FC = () => {
  const queryClient = useQueryClient();
  const { user: currentUser } = useAuthStore();
  const [searchTerm, setSearchTerm] = useState('');
  const [roleFilter, setRoleFilter] = useState('');

  // 1. Fetch Users
  const { data: users, isLoading, error } = useQuery<UserRecord[]>({
    queryKey: ['admin-users'],
    queryFn: async () => {
      const res = await api.get('/admin/users');
      return res.data;
    },
  });

  // 2. Toggle Status Mutation
  const toggleStatusMutation = useMutation({
    mutationFn: async ({ userId, isActive }: { userId: string; isActive: boolean }) => {
      const res = await api.put(`/admin/users/${userId}/status`, { isActive });
      return res.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-users'] });
    },
  });

  if (currentUser?.role !== 'SUPER_ADMIN' && currentUser?.role !== 'COURT_ADMIN') {
    return (
      <div className="flex-1 flex flex-col items-center justify-center p-8 text-center bg-slate-50 min-h-screen">
        <ShieldAlert className="w-16 h-16 text-red-500 mb-4" />
        <h2 className="text-xl font-bold text-gray-800">Access Restricted</h2>
        <p className="text-gray-500 text-sm max-w-md mt-2 font-medium">
          Only platform administrators (SUPER ADMIN or COURT ADMIN) have authorization to manage the User Directory.
        </p>
      </div>
    );
  }

  const filteredUsers = users?.filter((u) => {
    const matchesSearch =
      u.fullName.toLowerCase().includes(searchTerm.toLowerCase()) ||
      u.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
      u.username.toLowerCase().includes(searchTerm.toLowerCase());
    
    const matchesRole = roleFilter ? u.role === roleFilter : true;
    return matchesSearch && matchesRole;
  });

  const getRoleIcon = (role: string) => {
    switch (role) {
      case 'SUPER_ADMIN':
      case 'COURT_ADMIN':
        return <Shield className="w-4 h-4 text-indigo-500" />;
      case 'JUDGE':
        return <Gavel className="w-4 h-4 text-purple-500" />;
      case 'LAWYER':
        return <Activity className="w-4 h-4 text-blue-500" />;
      default:
        return <User className="w-4 h-4 text-gray-500" />;
    }
  };

  return (
    <div className="flex-1 flex flex-col min-h-0 bg-slate-50">
      {/* Header */}
      <div className="bg-white border-b border-gray-100 py-5 px-8 flex items-center justify-between shrink-0 shadow-sm">
        <div>
          <h1 className="text-2xl font-black text-gray-800 tracking-tight">User Directory</h1>
          <p className="text-gray-500 text-xs mt-1 font-semibold uppercase tracking-wider">
            System Profiles, Active Sessions & Administrative Locks
          </p>
        </div>
      </div>

      {/* Grid container */}
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
              placeholder="Search by full name, email, or handle..."
            />
          </div>

          <div className="flex items-center gap-3 w-full md:w-auto">
            <div className="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase tracking-wide">
              <Filter className="w-4 h-4 text-gray-400" />
              <span>Role Filter:</span>
            </div>
            <select
              value={roleFilter}
              onChange={(e) => setRoleFilter(e.target.value)}
              className="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-gray-600 focus:outline-none focus:ring-1 focus:ring-court-500"
            >
              <option value="">All Roles</option>
              <option value="SUPER_ADMIN">Super Admin</option>
              <option value="COURT_ADMIN">Court Admin</option>
              <option value="JUDGE">Judges</option>
              <option value="LAWYER">Lawyers</option>
              <option value="CLIENT">Clients</option>
              <option value="CLERK">Clerks</option>
              <option value="STAFF">Staff</option>
            </select>
          </div>
        </div>

        {/* Data List */}
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          {isLoading ? (
            <div className="p-12 flex flex-col items-center justify-center gap-3 text-gray-500">
              <Loader2 className="w-8 h-8 text-court-600 animate-spin" />
              <span className="text-sm font-bold">Querying system databases...</span>
            </div>
          ) : error ? (
            <div className="p-12 text-center text-red-650 flex flex-col items-center gap-2">
              <ShieldAlert className="w-10 h-10 text-red-500" />
              <span className="font-bold">Error loading user logs</span>
            </div>
          ) : !filteredUsers || filteredUsers.length === 0 ? (
            <div className="p-16 text-center text-gray-500 flex flex-col items-center justify-center gap-3">
              <UsersIcon className="w-12 h-12 text-gray-300" />
              <div>
                <span className="font-bold block text-gray-700">No Users Found</span>
                <span className="text-xs text-gray-400 font-semibold block mt-1">
                  Adjust filters or register new users to update list.
                </span>
              </div>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-slate-50 text-gray-400 text-xs font-bold uppercase tracking-wider">
                    <th className="px-6 py-4">Full Name</th>
                    <th className="px-6 py-4">Credentials / Contact</th>
                    <th className="px-6 py-4">Role Assigned</th>
                    <th className="px-6 py-4">Cases Involved</th>
                    <th className="px-6 py-4">Date Joined</th>
                    <th className="px-6 py-4">Account Status</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="text-sm font-semibold text-gray-700 divide-y divide-gray-100">
                  {filteredUsers.map((u) => (
                    <tr key={u.id} className="hover:bg-slate-50/50 transition-colors">
                      <td className="px-6 py-4">
                        <div className="flex items-center gap-3">
                          <div className="w-10 h-10 rounded-full bg-court-50 text-court-700 flex items-center justify-center font-bold uppercase text-sm border border-court-100">
                            {u.fullName.substring(0, 2)}
                          </div>
                          <div>
                            <span className="font-bold text-gray-800 block">{u.fullName}</span>
                            <span className="text-xs text-gray-450 block mt-0.5">@{u.username}</span>
                          </div>
                        </div>
                      </td>
                      <td className="px-6 py-4">
                        <span className="text-xs font-semibold text-gray-600 block">{u.email}</span>
                        {u.profile?.phoneNumber && (
                          <span className="text-[10px] text-gray-400 font-bold block mt-0.5">
                            Tel: {u.profile.phoneNumber}
                          </span>
                        )}
                      </td>
                      <td className="px-6 py-4">
                        <div className="flex items-center gap-1.5">
                          {getRoleIcon(u.role)}
                          <span className="px-2 py-0.5 text-xs font-bold bg-court-50 text-court-700 rounded uppercase">
                            {u.role.replace('_', ' ')}
                          </span>
                        </div>
                      </td>
                      <td className="px-6 py-4 text-center">
                        <span className="px-2.5 py-1 text-xs font-bold bg-blue-50 text-blue-700 rounded-full">
                          {u.casesCount} Cases
                        </span>
                      </td>
                      <td className="px-6 py-4 text-xs font-bold text-gray-500">
                        {new Date(u.createdAt).toLocaleDateString('en-IN', {
                          day: 'numeric',
                          month: 'short',
                          year: 'numeric',
                        })}
                      </td>
                      <td className="px-6 py-4">
                        <div className="flex items-center gap-1.5">
                          {u.isActive ? (
                            <CheckCircle className="w-4 h-4 text-emerald-600" />
                          ) : (
                            <XCircle className="w-4 h-4 text-red-650" />
                          )}
                          <span
                            className={`text-xs font-bold ${
                              u.isActive ? 'text-emerald-700' : 'text-red-700'
                            }`}
                          >
                            {u.isActive ? 'Active' : 'Deactivated'}
                          </span>
                        </div>
                      </td>
                      <td className="px-6 py-4 text-right">
                        {u.id !== currentUser.id && (
                          <button
                            onClick={() =>
                              toggleStatusMutation.mutate({
                                userId: u.id,
                                isActive: !u.isActive,
                              })
                            }
                            className="p-1 hover:bg-gray-150 rounded-lg text-gray-400 hover:text-gray-700 transition-colors"
                            title={u.isActive ? 'Suspend User' : 'Restore User'}
                            disabled={toggleStatusMutation.isPending}
                          >
                            {u.isActive ? (
                              <ToggleRight className="w-6 h-6 text-court-700" />
                            ) : (
                              <ToggleLeft className="w-6 h-6 text-gray-300" />
                            )}
                          </button>
                        )}
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

export default Users;
