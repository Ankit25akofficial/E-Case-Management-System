import React, { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Calendar as CalendarIcon,
  ChevronLeft,
  ChevronRight,
  PlusCircle,
  Clock,
  MapPin,
  FileText,
  AlertCircle,
  X,
  Loader2,
  CheckCircle,
} from 'lucide-react';
import api from '../utils/api';
import { useAuthStore } from '../store/useAuthStore';

interface Hearing {
  id: string;
  hearingDate: string;
  location: string;
  status: 'SCHEDULED' | 'COMPLETED' | 'POSTPONED' | 'CANCELLED';
  notes: string;
  case: {
    caseNumber: string;
    title: string;
    judge?: { fullName: string };
    client?: { fullName: string };
  };
}

interface CaseOption {
  id: string;
  caseNumber: string;
  title: string;
  courtId: string;
}

const CalendarView: React.FC = () => {
  const queryClient = useQueryClient();
  const { user } = useAuthStore();

  const [currentDate, setCurrentDate] = useState(new Date());
  const [selectedDate, setSelectedDate] = useState<Date | null>(new Date());
  const [isModalOpen, setIsModalOpen] = useState(false);

  // Form states
  const [scheduleCaseId, setScheduleCaseId] = useState('');
  const [scheduleTime, setScheduleTime] = useState('10:00');
  const [scheduleLocation, setScheduleLocation] = useState('Court Room #1');
  const [scheduleNotes, setScheduleNotes] = useState('');
  const [formError, setFormError] = useState<string | null>(null);
  const [formSuccess, setFormSuccess] = useState<string | null>(null);

  const year = currentDate.getFullYear();
  const month = currentDate.getMonth();

  // Fetch Hearings for current Month view range
  const startDate = new Date(year, month, 1).toISOString();
  const endDate = new Date(year, month + 1, 0, 23, 59, 59).toISOString();

  const { data: hearings } = useQuery<Hearing[]>({
    queryKey: ['hearings', startDate, endDate],
    queryFn: async () => {
      const res = await api.get('/hearings', {
        params: { startDate, endDate },
      });
      return res.data;
    },
  });

  // Query Cases options for selection dropdown
  const { data: casesData } = useQuery<{ cases: CaseOption[] }>({
    queryKey: ['cases-options'],
    queryFn: async () => {
      const res = await api.get('/cases', { params: { limit: 100 } });
      return res.data;
    },
    enabled: isModalOpen && (user?.role !== 'CLIENT'),
  });

  // Schedule Hearing Mutation
  const scheduleHearingMutation = useMutation({
    mutationFn: async (hearingPayload: any) => {
      const res = await api.post('/hearings', hearingPayload);
      return res.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['hearings'] });
      setFormSuccess('Hearing scheduled successfully!');
      setTimeout(() => {
        setIsModalOpen(false);
        setFormSuccess(null);
        setScheduleCaseId('');
        setScheduleNotes('');
      }, 2000);
    },
    onError: (err: any) => {
      setFormError(err.response?.data?.message || 'Failed to schedule hearing. Conflict detected.');
    },
  });

  const handlePrevMonth = () => {
    setCurrentDate(new Date(year, month - 1, 1));
  };

  const handleNextMonth = () => {
    setCurrentDate(new Date(year, month + 1, 1));
  };

  // Calendar Helpers
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const firstDayIndex = new Date(year, month, 1).getDay();

  const calendarDays = [];
  // Empty slots for previous month bleed
  for (let i = 0; i < firstDayIndex; i++) {
    calendarDays.push(null);
  }
  // Days of current month
  for (let d = 1; d <= daysInMonth; d++) {
    calendarDays.push(new Date(year, month, d));
  }

  // Get hearings mapped to specific date
  const getHearingsForDate = (date: Date) => {
    return hearings?.filter((h) => {
      const hDate = new Date(h.hearingDate);
      return (
        hDate.getDate() === date.getDate() &&
        hDate.getMonth() === date.getMonth() &&
        hDate.getFullYear() === date.getFullYear()
      );
    });
  };

  const handleScheduleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setFormError(null);
    setFormSuccess(null);

    if (!scheduleCaseId || !selectedDate || !scheduleTime || !scheduleLocation) {
      setFormError('All fields are required');
      return;
    }

    const selectedCase = casesData?.cases.find((c) => c.id === scheduleCaseId);
    if (!selectedCase) {
      setFormError('Invalid case selection');
      return;
    }

    // Combine selectedDate and scheduleTime
    const [hours, minutes] = scheduleTime.split(':');
    const hearingDateTime = new Date(selectedDate);
    hearingDateTime.setHours(Number(hours));
    hearingDateTime.setMinutes(Number(minutes));
    hearingDateTime.setSeconds(0);

    scheduleHearingMutation.mutate({
      caseId: scheduleCaseId,
      courtId: selectedCase.courtId,
      hearingDate: hearingDateTime.toISOString(),
      location: scheduleLocation,
      notes: scheduleNotes,
    });
  };

  return (
    <div className="min-h-screen bg-slate-50 flex flex-col">
      {/* Top Header */}
      <div className="bg-white border-b border-gray-100 py-5 px-8 flex items-center justify-between shrink-0">
        <div>
          <h1 className="text-2xl font-black text-gray-800 tracking-tight">Docket Calendar</h1>
          <p className="text-gray-500 text-xs mt-1 font-semibold uppercase tracking-wider">
            Hearing Schedules & Court Room Allocations
          </p>
        </div>
        {user?.role !== 'CLIENT' && user?.role !== 'LAWYER' && (
          <button
            onClick={() => setIsModalOpen(true)}
            className="py-2.5 px-4 bg-court-700 hover:bg-court-800 text-white font-bold rounded-xl shadow-md transition-all flex items-center gap-2 text-sm"
          >
            <PlusCircle className="w-4.5 h-4.5" />
            <span>Schedule Hearing</span>
          </button>
        )}
      </div>

      {/* Main Layout Area */}
      <div className="flex-1 p-6 md:p-8 grid grid-cols-1 lg:grid-cols-3 gap-8 overflow-y-auto">
        {/* Calendar Grid Section */}
        <div className="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col gap-4">
          {/* Calendar Header Controls */}
          <div className="flex items-center justify-between">
            <h2 className="text-xl font-bold text-gray-800">
              {currentDate.toLocaleString('default', { month: 'long', year: 'numeric' })}
            </h2>
            <div className="flex gap-2">
              <button
                onClick={handlePrevMonth}
                className="p-2 hover:bg-gray-50 border border-gray-100 rounded-lg text-gray-600 hover:text-gray-800 transition-colors"
              >
                <ChevronLeft className="w-5 h-5" />
              </button>
              <button
                onClick={handleNextMonth}
                className="p-2 hover:bg-gray-50 border border-gray-100 rounded-lg text-gray-600 hover:text-gray-800 transition-colors"
              >
                <ChevronRight className="w-5 h-5" />
              </button>
            </div>
          </div>

          {/* Month Grid */}
          <div className="flex-1 grid grid-cols-7 gap-1 text-center min-h-[350px]">
            {/* Week Headers */}
            {['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map((d) => (
              <div key={d} className="py-2 text-xs font-bold text-gray-400 uppercase tracking-wider">
                {d}
              </div>
            ))}

            {/* Days Grid */}
            {calendarDays.map((date, idx) => {
              if (!date) {
                return <div key={idx} className="bg-slate-50/20 rounded-xl" />;
              }

              const hearingsCount = getHearingsForDate(date)?.length || 0;
              const isSelected =
                selectedDate &&
                selectedDate.getDate() === date.getDate() &&
                selectedDate.getMonth() === date.getMonth() &&
                selectedDate.getFullYear() === date.getFullYear();

              return (
                <button
                  key={idx}
                  onClick={() => setSelectedDate(date)}
                  className={`p-2 rounded-xl border flex flex-col justify-between items-center transition-all ${
                    isSelected
                      ? 'bg-court-950 border-court-950 text-white shadow-md'
                      : 'bg-white border-gray-100 hover:bg-slate-50 text-gray-700'
                  }`}
                >
                  <span className="text-sm font-bold">{date.getDate()}</span>
                  {hearingsCount > 0 && (
                    <span
                      className={`w-2 h-2 rounded-full mt-1 ${
                        isSelected ? 'bg-white' : 'bg-court-500'
                      }`}
                    />
                  )}
                </button>
              );
            })}
          </div>
        </div>

        {/* Selected Date Listings Section */}
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6">
          <div className="border-b border-gray-50 pb-4">
            <h3 className="font-bold text-gray-800 text-lg">
              Schedules for{' '}
              {selectedDate
                ? selectedDate.toLocaleDateString('en-IN', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                  })
                : 'Select a Date'}
            </h3>
          </div>

          <div className="space-y-4 overflow-y-auto max-h-[450px] pr-1">
            {!selectedDate ? (
              <p className="text-gray-400 text-sm font-medium">Select a calendar date slot to view trials.</p>
            ) : !getHearingsForDate(selectedDate) || getHearingsForDate(selectedDate)!.length === 0 ? (
              <p className="text-gray-400 text-sm font-medium">No trials scheduled for this day.</p>
            ) : (
              getHearingsForDate(selectedDate)!.map((h) => (
                <div key={h.id} className="p-4 rounded-xl bg-slate-50/50 border border-gray-100 space-y-3">
                  <div className="flex items-start justify-between gap-2">
                    <div>
                      <span className="font-bold text-sm text-gray-800 block">Case: {h.case.caseNumber}</span>
                      <span className="text-xs text-gray-400 font-semibold block truncate mt-0.5 max-w-[200px]">
                        {h.case.title}
                      </span>
                    </div>
                    <span className="px-2 py-0.5 text-[9px] font-bold bg-amber-50 text-amber-800 rounded-full shrink-0">
                      {h.status}
                    </span>
                  </div>

                  <div className="space-y-1.5 pt-1.5 border-t border-gray-100/50 text-xs font-semibold text-gray-600">
                    <div className="flex items-center gap-2">
                      <Clock className="w-4 h-4 text-gray-400" />
                      <span>
                        {new Date(h.hearingDate).toLocaleTimeString('en-IN', {
                          hour: '2-digit',
                          minute: '2-digit',
                          hour12: true,
                        })}
                      </span>
                    </div>
                    <div className="flex items-center gap-2">
                      <MapPin className="w-4 h-4 text-gray-400" />
                      <span>{h.location}</span>
                    </div>
                    {h.notes && (
                      <div className="flex items-start gap-2 pt-1">
                        <FileText className="w-4 h-4 text-gray-400 mt-0.5" />
                        <span className="font-medium text-gray-500 leading-relaxed">{h.notes}</span>
                      </div>
                    )}
                  </div>
                </div>
              ))
            )}
          </div>
        </div>
      </div>

      {/* Schedule Hearing Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-md bg-white rounded-2xl shadow-xl p-6 border border-gray-100 animate-scale-in">
            {/* Modal Header */}
            <div className="flex items-center justify-between pb-4 border-b border-gray-100">
              <div className="flex items-center gap-2">
                <CalendarIcon className="w-5 h-5 text-court-700" />
                <h3 className="text-lg font-bold text-gray-800">Schedule Hearing Slot</h3>
              </div>
              <button onClick={() => setIsModalOpen(false)} className="text-gray-400 hover:text-gray-600">
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Notification alert states */}
            {formError && (
              <div className="mt-3 bg-red-50 border-l-4 border-red-500 text-red-700 p-2.5 rounded-lg flex gap-2 text-xs">
                <AlertCircle className="w-4.5 h-4.5 shrink-0" />
                <span>{formError}</span>
              </div>
            )}

            {formSuccess && (
              <div className="mt-3 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-2.5 rounded-lg flex gap-2 text-xs">
                <CheckCircle className="w-4.5 h-4.5 shrink-0" />
                <span>{formSuccess}</span>
              </div>
            )}

            {/* Form */}
            <form onSubmit={handleScheduleSubmit} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">
                  Select Case File
                </label>
                <select
                  value={scheduleCaseId}
                  onChange={(e) => setScheduleCaseId(e.target.value)}
                  className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-800 focus:outline-none focus:ring-1 focus:ring-court-500"
                  required
                >
                  <option value="">Choose active case suit</option>
                  {casesData?.cases?.map((c) => (
                    <option key={c.id} value={c.id}>
                      {c.caseNumber} - {c.title.substring(0, 30)}...
                    </option>
                  ))}
                </select>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">
                    Selected Day
                  </label>
                  <input
                    type="text"
                    value={
                      selectedDate
                        ? selectedDate.toLocaleDateString('en-IN', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric',
                          })
                        : ''
                    }
                    className="w-full px-3 py-2 bg-gray-150 border border-gray-200 rounded-xl text-sm font-semibold text-gray-500 focus:outline-none"
                    disabled
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">
                    Start Time
                  </label>
                  <input
                    type="time"
                    value={scheduleTime}
                    onChange={(e) => setScheduleTime(e.target.value)}
                    className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-800 focus:outline-none focus:ring-1 focus:ring-court-500"
                    required
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">
                  Court Room Allocation
                </label>
                <select
                  value={scheduleLocation}
                  onChange={(e) => setScheduleLocation(e.target.value)}
                  className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-800 focus:outline-none focus:ring-1 focus:ring-court-500"
                >
                  <option value="Court Room #1">Court Room #1 (Main Hall)</option>
                  <option value="Court Room #2">Court Room #2</option>
                  <option value="Court Room #3">Court Room #3</option>
                  <option value="Chambers Room #A">Chambers Room #A</option>
                </select>
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">
                  Filing Notes / Agenda
                </label>
                <textarea
                  value={scheduleNotes}
                  onChange={(e) => setScheduleNotes(e.target.value)}
                  className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-800 h-20 resize-none focus:outline-none focus:ring-1 focus:ring-court-500"
                  placeholder="State hearing agenda details..."
                />
              </div>

              <div className="pt-4 flex justify-end gap-2">
                <button
                  type="button"
                  onClick={() => setIsModalOpen(false)}
                  className="px-4 py-2 bg-gray-105 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={scheduleHearingMutation.isPending}
                  className="px-4 py-2 bg-court-700 hover:bg-court-800 text-white rounded-xl text-xs font-bold flex items-center gap-1.5"
                >
                  {scheduleHearingMutation.isPending ? (
                    <Loader2 className="w-4.5 h-4.5 animate-spin" />
                  ) : (
                    <span>Reserve Slot</span>
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

export default CalendarView;
