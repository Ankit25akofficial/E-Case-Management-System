# 🏛️ E-Case Management System - Digital India Initiative

Transforming legal processes through cutting-edge technology. Empowering judiciary with digital workflow solutions.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4.4-blueviolet)](https://tailwindcss.com)
[![React](https://img.shields.io/badge/React-19.2.6-blue)](https://react.dev)
[![TypeScript](https://img.shields.io/badge/TypeScript-6.0.2-blue)](https://www.typescriptlang.org/)
[![Prisma](https://img.shields.io/badge/Prisma-ORM-teal)](https://www.prisma.io/)

A modern, enterprise-grade Judicial Management Platform built to replace legacy server-rendered architectures with a high-fidelity, type-safe Single Page Application (React) and a scalable REST/WebSocket API (Node.js/Express).

---

## 🏗️ System Architecture

The application is structured as a modern monorepo with decoupled frontend and backend layers:

```mermaid
graph TD
    subgraph Client Layer [Frontend - React SPA]
        A[Browser / Client App]
        A1[React / TypeScript / Tailwind CSS]
        A2[Zustand - Session State]
        A3[React Query - Server State Caching]
        A4[Socket.IO Client - Live Alerts]
    end

    subgraph Gateway Layer [Nginx Reverse Proxy / Load Balancer]
        B[Nginx / Proxy Routing]
    end

    subgraph Application Layer [Backend API Service]
        C[Express.js / Node.js / TS Server]
        C1[Auth Middleware / JWT Validation]
        C2[RBAC Guard / Custom Middleware]
        C3[Business Logic Services]
        C4[Real-time Manager Socket.IO]
        C5[Gemini AI Service Adapter]
    end

    subgraph Data & Caching Layer
        D[(SQLite File-based Database)]
        E[(Redis Cache & In-Memory Fallback)]
    end

    A --> B
    B --> C
    C -->|Prisma Client| D
    C -->|Redis / Map| E
```

---

## 🗄️ Database Design (Entity Relationship Diagram)

The underlying schema models complex judicial relationships, supporting full Role-Based Access Controls (RBAC), auditing trails, and task delegations:

```mermaid
erDiagram
    User ||--o| UserProfile : "has profile"
    User ||--o{ Case : "files (Client) / adjudicates (Judge) / represents (Lawyer)"
    User ||--o{ Document : "uploads"
    User ||--o{ Notification : "receives"
    User ||--o{ Message : "sends/receives"
    User ||--o{ Task : "performs"
    User ||--o{ Appointment : "sets"
    User ||--o{ AuditLog : "triggers"
    User ||--o{ CaseNote : "writes"
    User ||--o{ ActivityLog : "records activity"
    
    Court ||--o{ Case : "administers"
    Court ||--o{ Hearing : "holds"
    
    Case ||--o{ Hearing : "schedules"
    Case ||--o{ Document : "contains"
    Case ||--o{ EvidenceRecord : "stores evidence"
    Case ||--o{ CaseNote : "holds remarks"
    Case ||--o{ Task : "tracks duties"
```

---

## ✨ Features

### 🛡️ Role-Based Access Control (RBAC)
- **Super Admin & Court Admin**: Full control over user registers, account activation status, system configurations, and complete cryptographic audit trails.
- **Judge**: Case status updates, docket scheduling, document review, digital signing, and AI case summaries.
- **Lawyer**: Filing claims, reviewing dockets, client consultations room, uploading pleadings/evidence, and signing documents.
- **Client**: Case status updates, viewing timelines, uploading personal evidence, and text consultations.

### ⚙️ Core Functionality
- **Case Registries & Timeline**: Auto-generated case numbers (`ECMS-YYYY-XXXX`) and chronological event aggregator.
- **Conflict-Safe Hearings Docket**: Prevents concurrent schedules for the same judge within a 1-hour window. Month-by-month calendar view.
- **Documents & Signature Engine**: Multipart secure uploads, status workflow (Pending, Approved, Rejected), and digital signing hash logs.
- **Consultation Rooms**: Direct messaging channel over persistent WebSockets (Socket.IO) for real-time messaging, updates, and online presence tracking.
- **AI Judicial Assistant**: Powered by the Gemini API (`gemini-1.5-flash`) to compile case summaries and generate strategic legal insights. Includes a local fallback text synthesizer if the API key is not configured.
- **Personal Tasks & Notes Checklist**: In-dashboard and case-specific checklist widgets and official case notes logs.

---

## 🛠️ Setup & Installation

### Prerequisities
- Node.js (v18+)
- npm (v9+)
- Docker & Docker Compose (Optional)

### Step 1: Environment Configuration
Create a `.env` file in the `backend/` directory:
```env
PORT=5000
DATABASE_URL="file:./dev.db"
JWT_ACCESS_SECRET="your-super-secret-access-key-goes-here-make-it-long"
JWT_REFRESH_SECRET="your-super-secret-refresh-key-goes-here"
GEMINI_API_KEY="your-gemini-api-key"
NODE_ENV=development
```

### Step 2: Database Initialization
Install dependencies, generate Prisma models, and seed the SQLite database:
```bash
cd backend
npm install
npx prisma generate
npx prisma db push
npm run prisma:seed
```

### Step 3: Launch Services

#### Development Mode:
Start the backend dev server (auto-reloads on edits):
```bash
cd backend
npm run dev
```

In another terminal, start the frontend Vite server:
```bash
cd ../frontend
npm install
npm run dev
```
Open `http://localhost:5173/` in your browser.

#### Production Mode (Docker):
Build and launch the complete stack containing PostgreSQL, Redis, Backend REST/Socket server, and Nginx serving the React SPA:
```bash
docker-compose up --build
```

---

## 🧪 Testing
Run backend Jest tests:
```bash
cd backend
npm run test
```

## 🔒 Security Auditing Highlights
- Short-lived Access Tokens (stored in memory) and long-lived Refresh Tokens (stored in secure `HttpOnly`, `SameSite` cookies).
- Cryptographic Audit Trail logs for every user mutation, profile status change, and file submission.
- Fully parameter-bound queries via Prisma ORM to prevent SQL Injection.
- Clean file uploads validation logic (enforcing MIME extensions and 10MB limits).
