import { Server as HttpServer } from 'http';
import { Server as SocketServer, Socket } from 'socket.io';

let io: SocketServer | null = null;
const onlineUsers = new Map<string, string>(); // Maps userId -> socketId

export const initSocket = (server: HttpServer): SocketServer => {
  io = new SocketServer(server, {
    cors: {
      origin: process.env.CLIENT_URL || 'http://localhost:5173',
      credentials: true,
    },
  });

  io.on('connection', (socket: Socket) => {
    console.log(`[Socket] Client connected: ${socket.id}`);

    // Map user to socket connection on authentication event
    socket.on('register_user', (userId: string) => {
      onlineUsers.set(userId, socket.id);
      console.log(`[Socket] User registered: ${userId} -> Socket: ${socket.id}`);
    });

    // Handle disconnecting
    socket.on('disconnect', () => {
      // Find and remove mapping
      for (const [userId, socketId] of onlineUsers.entries()) {
        if (socketId === socket.id) {
          onlineUsers.delete(userId);
          console.log(`[Socket] User unregistered on disconnect: ${userId}`);
          break;
        }
      }
      console.log(`[Socket] Client disconnected: ${socket.id}`);
    });
  });

  return io;
};

export const getIO = (): SocketServer => {
  if (!io) {
    throw new Error('Socket.IO is not initialized!');
  }
  return io;
};

export const sendToUser = (userId: string, event: string, data: any): boolean => {
  const socketId = onlineUsers.get(userId);
  if (socketId && io) {
    io.to(socketId).emit(event, data);
    return true;
  }
  return false;
};
