import React, { useState, useEffect, useRef } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Send,
  MessageSquare,
  Search,
  User,
  Gavel,
  CheckCheck,
  PlusCircle,
  X,
  Loader2,
} from 'lucide-react';
import api from '../utils/api';
import socket from '../utils/socket';
import { useAuthStore } from '../store/useAuthStore';

interface UserContact {
  id: string;
  fullName: string;
  username: string;
  role: string;
}

interface Conversation {
  partner: {
    id: string;
    fullName: string;
    role: string;
  };
  lastMessage: {
    content: string;
    createdAt: string;
    senderId: string;
    isRead: boolean;
  };
}

interface Message {
  id: string;
  senderId: string;
  receiverId: string;
  content: string;
  createdAt: string;
  sender: {
    fullName: string;
    role: string;
  };
}

const Chat: React.FC = () => {
  const queryClient = useQueryClient();
  const { user } = useAuthStore();

  const [activePartner, setActivePartner] = useState<Conversation['partner'] | null>(null);
  const [messages, setMessages] = useState<Message[]>([]);
  const [inputContent, setInputContent] = useState('');
  const [isNewChatModalOpen, setIsNewChatModalOpen] = useState(false);
  const [contactSearch, setContactSearch] = useState('');

  const chatEndRef = useRef<HTMLDivElement>(null);

  // 1. Fetch active conversations list
  const { data: conversations, isLoading: isConversationsLoading } = useQuery<Conversation[]>({
    queryKey: ['conversations'],
    queryFn: async () => {
      const res = await api.get('/messages/conversations');
      return res.data;
    },
  });

  // 2. Fetch contact users directory
  const { data: contacts } = useQuery<UserContact[]>({
    queryKey: ['contacts'],
    queryFn: async () => {
      const res = await api.get('/messages/users');
      return res.data;
    },
    enabled: isNewChatModalOpen,
  });

  // 3. Fetch message thread for active partner
  const fetchThread = async (partnerId: string) => {
    try {
      const res = await api.get(`/messages/${partnerId}`);
      setMessages(res.data);
    } catch (err) {
      console.error('Failed to load message thread', err);
    }
  };

  useEffect(() => {
    if (activePartner) {
      fetchThread(activePartner.id);
    } else {
      setMessages([]);
    }
  }, [activePartner]);

  // 4. Socket real-time message listener
  useEffect(() => {
    const handleNewMessage = (msg: Message) => {
      // If message belongs to active partner, append immediately
      if (activePartner && (msg.senderId === activePartner.id || msg.receiverId === activePartner.id)) {
        setMessages((prev) => [...prev, msg]);
        // Call read marker API
        api.get(`/messages/${activePartner.id}`).catch(() => null);
      }
      // Invalidate query to update conversation list sidebar
      queryClient.invalidateQueries({ queryKey: ['conversations'] });
    };

    socket.on('new_message', handleNewMessage);

    return () => {
      socket.off('new_message', handleNewMessage);
    };
  }, [activePartner, queryClient]);

  // Scroll to bottom on new message
  useEffect(() => {
    chatEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  // 5. Send Message Mutation
  const sendMessageMutation = useMutation({
    mutationFn: async (payload: { receiverId: string; content: string }) => {
      const res = await api.post('/messages', payload);
      return res.data;
    },
    onSuccess: (newMessage: Message) => {
      setMessages((prev) => [...prev, newMessage]);
      setInputContent('');
      queryClient.invalidateQueries({ queryKey: ['conversations'] });
    },
  });

  const handleSendMessageSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!inputContent.trim() || !activePartner) return;

    sendMessageMutation.mutate({
      receiverId: activePartner.id,
      content: inputContent.trim(),
    });
  };

  const startNewChat = (contact: UserContact) => {
    setActivePartner({
      id: contact.id,
      fullName: contact.fullName,
      role: contact.role,
    });
    setIsNewChatModalOpen(false);
  };

  const filteredContacts = contacts?.filter((c) => {
    const term = contactSearch.toLowerCase();
    return c.fullName.toLowerCase().includes(term) || c.role.toLowerCase().includes(term);
  });

  return (
    <div className="min-h-screen bg-slate-50 flex">
      {/* Messages Sidebar */}
      <div className="w-80 bg-white border-r border-gray-150 flex flex-col shrink-0">
        <div className="p-5 border-b border-gray-100 flex items-center justify-between">
          <h2 className="text-xl font-bold text-gray-800 flex items-center gap-2">
            <MessageSquare className="w-5 h-5 text-court-700" />
            <span>Consultations</span>
          </h2>
          <button
            onClick={() => setIsNewChatModalOpen(true)}
            className="p-1.5 hover:bg-gray-50 rounded-lg text-court-700 transition-colors"
          >
            <PlusCircle className="w-5 h-5" />
          </button>
        </div>

        {/* Conversations List */}
        <div className="flex-1 overflow-y-auto p-3 space-y-1">
          {isConversationsLoading ? (
            <div className="p-8 flex justify-center">
              <Loader2 className="w-6 h-6 text-court-600 animate-spin" />
            </div>
          ) : !conversations || conversations.length === 0 ? (
            <div className="p-8 text-center text-gray-400 text-xs font-semibold">
              No consultations started. Click the plus icon to message lawyers/judges.
            </div>
          ) : (
            conversations.map((conv) => {
              const isSelected = activePartner?.id === conv.partner.id;
              const hasUnread = !conv.lastMessage.isRead && conv.lastMessage.senderId === conv.partner.id;

              return (
                <button
                  key={conv.partner.id}
                  onClick={() => setActivePartner(conv.partner)}
                  className={`w-full text-left p-3.5 rounded-xl transition-all flex items-start gap-3 ${
                    isSelected
                      ? 'bg-court-950 text-white shadow-md'
                      : 'hover:bg-slate-50 text-gray-700'
                  }`}
                >
                  <div
                    className={`w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0 uppercase ${
                      isSelected ? 'bg-court-800 text-court-200' : 'bg-court-50 text-court-700'
                    }`}
                  >
                    {conv.partner.role === 'JUDGE' ? (
                      <Gavel className="w-4 h-4" />
                    ) : (
                      <User className="w-4 h-4" />
                    )}
                  </div>
                  <div className="truncate flex-1 min-w-0">
                    <div className="flex justify-between items-center gap-2">
                      <span className="font-bold text-sm truncate">{conv.partner.fullName}</span>
                      <span
                        className={`text-[9px] font-black uppercase tracking-wider ${
                          isSelected ? 'text-court-400' : 'text-gray-400'
                        }`}
                      >
                        {conv.partner.role}
                      </span>
                    </div>
                    <p
                      className={`text-xs mt-1 truncate ${
                        isSelected
                          ? 'text-court-200'
                          : hasUnread
                          ? 'font-bold text-gray-900'
                          : 'text-gray-500'
                      }`}
                    >
                      {conv.lastMessage.content}
                    </p>
                  </div>
                  {hasUnread && (
                    <span className="w-2.5 h-2.5 rounded-full bg-orange-500 shrink-0 self-center" />
                  )}
                </button>
              );
            })
          )}
        </div>
      </div>

      {/* Message Screen Area */}
      <div className="flex-1 flex flex-col min-w-0 bg-slate-50">
        {activePartner ? (
          <>
            {/* Header info */}
            <div className="h-16 bg-white border-b border-gray-150 flex items-center justify-between px-6 shrink-0 shadow-sm">
              <div className="flex items-center gap-3">
                <div className="w-9 h-9 rounded-full bg-court-50 text-court-700 flex items-center justify-center font-bold">
                  {activePartner.role === 'JUDGE' ? (
                    <Gavel className="w-4 h-4" />
                  ) : (
                    <User className="w-4 h-4" />
                  )}
                </div>
                <div>
                  <span className="font-bold text-gray-800 text-sm block">{activePartner.fullName}</span>
                  <span className="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">
                    {activePartner.role.replace('_', ' ')}
                  </span>
                </div>
              </div>
            </div>

            {/* Messages Thread list */}
            <div className="flex-1 overflow-y-auto p-6 space-y-4">
              {messages.map((msg) => {
                const isMine = msg.senderId === user?.id;

                return (
                  <div key={msg.id} className={`flex ${isMine ? 'justify-end' : 'justify-start'}`}>
                    <div
                      className={`max-w-md p-3.5 rounded-2xl text-sm shadow-sm ${
                        isMine
                          ? 'bg-court-700 text-white rounded-tr-none'
                          : 'bg-white text-gray-800 border border-gray-100 rounded-tl-none'
                      }`}
                    >
                      <p className="leading-relaxed font-medium whitespace-pre-wrap">{msg.content}</p>
                      <div className="flex items-center justify-end gap-1.5 mt-1.5 text-[9px] opacity-75 font-semibold">
                        <span>
                          {new Date(msg.createdAt).toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit',
                          })}
                        </span>
                        {isMine && <CheckCheck className="w-3 h-3 text-court-200" />}
                      </div>
                    </div>
                  </div>
                );
              })}
              <div ref={chatEndRef} />
            </div>

            {/* Input Form Box */}
            <div className="p-4 bg-white border-t border-gray-150 shrink-0">
              <form onSubmit={handleSendMessageSubmit} className="flex gap-3">
                <input
                  type="text"
                  value={inputContent}
                  onChange={(e) => setInputContent(e.target.value)}
                  className="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-court-500 text-gray-800"
                  placeholder={`Write your message to ${activePartner.fullName}...`}
                />
                <button
                  type="submit"
                  disabled={!inputContent.trim() || sendMessageMutation.isPending}
                  className="py-2.5 px-4 bg-court-700 hover:bg-court-800 text-white font-bold rounded-xl text-sm transition-all disabled:opacity-50 flex items-center justify-center"
                >
                  <Send className="w-4 h-4" />
                </button>
              </form>
            </div>
          </>
        ) : (
          <div className="flex-1 flex flex-col items-center justify-center p-8 text-center text-gray-400 gap-3">
            <MessageSquare className="w-16 h-16 text-gray-200" />
            <div>
              <span className="font-bold text-gray-700 text-sm block">Select a Consultation Room</span>
              <span className="text-xs text-gray-400 font-semibold block mt-1">
                Choose a contact from the sidebar or start a new chat thread.
              </span>
            </div>
          </div>
        )}
      </div>

      {/* Start Chat Modal */}
      {isNewChatModalOpen && (
        <div className="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-md bg-white rounded-2xl shadow-xl p-6 border border-gray-100 animate-scale-in">
            <div className="flex items-center justify-between pb-4 border-b border-gray-100">
              <h3 className="text-lg font-bold text-gray-800">Start Consultation</h3>
              <button onClick={() => setIsNewChatModalOpen(false)} className="text-gray-400 hover:text-gray-600">
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="relative my-4">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <Search className="w-4 h-4" />
              </div>
              <input
                type="text"
                value={contactSearch}
                onChange={(e) => setContactSearch(e.target.value)}
                className="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none"
                placeholder="Search user name or role..."
              />
            </div>

            <div className="overflow-y-auto max-h-60 space-y-1 pr-1">
              {!filteredContacts || filteredContacts.length === 0 ? (
                <p className="text-center text-xs text-gray-400 py-4 font-semibold">No contacts found</p>
              ) : (
                filteredContacts.map((contact) => (
                  <button
                    key={contact.id}
                    onClick={() => startNewChat(contact)}
                    className="w-full text-left p-3 hover:bg-slate-50 rounded-xl transition-all flex items-center justify-between gap-3 text-sm font-semibold text-gray-800"
                  >
                    <span>{contact.fullName}</span>
                    <span className="text-[10px] text-gray-400 uppercase tracking-wider">{contact.role}</span>
                  </button>
                ))
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default Chat;
