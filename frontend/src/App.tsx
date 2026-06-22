import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useAuthStore } from './store/useAuthStore';
import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';
import Cases from './pages/Cases';
import CaseDetails from './pages/CaseDetails';
import CalendarView from './pages/CalendarView';
import Chat from './pages/Chat';
import Layout from './components/Layout';
import Documents from './pages/Documents';
import Users from './pages/Users';
import Audits from './pages/Audits';

import socket from './utils/socket';

// Initialize React Query Client for server state queries
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      retry: 1,
    },
  },
});

// Protect private routes from unauthenticated session views
const PrivateRoute: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const isAuthenticated = useAuthStore((state) => state.isAuthenticated);
  return isAuthenticated ? <>{children}</> : <Navigate to="/login" replace />;
};

const App: React.FC = () => {
  const { isAuthenticated, user } = useAuthStore();

  React.useEffect(() => {
    if (isAuthenticated && user) {
      socket.connect();
      socket.emit('register_user', user.id);
      
      return () => {
        socket.disconnect();
      };
    }
  }, [isAuthenticated, user]);

  return (
    <QueryClientProvider client={queryClient}>
      <Router>
        <Routes>
          <Route 
            path="/login" 
            element={!isAuthenticated ? <Login /> : <Navigate to="/dashboard" replace />} 
          />
          <Route 
            path="/register" 
            element={!isAuthenticated ? <Register /> : <Navigate to="/dashboard" replace />} 
          />
          <Route
            path="/dashboard"
            element={
              <PrivateRoute>
                <Layout>
                  <Dashboard />
                </Layout>
              </PrivateRoute>
            }
          />
          <Route
            path="/cases"
            element={
              <PrivateRoute>
                <Layout>
                  <Cases />
                </Layout>
              </PrivateRoute>
            }
          />
          <Route
            path="/cases/:id"
            element={
              <PrivateRoute>
                <Layout>
                  <CaseDetails />
                </Layout>
              </PrivateRoute>
            }
          />
          <Route
            path="/hearings"
            element={
              <PrivateRoute>
                <Layout>
                  <CalendarView />
                </Layout>
              </PrivateRoute>
            }
          />
          <Route
            path="/documents"
            element={
              <PrivateRoute>
                <Layout>
                  <Documents />
                </Layout>
              </PrivateRoute>
            }
          />
          <Route
            path="/chat"
            element={
              <PrivateRoute>
                <Layout>
                  <Chat />
                </Layout>
              </PrivateRoute>
            }
          />
          <Route
            path="/users"
            element={
              <PrivateRoute>
                <Layout>
                  <Users />
                </Layout>
              </PrivateRoute>
            }
          />
          <Route
            path="/audits"
            element={
              <PrivateRoute>
                <Layout>
                  <Audits />
                </Layout>
              </PrivateRoute>
            }
          />
          {/* Wildcard redirector */}
          <Route 
            path="*" 
            element={<Navigate to={isAuthenticated ? "/dashboard" : "/login"} replace />} 
          />
        </Routes>
      </Router>
    </QueryClientProvider>
  );
};

export default App;
