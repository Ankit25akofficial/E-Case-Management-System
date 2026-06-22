import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export interface User {
  id: string;
  username: string;
  email: string;
  fullName: string;
  role: 'SUPER_ADMIN' | 'COURT_ADMIN' | 'JUDGE' | 'LAWYER' | 'CLIENT' | 'CLERK' | 'STAFF';
}

interface AuthState {
  user: User | null;
  accessToken: string | null;
  isAuthenticated: boolean;
  login: (accessToken: string, user: User) => void;
  logout: () => void;
  setAccessToken: (token: string) => void;
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set) => ({
      user: null,
      accessToken: null,
      isAuthenticated: false,
      login: (accessToken, user) => set({ accessToken, user, isAuthenticated: true }),
      logout: () => set({ accessToken: null, user: null, isAuthenticated: false }),
      setAccessToken: (accessToken) => set({ accessToken, isAuthenticated: true }),
    }),
    {
      name: 'auth-storage',
    }
  )
);
