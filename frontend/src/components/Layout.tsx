import React from 'react';
import { useNavigate, Link, useLocation } from 'react-router-dom';
import {
  Gavel,
  FolderOpen,
  Calendar,
  Users,
  LogOut,
  Bell,
  Search,
  User,
  Activity,
  Shield,
  MessageSquare,
  FileText,
} from 'lucide-react';
import { useAuthStore } from '../store/useAuthStore';
import api from '../utils/api';

const Layout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { user, logout } = useAuthStore();
  const navigate = useNavigate();
  const location = useLocation();

  const handleLogout = async () => {
    try {
      await api.post('/auth/logout');
    } catch (err) {
      console.error('Logout API failed, continuing local clear...', err);
    } finally {
      logout();
      navigate('/login');
    }
  };

  if (!user) return null;

  const isActive = (path: string) => {
    if (path === '/dashboard') {
      return location.pathname === path;
    }
    return location.pathname.startsWith(path);
  };

  const linkClass = (path: string) =>
    `flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-semibold ${
      isActive(path)
        ? 'bg-court-900 text-white shadow-md'
        : 'text-court-300 hover:bg-court-900 hover:text-white'
    }`;

  return (
    <div className="min-h-screen bg-slate-50 flex w-screen overflow-hidden">
      {/* Sidebar */}
      <aside className="w-64 bg-court-950 text-white shrink-0 hidden md:flex flex-col shadow-xl">
        {/* Sidebar Header */}
        <div className="p-6 border-b border-court-900 flex items-center gap-3">
          <div className="p-2 rounded-lg bg-court-500 text-white">
            <Gavel className="w-6 h-6" />
          </div>
          <div>
            <span className="font-black text-lg tracking-wider block">E-COURT</span>
            <span className="text-xs text-court-300 font-semibold uppercase tracking-widest">Platform v2</span>
          </div>
        </div>

        {/* Sidebar Nav Items */}
        <nav className="flex-1 px-4 py-6 space-y-1.5">
          <Link to="/dashboard" className={linkClass('/dashboard')}>
            <Activity className="w-5 h-5" />
            <span>Dashboard</span>
          </Link>
          <Link to="/cases" className={linkClass('/cases')}>
            <FolderOpen className="w-5 h-5" />
            <span>Cases</span>
          </Link>
          <Link to="/hearings" className={linkClass('/hearings')}>
            <Calendar className="w-5 h-5" />
            <span>Hearings</span>
          </Link>
          <Link to="/documents" className={linkClass('/documents')}>
            <FileText className="w-5 h-5" />
            <span>Documents</span>
          </Link>
          <Link to="/chat" className={linkClass('/chat')}>
            <MessageSquare className="w-5 h-5" />
            <span>Consultations</span>
          </Link>

          {(user.role === 'SUPER_ADMIN' || user.role === 'COURT_ADMIN') && (
            <>
              <div className="pt-4 pb-2 px-4 text-xs font-bold text-court-400 uppercase tracking-widest">Admin Controls</div>
              <Link to="/users" className={linkClass('/users')}>
                <Users className="w-5 h-5" />
                <span>User Directory</span>
              </Link>
              <Link to="/audits" className={linkClass('/audits')}>
                <Shield className="w-5 h-5" />
                <span>System Audits</span>
              </Link>
            </>
          )}
        </nav>

        {/* Sidebar Footer Profile */}
        <div className="p-4 border-t border-court-900 bg-court-950/50">
          <div className="flex items-center gap-3 mb-4">
            <div className="w-10 h-10 rounded-full bg-court-800 flex items-center justify-center font-bold text-court-300 uppercase">
              {user.username.substring(0, 2)}
            </div>
            <div className="truncate">
              <span className="font-bold text-sm block truncate">{user.fullName}</span>
              <span className="text-xs text-court-400 font-semibold uppercase">{user.role.replace('_', ' ')}</span>
            </div>
          </div>
          <button
            onClick={handleLogout}
            className="w-full flex items-center justify-center gap-2 py-2 px-3 bg-red-950/30 hover:bg-red-900/40 text-red-300 hover:text-red-200 border border-red-900/45 rounded-xl text-sm font-semibold transition-all"
          >
            <LogOut className="w-4 h-4" />
            <span>Sign Out</span>
          </button>
        </div>
      </aside>

      {/* Main Content Area */}
      <div className="flex-1 flex flex-col min-w-0 min-h-screen overflow-hidden">
        {/* Top Header */}
        <header className="h-16 bg-white border-b border-gray-100 flex items-center justify-between px-6 shrink-0 shadow-sm">
          {/* Search bar */}
          <div className="relative w-64 hidden sm:block">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
              <Search className="w-4 h-4" />
            </div>
            <input
              type="text"
              className="w-full pl-9 pr-4 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-court-500 focus:border-court-500 transition-all font-semibold"
              placeholder="Search case registries, files..."
            />
          </div>

          <div className="flex items-center gap-4 ml-auto">
            {/* Notification bell */}
            <button className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition-colors relative">
              <Bell className="w-5 h-5" />
              <span className="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-orange-500 ring-2 ring-white" />
            </button>

            {/* Profile Menu */}
            <div className="h-8 w-px bg-gray-100" />
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 rounded-lg bg-court-50 text-court-700 flex items-center justify-center font-bold text-sm">
                <User className="w-4 h-4" />
              </div>
              <span className="text-sm font-bold text-gray-700 hidden sm:inline-block">{user.fullName}</span>
            </div>
          </div>
        </header>

        {/* Children Render View */}
        <main className="flex-1 overflow-y-auto min-h-0 bg-slate-50 flex flex-col">
          {children}
        </main>
      </div>
    </div>
  );
};

export default Layout;
