import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  FolderOpen,
  Calendar,
  PlusCircle,
  Clock,
  CheckCircle,
  Plus,
  Trash2,
  CheckSquare,
  Square,
} from 'lucide-react';
import { useAuthStore } from '../store/useAuthStore';
import api from '../utils/api';

const Dashboard: React.FC = () => {
  const { user } = useAuthStore();
  const queryClient = useQueryClient();
  const [newTaskTitle, setNewTaskTitle] = useState('');

  // Fetch tasks
  const { data: tasks } = useQuery<any[]>({
    queryKey: ['tasks'],
    queryFn: async () => {
      const res = await api.get('/tasks');
      return res.data;
    },
  });

  // Create task mutation
  const createTaskMutation = useMutation({
    mutationFn: async (title: string) => {
      const res = await api.post('/tasks', { title });
      return res.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['tasks'] });
      setNewTaskTitle('');
    },
  });

  // Toggle completed
  const toggleTaskMutation = useMutation({
    mutationFn: async ({ taskId, isCompleted }: { taskId: string; isCompleted: boolean }) => {
      const res = await api.put(`/tasks/${taskId}`, { isCompleted });
      return res.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['tasks'] });
    },
  });

  // Delete task
  const deleteTaskMutation = useMutation({
    mutationFn: async (taskId: string) => {
      const res = await api.delete(`/tasks/${taskId}`);
      return res.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['tasks'] });
    },
  });

  const handleAddTask = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newTaskTitle.trim()) return;
    createTaskMutation.mutate(newTaskTitle.trim());
  };


  if (!user) return null;

  return (
    <div className="p-6 md:p-8 space-y-8">
      {/* Welcome Banner */}
      <div className="bg-gradient-to-r from-court-900 to-court-800 text-white rounded-2xl p-6 md:p-8 shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
              <h2 className="text-2xl md:text-3xl font-extrabold tracking-tight">System Portal Dashboard</h2>
              <p className="text-court-200 mt-2 text-sm md:text-base max-w-xl font-medium">
                Welcome back, {user.fullName}. Use the interface to monitor active judicial cases, scheduled hearings, and pending tasks.
              </p>
            </div>
            <div className="shrink-0 flex gap-3">
              {user.role === 'CLIENT' ? (
                <button className="py-2.5 px-4 bg-white text-court-900 hover:bg-court-50 font-bold rounded-xl shadow-md transition-all flex items-center gap-2 text-sm">
                  <PlusCircle className="w-4 h-4" />
                  <span>File New Case</span>
                </button>
              ) : (
                <button className="py-2.5 px-4 bg-white text-court-900 hover:bg-court-50 font-bold rounded-xl shadow-md transition-all flex items-center gap-2 text-sm">
                  <PlusCircle className="w-4 h-4" />
                  <span>Schedule Hearing</span>
                </button>
              )}
            </div>
          </div>

          {/* Stats Grid */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {/* Total Cases */}
            <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
              <div>
                <span className="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Active Cases</span>
                <span className="text-3xl font-black text-gray-800 mt-1 block">12</span>
              </div>
              <div className="p-3.5 rounded-2xl bg-blue-50 text-blue-600">
                <FolderOpen className="w-6 h-6" />
              </div>
            </div>

            {/* Pending Case Audits */}
            <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
              <div>
                <span className="text-xs font-bold text-gray-400 uppercase tracking-wider block">Pending Actions</span>
                <span className="text-3xl font-black text-gray-800 mt-1 block">4</span>
              </div>
              <div className="p-3.5 rounded-2xl bg-amber-50 text-amber-600">
                <Clock className="w-6 h-6" />
              </div>
            </div>

            {/* Resolved Cases */}
            <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
              <div>
                <span className="text-xs font-bold text-gray-400 uppercase tracking-wider block">Closed Registries</span>
                <span className="text-3xl font-black text-gray-800 mt-1 block">38</span>
              </div>
              <div className="p-3.5 rounded-2xl bg-emerald-50 text-emerald-600">
                <CheckCircle className="w-6 h-6" />
              </div>
            </div>

            {/* Scheduled Hearings */}
            <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
              <div>
                <span className="text-xs font-bold text-gray-400 uppercase tracking-wider block">Upcoming Hearings</span>
                <span className="text-3xl font-black text-gray-800 mt-1 block">3</span>
              </div>
              <div className="p-3.5 rounded-2xl bg-purple-50 text-purple-600">
                <Calendar className="w-6 h-6" />
              </div>
            </div>
          </div>

          {/* Detailed Lists Grid */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Cases Table */}
            <div className="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
              <div className="p-6 border-b border-gray-50 flex items-center justify-between">
                <h3 className="font-bold text-gray-800 text-lg">Recent Case Registries</h3>
                <Link to="/cases" className="text-xs font-bold text-court-600 hover:text-court-800 transition-colors">
                  View All Files
                </Link>
              </div>
              <div className="overflow-x-auto">
                <table className="w-full text-left border-collapse">
                  <thead>
                    <tr className="bg-slate-50 text-gray-400 text-xs font-bold uppercase tracking-wider">
                      <th className="px-6 py-3">Case ID</th>
                      <th className="px-6 py-3">Title</th>
                      <th className="px-6 py-3">Status</th>
                      <th className="px-6 py-3">Priority</th>
                    </tr>
                  </thead>
                  <tbody className="text-sm font-medium text-gray-700 divide-y divide-gray-50">
                    <tr>
                      <td className="px-6 py-4 text-court-700 font-bold">ECMS-2026-8812</td>
                      <td className="px-6 py-4 truncate max-w-xs">State of Delhi vs. R. Kumar & Ors.</td>
                      <td className="px-6 py-4">
                        <span className="px-2.5 py-1 text-xs font-semibold bg-blue-50 text-blue-700 rounded-full">
                          In Progress
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        <span className="px-2.5 py-1 text-xs font-semibold bg-red-50 text-red-700 rounded-full">
                          High
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td className="px-6 py-4 text-court-700 font-bold">ECMS-2026-3104</td>
                      <td className="px-6 py-4 truncate max-w-xs">A. Verma vs. Bharti Land Corp</td>
                      <td className="px-6 py-4">
                        <span className="px-2.5 py-1 text-xs font-semibold bg-amber-50 text-amber-700 rounded-full">
                          Pending
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        <span className="px-2.5 py-1 text-xs font-semibold bg-gray-50 text-gray-700 rounded-full">
                          Medium
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td className="px-6 py-4 text-court-700 font-bold">ECMS-2026-5991</td>
                      <td className="px-6 py-4 truncate max-w-xs">M. Sen vs. Union of India</td>
                      <td className="px-6 py-4">
                        <span className="px-2.5 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 rounded-full">
                          Closed
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        <span className="px-2.5 py-1 text-xs font-semibold bg-gray-50 text-gray-700 rounded-full">
                          Medium
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            {/* Hearings / Activities Sidebar */}
            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6">
              <div>
                <div className="flex items-center justify-between border-b border-gray-50 pb-4">
                  <h3 className="font-bold text-gray-800 text-lg">Upcoming Schedules</h3>
                  <span className="px-2 py-0.5 text-xs font-bold bg-purple-50 text-purple-700 rounded-full">Hearings</span>
                </div>

                <div className="space-y-4 mt-4">
                  {/* Hearing 1 */}
                  <div className="flex gap-4 p-3 rounded-xl hover:bg-slate-50 transition-colors">
                    <div className="w-12 h-12 bg-court-50 text-court-700 rounded-xl flex flex-col items-center justify-center shrink-0">
                      <span className="text-xs font-black">28</span>
                      <span className="text-[10px] font-bold uppercase -mt-0.5">Jun</span>
                    </div>
                    <div>
                      <span className="font-bold text-sm block text-gray-800">Hearing: ECMS-2026-8812</span>
                      <span className="text-xs text-gray-400 font-semibold block mt-0.5">10:30 AM | Room #3</span>
                      <span className="text-xs text-court-600 font-semibold mt-1 inline-block">Judge Sharma presiding</span>
                    </div>
                  </div>

                  {/* Hearing 2 */}
                  <div className="flex gap-4 p-3 rounded-xl hover:bg-slate-50 transition-colors">
                    <div className="w-12 h-12 bg-court-50 text-court-700 rounded-xl flex flex-col items-center justify-center shrink-0">
                      <span className="text-xs font-black">02</span>
                      <span className="text-[10px] font-bold uppercase -mt-0.5">Jul</span>
                    </div>
                    <div>
                      <span className="font-bold text-sm block text-gray-800">Hearing: ECMS-2026-3104</span>
                      <span className="text-xs text-gray-400 font-semibold block mt-0.5">02:15 PM | Room #1</span>
                      <span className="text-xs text-court-600 font-semibold mt-1 inline-block">Evidence submission stage</span>
                    </div>
                  </div>
                </div>
              </div>

              {/* Personal Tasks Checklist Section */}
              <div className="border-t border-gray-100 pt-6 space-y-4">
                <div className="flex items-center justify-between border-b border-gray-50 pb-3">
                  <h3 className="font-bold text-gray-800 text-lg">Personal Tasks</h3>
                  <span className="px-2 py-0.5 text-xs font-bold bg-indigo-50 text-indigo-700 rounded-full">To-Do</span>
                </div>

                {/* Add Task Form */}
                <form onSubmit={handleAddTask} className="flex gap-2">
                  <input
                    type="text"
                    value={newTaskTitle}
                    onChange={(e) => setNewTaskTitle(e.target.value)}
                    placeholder="New task..."
                    className="flex-1 px-3 py-2 bg-gray-50 border border-gray-250 rounded-xl text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-court-500"
                    required
                  />
                  <button
                    type="submit"
                    className="p-2 bg-court-700 hover:bg-court-800 text-white rounded-xl flex items-center justify-center transition-colors shrink-0"
                  >
                    <Plus className="w-4 h-4" />
                  </button>
                </form>

                {/* Task List */}
                <div className="space-y-2 max-h-60 overflow-y-auto pr-1">
                  {tasks && tasks.length > 0 ? (
                    tasks.map((task: any) => (
                      <div key={task.id} className="flex items-center justify-between gap-2 p-2.5 rounded-xl hover:bg-slate-50 border border-gray-50 transition-colors">
                        <button
                          onClick={() => toggleTaskMutation.mutate({ taskId: task.id, isCompleted: !task.isCompleted })}
                          className="flex items-center gap-2.5 text-left flex-1"
                        >
                          {task.isCompleted ? (
                            <CheckSquare className="w-4.5 h-4.5 text-emerald-600 shrink-0" />
                          ) : (
                            <Square className="w-4.5 h-4.5 text-gray-400 shrink-0" />
                          )}
                          <span className={`text-xs font-bold ${task.isCompleted ? 'text-gray-400 line-through' : 'text-gray-700'}`}>
                            {task.title}
                          </span>
                        </button>
                        <button
                          onClick={() => deleteTaskMutation.mutate(task.id)}
                          className="p-1 hover:bg-red-50 hover:text-red-600 rounded text-gray-400 transition-colors"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    ))
                  ) : (
                    <p className="text-xs text-gray-400 italic text-center font-semibold py-3">No pending tasks.</p>
                  )}
                </div>
              </div>
            </div>

          </div>
    </div>
  );
};

export default Dashboard;
