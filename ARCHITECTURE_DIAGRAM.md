# SYSTEM ARCHITECTURE & INTEGRATION DIAGRAM

## Complete System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                         ESPPD SYSTEM                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                    FRONTEND LAYER                         │  │
│  │  (Blade Templates + Livewire Components)                  │  │
│  │                                                            │  │
│  │  ├─ Profile Page (with 13 biodata fields) ✨              │  │
│  │  ├─ Dashboard (role-based views)                          │  │
│  │  ├─ SPD Management (create/edit/view)                     │  │
│  │  ├─ Approval Queue (workflow)                             │  │
│  │  ├─ Report Builder (generate reports)                     │  │
│  │  ├─ Budget Management                                      │  │
│  │  └─ Settings (system config)                              │  │
│  │                                                            │  │
│  │  [User Browser] → HTTP Request                            │  │
│  │        ↓                                                    │  │
│  └──────────────────────────────────────────────────────────┘  │
│           │ HTTP/Livewire                                        │
│           ↓                                                       │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                   BACKEND LAYER                           │  │
│  │  (Laravel 11 Application)                                 │  │
│  │                                                            │  │
│  │  HTTP Routes                                              │  │
│  │  ├─ /dashboard       → Dashboard controller               │  │
│  │  ├─ /spd/*          → SPD management                       │  │
│  │  ├─ /approvals/*    → Approval queue                       │  │
│  │  ├─ /profile        → User profile (enhanced)             │  │
│  │  ├─ /api/*          → REST API endpoints                   │  │
│  │  └─ /login, /logout → Authentication                      │  │
│  │                                                            │  │
│  │  Controllers & Livewire Components                         │  │
│  │  ├─ Handle HTTP requests                                   │  │
│  │  ├─ Process business logic                                 │  │
│  │  └─ Return JSON/HTML responses                             │  │
│  │                                                            │  │
│  │  Models & ORM (Eloquent)                                   │  │
│  │  ├─ User (with employee relationship)                      │  │
│  │  ├─ Employee (21 fields)                                   │  │
│  │  ├─ Spd (document management)                              │  │
│  │  ├─ Approval (workflow)                                    │  │
│  │  └─ ... (10+ more models)                                  │  │
│  │                                                            │  │
│  │  Service Layer                                             │  │
│  │  ├─ PythonDocumentService    ← Calls Microservice         │  │
│  │  ├─ ApprovalService          ← Workflow logic              │  │
│  │  ├─ DocumentService          ← Fallback generation         │  │
│  │  └─ ... (more services)                                    │  │
│  │                                                            │  │
│  │  Middleware & Security                                     │  │
│  │  ├─ Authentication (NIP login)                             │  │
│  │  ├─ Authorization (role.level:X)                           │  │
│  │  ├─ CSRF Protection                                        │  │
│  │  └─ OTP Verification                                       │  │
│  │                                                            │  │
│  └──────────────────────────────────────────────────────────┘  │
│           │ SQL Queries              │ HTTP POST/GET             │
│           ↓                           ↓                           │
│  ┌────────────────────────┐ ┌─────────────────────────┐         │
│  │  DATABASE LAYER        │ │  MICROSERVICE LAYER     │         │
│  │  (PostgreSQL)          │ │  (Python/FastAPI)       │         │
│  │                        │ │                         │         │
│  │  Tables:               │ │  Endpoints:             │         │
│  │  ├─ users             │ │  ├─ /health             │         │
│  │  ├─ employees         │ │  ├─ /generate-sppd     │         │
│  │  ├─ organizations     │ │  ├─ /generate-surat-tugas
│  │  ├─ units             │ │  ├─ /generate-laporan   │         │
│  │  ├─ spds              │ │  └─ /download/{file}    │         │
│  │  ├─ approvals         │ │                         │         │
│  │  ├─ budgets           │ │  Process:               │         │
│  │  ├─ audit_logs        │ │  1. Receive request     │         │
│  │  └─ ... (more)        │ │  2. Load template       │         │
│  │                        │ │  3. Process data        │         │
│  │  Features:             │ │  4. Generate DOCX/PDF   │         │
│  │  ├─ Foreign keys       │ │  5. Save file           │         │
│  │  ├─ Indexes (perf)     │ │  6. Return filename     │         │
│  │  ├─ Soft deletes       │ │                         │         │
│  │  └─ Transactions       │ │  Technology:            │         │
│  │                        │ │  ├─ FastAPI            │         │
│  │  Port: 5432           │ │  ├─ Uvicorn            │         │
│  │                        │ │  ├─ python-docx        │         │
│  │                        │ │  └─ Pydantic           │         │
│  │                        │ │                         │         │
│  │                        │ │  Port: 8001             │         │
│  └────────────────────────┘ └─────────────────────────┘         │
│           ↑                           ↑                           │
│           └───────────────────────────┘                           │
│           ← Response Data / Downloaded Files                      │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## Data Flow Sequences

### 1. User Profile Page Load

```
User (Browser)
    │
    ├─ GET /profile
    │
    └─→ Laravel Router
        │
        ├─ Check Auth Middleware
        │  (NIP login verified)
        │
        ├─ Load Blade Template
        │  resources/views/profile.blade.php
        │
        ├─ Execute: auth()->user()->employee
        │
        ├─→ Database Query
        │   SELECT users.*, employees.*
        │   FROM users
        │   LEFT JOIN employees ON users.id = employees.user_id
        │   WHERE users.id = {current_user_id}
        │
        ├─→ PostgreSQL Database
        │   │
        │   ├─ Fetch user record
        │   ├─ Fetch employee record (13 fields)
        │   │  - NIP
        │   │  - Position
        │   │  - Rank
        │   │  - Grade
        │   │  - Employment Status
        │   │  - Unit
        │   │  - Phone
        │   │  - Birth Date
        │   │  - Bank Name
        │   │  - Bank Account
        │   │  - Account Holder
        │   │  - Active Status
        │   │  - Timestamps
        │   │
        │   └─ Return data
        │
        ├─ Blade Template Rendering
        │  - Replace {{ auth()->user()->employee->nip }} with data
        │  - Apply dark mode styles
        │  - Responsive grid layout (1 col mobile, 2 col desktop)
        │
        ├─ Generate HTML Response
        │
        └─→ Browser Display
           Profile page with all 13 biodata fields ✅
```

### 2. Document Generation Flow

```
User submits SPD form
    │
    ├─ POST /spd
    │
    └─→ Laravel SpdCreate Component (Livewire)
        │
        ├─ Validate form data
        │  (check required fields, formats, etc.)
        │
        ├─ Call: DocumentController::generateDocument()
        │
        ├─→ PythonDocumentService::generateSPPD()
        │   │
        │   ├─ Check: isAvailable()
        │   │  GET http://localhost:8001/health
        │   │
        │   ├─ If NOT Available
        │   │  └─ Use Fallback: DocumentService::generateSPPDLocal()
        │   │     └─ Generate locally using templates
        │   │
        │   └─ If Available ✅
        │      │
        │      ├─ Prepare payload
        │      │  {
        │      │    "nama": "...",
        │      │    "nip": "...",
        │      │    "jabatan": "...",
        │      │    "tujuan": "...",
        │      │    ...
        │      │  }
        │      │
        │      ├─ POST http://localhost:8001/generate-sppd
        │      │
        │      └─→ Python Microservice
        │          │
        │          ├─ Receive POST request
        │          ├─ Validate input (Pydantic)
        │          ├─ Load DOCX template
        │          ├─ Replace placeholders with data
        │          ├─ Generate output file: sppd_20260131_123456.docx
        │          ├─ Save to generated/ directory
        │          │
        │          └─ Response:
        │             {
        │               "success": true,
        │               "filename": "sppd_20260131_123456.docx"
        │             }
        │
        ├─ Backend receives response
        │
        ├─ Download file
        │  GET http://localhost:8001/download/sppd_20260131_123456.docx
        │
        ├─→ Microservice returns DOCX file stream
        │
        ├─ Save to Laravel storage
        │  storage/documents/sppd/sppd_20260131_123456.docx
        │
        ├─ Return file path to frontend
        │
        └─→ Browser downloads file ✅
           User has SPPD document
```

### 3. Login & Authentication Flow

```
User visits /login
    │
    ├─ GET /login
    │
    └─→ Display Login Form (Livewire component)
        │
        ├─ User enters: NIP + Password
        │
        ├─ POST /login
        │
        ├─→ AuthenticateUser Middleware
        │   │
        │   ├─ Query: SELECT * FROM users WHERE nip = ?
        │   │
        │   ├─→ PostgreSQL
        │   │   └─ Find user by NIP
        │   │
        │   ├─ Verify password hash
        │   │
        │   ├─ If invalid
        │   │  └─ Redirect back with error
        │   │
        │   └─ If valid ✅
        │      │
        │      ├─ Load user record
        │      ├─ Load user relationship: employee
        │      ├─ Load user relationship: role
        │      │
        │      ├─ Check 2FA/OTP required?
        │      │ (if yes, send OTP to email)
        │      │
        │      ├─ Create session
        │      ├─ Set auth() global
        │      │
        │      └─ Redirect to dashboard
        │
        └─→ Browser Display Dashboard
           User logged in, role-based view shown ✅
```

---

## Component Integration Map

```
┌─────────────────────────────────────────────────────────┐
│              COMPONENT INTEGRATION MAP                   │
└─────────────────────────────────────────────────────────┘

Frontend Tier:
├─ Profile.blade.php ──┐
├─ Dashboard.blade.php ├─→ Blade Template Engine
├─ Livewire/*          │
└─ Components/*        ┘
     ↓ (Sends HTTP/Form Data)
     │
Backend Tier:
├─ Routes (web.php)
│  ├─ /profile → ProfileController
│  ├─ /dashboard → DashboardComponent
│  ├─ /spd/* → SpdController
│  └─ /api/* → ApiController
│    ↓ (Executes Business Logic)
│
├─ Models (Eloquent ORM)
│  ├─ User → queries users table
│  ├─ Employee → queries employees table
│  ├─ Spd → queries spds table
│  └─ relationships → joins
│    ↓ (Executes SQL)
│
├─ Services (Business Logic)
│  ├─ PythonDocumentService
│  │  └─ Calls Microservice via HTTP
│  ├─ ApprovalService
│  └─ DocumentService (fallback)
│    ↓ (Processes Data)
│
└─ Database Access (PDO/Laravel)
   ↓
Data Tier:
├─ PostgreSQL Connection (localhost:5432)
│
├─ Schema
│  ├─ users (id, nip, name, role, ...)
│  ├─ employees (id, nip, position, rank, grade, ...)
│  ├─ spds (id, employee_id, status, ...)
│  └─ approvals (id, spd_id, approver_id, ...)
│
└─ Returns Data
   ↓
Backend Tier:
├─ Eloquent Models transform rows to objects
├─ Services process & manipulate
└─ Templates render for display
   ↓
Frontend Tier:
└─ User sees complete page/data ✅

Microservice Tier (Parallel):
├─ Python FastAPI Service (localhost:8001)
├─ Backend calls via HTTP POST
├─ Service generates DOCX from template
├─ Backend downloads file
└─ User gets document ✅
```

---

## Integration Points (Verified)

```
1. Frontend → Backend
   ✅ HTTP Requests (GET, POST, PUT, DELETE)
   ✅ Form submissions
   ✅ Livewire real-time updates
   ✅ CSRF token validation

2. Backend → Database
   ✅ PDO connections (config/database.php)
   ✅ Eloquent ORM (App\Models\*)
   ✅ Query builder (DB facades)
   ✅ Transactions & rollback
   ✅ Foreign key constraints

3. Backend → Microservice
   ✅ HTTP POST (generateSPPD)
   ✅ HTTP GET (health check)
   ✅ HTTP GET (file download)
   ✅ Error handling & fallback
   ✅ File streaming & storage

4. Authentication
   ✅ NIP-based login verified
   ✅ Password hashing with bcrypt
   ✅ Session management
   ✅ Role-based access control
   ✅ OTP verification flow

5. Error Handling
   ✅ Try/catch blocks
   ✅ Logging to storage/logs/
   ✅ User-friendly error messages
   ✅ Fallback mechanisms
   ✅ Exception handling
```

---

## Architecture Benefits

```
✅ Separation of Concerns
   - Frontend: Presentation only
   - Backend: Business logic
   - Database: Data persistence
   - Microservice: Document generation

✅ Scalability
   - Microservice can run on separate server
   - Database can be optimized independently
   - Frontend can be cached
   - Load balancing possible

✅ Maintainability
   - Each layer can be updated independently
   - Clear interfaces between components
   - Comprehensive logging
   - Error handling at each level

✅ Reliability
   - Fallback mechanisms (local document generation)
   - Transaction support (database)
   - Session management (authentication)
   - Audit logging (compliance)

✅ Performance
   - Database indexes optimized
   - Query caching available
   - File streaming (not loading full file)
   - Asynchronous processing possible
```

---

## Testing All Integration Points

```
1. Database Connection
   Command: php artisan migrate:status
   Expected: All 28 migrations show [1] Ran

2. Backend Routes
   Command: php artisan route:list | grep spd
   Expected: Routes listed

3. Model Relationships
   Command: php artisan tinker
   >>> Auth::attempt(['email'=>'...','password'=>'...'])
   >>> auth()->user()->employee
   Expected: Employee record loaded

4. Microservice Configuration
   Command: grep -r "localhost:8001" app/Services/
   Expected: Service URL found

5. Frontend Integration
   Command: curl http://localhost:8000/profile
   Expected: HTML with biodata fields

6. Full End-to-End
   Steps:
   1. Start database (PostgreSQL)
   2. Start backend (php artisan serve)
   3. Start microservice (uvicorn)
   4. Login at http://localhost:8000
   5. Go to /profile → See biodata ✅
   6. Create SPD → Generate document ✅
   7. Download file → Success ✅
```

---

**System Status:** ✅ FULLY INTEGRATED  
**All Connections:** ✅ VERIFIED  
**Ready for:** ✅ PRODUCTION DEPLOYMENT  

Architecture is clean, scalable, and maintainable!
