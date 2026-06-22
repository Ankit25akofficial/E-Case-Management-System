import express from 'express';
import cors from 'cors';
import cookieParser from 'cookie-parser';
import helmet from 'helmet';
import dotenv from 'dotenv';
import http from 'http';
import { initSocket } from './socket';
import authRoutes from './routes/authRoutes';
import caseRoutes from './routes/caseRoutes';
import documentRoutes from './routes/documentRoutes';
import hearingRoutes from './routes/hearingRoutes';
import messageRoutes from './routes/messageRoutes';
import taskRoutes from './routes/taskRoutes';
import aiRoutes from './routes/aiRoutes';
import adminRoutes from './routes/adminRoutes';

// Load environment variables
dotenv.config();

const app = express();
const PORT = process.env.PORT || 5000;
const server = http.createServer(app);

// Initialize Socket.IO WebSockets Server
initSocket(server);

// Security Headers
app.use(helmet());

// CORS configuration
app.use(
  cors({
    origin: process.env.CLIENT_URL || 'http://localhost:5173',
    credentials: true,
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization'],
  })
);

// Body Parsers
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(cookieParser());

// Mount API Routes
app.use('/api/v1/auth', authRoutes);
app.use('/api/v1/cases', caseRoutes);
app.use('/api/v1/documents', documentRoutes);
app.use('/api/v1/hearings', hearingRoutes);
app.use('/api/v1/messages', messageRoutes);
app.use('/api/v1/tasks', taskRoutes);
app.use('/api/v1/ai', aiRoutes);
app.use('/api/v1/admin', adminRoutes);

// Base Health Endpoint
app.get('/health', (req, res) => {
  res.status(200).json({
    status: 'UP',
    timestamp: new Date().toISOString(),
    message: 'Judicial Management Platform API is running smoothly',
  });
});

// Start HTTP + WebSockets Server
server.listen(PORT, () => {
  console.log(`[Server] Running in ${process.env.NODE_ENV} mode on port ${PORT}`);
});

export default app;

