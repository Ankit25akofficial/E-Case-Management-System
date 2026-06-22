import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { Gavel, Shield, Lock, User, AlertCircle, Eye, EyeOff } from 'lucide-react';
import api from '../utils/api';
import { useAuthStore } from '../store/useAuthStore';

const Login: React.FC = () => {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  const login = useAuthStore((state) => state.login);
  const navigate = useNavigate();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!username || !password) {
      setError('Please fill in all fields');
      return;
    }

    setIsLoading(true);
    setError(null);

    try {
      const response = await api.post('/auth/login', { username, password });
      const { accessToken, user } = response.data;
      login(accessToken, user);
      navigate('/dashboard');
    } catch (err: any) {
      setError(err.response?.data?.message || 'Login failed. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-tr from-court-950 via-court-900 to-court-800 flex items-center justify-center p-4 relative overflow-hidden">
      {/* Decorative Blur Spheres */}
      <div className="absolute top-[-10%] left-[-10%] w-96 h-96 rounded-full bg-blue-500/10 blur-3xl pointer-events-none" />
      <div className="absolute bottom-[-10%] right-[-10%] w-96 h-96 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none" />

      <div className="w-full max-w-md">
        {/* Emblem/Logo */}
        <div className="text-center mb-6 animate-fade-in">
          <div className="inline-flex p-3 rounded-full bg-gradient-to-br from-court-500 to-court-700 text-white shadow-xl mb-3 ring-4 ring-court-800">
            <Gavel className="w-8 h-8" />
          </div>
          <h1 className="text-3xl font-extrabold text-white tracking-tight">E-Case Management</h1>
          <p className="text-court-300 text-sm mt-1 font-medium">Judicial Management Platform | Government of India</p>
        </div>

        {/* Card */}
        <div className="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-white/20 p-8">
          <h2 className="text-2xl font-bold text-gray-800 text-center mb-6">Portal Sign In</h2>

          {error && (
            <div className="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded-lg flex items-start gap-2 mb-6 text-sm">
              <AlertCircle className="w-5 h-5 shrink-0 mt-0.5" />
              <span>{error}</span>
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-5">
            {/* Username */}
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-1.5">Username</label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                  <User className="w-5 h-5" />
                </div>
                <input
                  type="text"
                  value={username}
                  onChange={(e) => setUsername(e.target.value)}
                  className="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-court-500 focus:border-court-500 transition-all font-medium text-gray-800"
                  placeholder="Enter your username"
                  required
                />
              </div>
            </div>

            {/* Password */}
            <div>
              <div className="flex justify-between items-center mb-1.5">
                <label className="block text-sm font-semibold text-gray-700">Password</label>
                <Link to="/forgot-password" className="text-xs font-semibold text-court-600 hover:text-court-800 transition-colors">
                  Forgot Password?
                </Link>
              </div>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                  <Lock className="w-5 h-5" />
                </div>
                <input
                  type={showPassword ? 'text' : 'password'}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="w-full pl-11 pr-11 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-court-500 focus:border-court-500 transition-all font-medium text-gray-800"
                  placeholder="••••••••"
                  required
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                >
                  {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                </button>
              </div>
            </div>

            {/* Remember Me */}
            <div className="flex items-center">
              <input
                id="remember_me"
                type="checkbox"
                className="h-4 w-4 text-court-600 focus:ring-court-500 border-gray-300 rounded"
              />
              <label htmlFor="remember_me" className="ml-2 block text-sm text-gray-600 font-medium">
                Keep me logged in
              </label>
            </div>

            {/* Submit Button */}
            <button
              type="submit"
              disabled={isLoading}
              className="w-full py-3 px-4 bg-gradient-to-r from-court-700 to-court-600 text-white font-bold rounded-xl hover:from-court-800 hover:to-court-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-court-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-2"
            >
              {isLoading ? (
                <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
              ) : (
                <>
                  <Shield className="w-5 h-5" />
                  <span>Secure Login</span>
                </>
              )}
            </button>
          </form>

          {/* Registration Link */}
          <div className="text-center mt-6 pt-5 border-t border-gray-100">
            <p className="text-sm text-gray-600">
              New client?{' '}
              <Link to="/register" className="font-bold text-court-600 hover:text-court-800 transition-colors">
                Register a new account
              </Link>
            </p>
          </div>
        </div>

        {/* Security Notice */}
        <p className="text-center text-court-400 text-xs mt-6 font-medium leading-relaxed max-w-sm mx-auto">
          Notice: This is a secure system. Unauthorized access attempts are audited and subject to legal action under current IT security statutes.
        </p>
      </div>
    </div>
  );
};

export default Login;
