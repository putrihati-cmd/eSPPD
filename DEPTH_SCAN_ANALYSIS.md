# 📊 DEPTH SCAN PROJECT e-SPPD - Analisis Mendalam

**Tanggal Scan:** 29 Januari 2026  
**Status:** Production-Ready (Go-Live)  
**Framework:** Laravel 12.49.0 + Livewire 3.6.4 + Vite 7  
**Database:** PostgreSQL (Primary) / MySQL (Flexible)

---

## 🎯 Gambaran Umum Proyek

**e-SPPD** (Elektronik Surat Perintah Perjalanan Dinas) adalah sistem digital terintegrasi untuk **UIN Saizu Purwokerto** yang mendigitalkan proses perjalanan dinas dari pengajuan hingga pembayaran.

### Tujuan Utama

- Digitalisasi proses birokrasi perjalanan dinas (paperless)
- Monitoring anggaran real-time per unit/fakultas
- Approval otomatis berjenjang dengan transparansi penuh
- Pelaporan terstandarisasi dan teraudit

### Identitas Visual

- **Brand Colors:** Teal (#009CA6) + Lime (#D4E157)
- **Font:** Inter
- **Design Philosophy:** Clean & Functional (Atoms World Aesthetic)

---

## 🛠️ Tech Stack Lengkap

### Backend

| Komponen | Versi | Fungsi |
| --- | --- | --- |
| **Laravel** | 12.49.0 | Framework utama |
| **Livewire** | 3.6.4 | Reactive UI components |
| **Livewire Volt** | ^1.7.0 | Component-based view layer |
| **PostgreSQL** | - | Database produksi |
| **Redis** | - | Cache, Queue, Session |
| **Laravel Sanctum** | ^4.3 | API authentication |

### Frontend

| Komponen | Versi | Fungsi |
| --- | --- | --- |
| **Vite** | ^7.0.7 | Asset bundler |
| **Tailwind CSS** | ^3.1.0 | Styling framework |
| **Alpine.js** | - | Lightweight interactivity |
| **Axios** | ^1.11.0 | HTTP client |

### DevTools & Utilities

| Paket | Versi | Fungsi |
| --- | --- | --- |
| **DomPDF** | ^3.1 | PDF generation |
| **Maatwebsite Excel** | ^3.1 | Excel import/export |
| **PHPOffice Word** | ^1.4 | Document generation |
| **Predis** | ^3.3 | Redis client |
| **Laravel Pail** | ^1.2.2 | Log viewer |
| **Laravel Pint** | ^1.24 | Code formatting |

### Services Eksternal

- **Python FastAPI** (Microservice untuk dokumen DOCX/PDF kompleks)
- **Firebase** (Push notifications)
- **LDAP** (Authentication terintegrasi)
- **SMS Gateway** (Notifikasi SMS)

---

## 📁 Struktur Direktori & Komponen

### Core Directories

```bash
├── app/
│   ├── Http/
│   │   ├── Controllers/       # HTTP Request Handlers
│   │   │   ├── Admin/         # User management
│   │   │   ├── Api/           # REST API endpoints
│   │   │   ├── Auth/          # Authentication
│   │   │   ├── Finance/       # Bendahara operations
│   │   │   ├── SpdPdfController.php        # PDF generation
│   │   │   ├── TripReportPdfController.php # LPD PDF
│   │   │   ├── ExcelController.php         # Import/Export
│   │   │   └── SmartImportController.php   # AI-based import
│   │   ├── Middleware/        # Custom middleware
│   │   │   ├── CheckRole.php
│   │   │   ├── CheckRoleLevel.php  # Role hierarchy check
│   │   │   ├── CheckPasswordReset.php
│   │   │   ├── CacheResponse.php
│   │   │   └── SecurityHeaders.php
│   │   └── Resources/          # API resources (if applicable)
│   │
│   ├── Models/                # Eloquent Models (28 models)
│   │   ├── User.php              # Auth user with roles
│   │   ├── Spd.php               # Main SPPD document
│   │   ├── TripReport.php        # Laporan Perjalanan Dinas
│   │   ├── Approval.php          # Approval workflow
│   │   ├── ApprovalRule.php      # Business rules untuk approval
│   │   ├── Budget.php            # Unit budget tracking
│   │   ├── Employee.php          # Pegawai data
│   │   ├── Organization.php      # Org structure
│   │   ├── Role.php              # RBAC roles
│   │   ├── AuditLog.php          # Compliance audit trail
│   │   └── [23 other models]
│   │
│   ├── Services/              # Business Logic (11 services)
│   │   ├── ApprovalService.php              # Approval workflow logic
│   │   ├── NomorSuratService.php            # Auto letter numbering
│   │   ├── PythonDocumentService.php        # Python FastAPI client
│   │   ├── DocxGeneratorService.php         # DOCX template generation
│   │   ├── DocumentService.php              # Document handling
│   │   ├── SmartImportService.php           # AI-based data matching
│   │   ├── CacheService.php                 # Caching strategies
│   │   ├── LdapAuthService.php              # LDAP integration
│   │   ├── FirebasePushService.php          # Push notifications
│   │   ├── SmsGatewayService.php            # SMS alerts
│   │   └── CalendarIntegrationService.php   # Calendar sync
│   │
│   ├── Livewire/              # Interactive components
│   │   ├── Dashboard.php
│   │   ├── Approvals/         # ApprovalIndex, ApprovalQueue
│   │   ├── Spd/               # SpdIndex, SpdCreate, SpdShow
│   │   ├── Reports/           # ReportIndex, TripReportCreate
│   │   ├── Budgets/
│   │   ├── Employees/
│   │   ├── Excel/             # ExcelManager
│   │   ├── Settings/
│   │   ├── Forms/             # Reusable form components
│   │   └── Actions/           # Action handlers
│   │
│   ├── Jobs/                  # Queue jobs (background processing)
│   ├── Notifications/         # Email/SMS notifications
│   ├── Exports/               # Excel export classes
│   ├── Imports/               # Excel import handlers
│   └── Providers/             # Service providers
│
├── routes/
│   ├── web.php                # Web routes (Livewire pages)
│   ├── api.php                # REST API routes
│   ├── auth.php               # Authentication routes (Breeze)
│   └── console.php            # CLI commands
│
├── database/
│   ├── migrations/            # 31 migration files
│   │   ├── Core tables (users, organizations, employees, units)
│   │   ├── Domain tables (spds, costs, approvals, trip_reports)
│   │   ├── Reference tables (budget, sbm settings, webhooks)
│   │   ├── Optimization (indexes, soft deletes, constraints)
│   │   └── Feature migrations (revision fields, OTP, role management)
│   ├── factories/
│   └── seeders/
│
├── resources/
│   ├── views/                 # Blade templates
│   ├── css/                   # Tailwind customizations
│   └── js/                    # Frontend JavaScript
│
├── config/
│   ├── app.php                # Application config
│   ├── database.php           # DB connections
│   ├── esppd.php              # Custom e-SPPD config
│   ├── mail.php               # Email settings
│   ├── queue.php              # Queue driver config
│   └── [11 other configs]
│
├── docker/
│   └── nginx/                 # Nginx configuration
│
├── deployment/
│   ├── deploy_app.sh          # Production deploy script
│   ├── setup_server.sh        # Server provisioning
│   ├── setup_db*.sql          # Database setup scripts
│   └── supervisord.conf       # Process management
│
├── document-service/          # Python FastAPI Microservice
│   ├── main.py
│   ├── requirements.txt
│   ├── services/              # Document processing logic
│   └── templates/             # DOCX templates
│
└── tests/
    ├── Feature/               # Feature tests
    ├── Unit/                  # Unit tests
    ├── Browser/               # Dusk browser tests
    └── Performance/           # Performance benchmarks
```

---

## 👥 Sistem Role & Hierarki Access

### Level-Based Role System

| Level | Role | Deskripsi | Budget Limit |
| --- | --- | --- | --- |
| **99** | Superadmin | Kontrol penuh sistem | Unlimited |
| **98** | Admin | Manajemen user, employee | No limit |
| **6** | Rektor | Pimpinan universitas | Unlimited |
| **5** | Wakil Rektor | Deputy leadership | 100 Juta |
| **4** | Dekan | Pimpinan fakultas | 50 Juta |
| **3** | Wakil Dekan | Deputy dekan | 20 Juta |
| **2** | Kaprodi/Kabag | Pimpinan program/bagian | 5 Juta |
| **1** | Dosen/Staff | Pemohon (employee) | 0 (pemohon) |

### Authentication Flow

```bash
NIP Input (18 digit)
    ↓
Convert ke email internal (nip@domain)
    ↓
Validate password (default: DDMMYYYY)
    ↓
Rate limiting check (3 attempt max, auto-lockout)
    ↓
Force password change (first login)
    ↓
Dashboard redirect based on role level
```

### Middleware Stack

- `auth` - Authenticated user check
- `role.level:N` - Role level gating (e.g., `:2` untuk Kaprodi+)
- `role:name1,name2` - Specific role check
- `CheckPasswordReset` - Force password change
- `CacheResponse` - Response caching
- `SecurityHeaders` - Security headers

---

## 🔄 Workflow & Business Logic

### SPPD Lifecycle

```text
1. DRAFT
   └─ Employee creates new SPPD
   └─ Can edit/delete own draft
   └─ Auto-save to database

2. SUBMITTED
   └─ Employee submits to manager
   └─ Status locked for editing
   └─ Audit log created

3. APPROVAL FLOW (Multi-level)
   ├─ Check budget availability
   ├─ Route to appropriate approver based on org hierarchy
   ├─ Approver can:
   │  ├─ APPROVE → moves to next level
   │  └─ REJECT → returns to draft with reason
   └─ Final approval generates letter number

4. APPROVED
   └─ System generates SPT (Surat Perintah Tugas)
   └─ System generates SPPD (Surat Perjalanan Dinas)
   └─ Auto letter numbering: 0001/Un.19/K.AUPK/FP.01/2025

5. COMPLETED
   └─ Trip report submitted
   └─ Financial settlement recorded
   └─ Bendahara verifies
   └─ Payment processed

6. REJECTED (Alternative path)
   └─ User can revise and resubmit
   └─ Revision history tracked (JSON)
   └─ revision_count incremented
```

### Revision Feature

```json
REJECTED → Can edit specific fields
   ↓
RESUBMITTED (revision_count++)
   ↓
Back to approval queue
   ↓
History JSON stores:
{
  "revision_1": {
    "date": "2025-01-29",
    "changed_fields": ["destination", "duration"],
    "reason": "Budget melebihi limit"
  }
}
```

### Approval Delegation

- Approver can delegate to colleague
- Created via `ApprovalDelegate` model
- Delegation can be time-limited
- Audit trail maintained

---

## 💾 Database Schema Overview

### Core Tables (31 migrations)

#### Users & Access

- `users` - Authentication + role assignment
- `roles` - Role definitions with hierarchy
- `password_resets_otp` - OTP-based password recovery
- `approval_delegates` - Delegation management

#### Organization Structure

- `organizations` - Institution (UIN Saizu)
- `units` - Faculty/Department
- `employees` - Staff data
- `grade_references` - Employee grades

#### SPPD Management

- `spds` - Main travel request document
- `costs` - Cost breakdown per SPPD
- `budgets` - Unit budget allocation
- `sbm_settings` - Budget settings per unit

#### Approval & Workflow

- `approvals` - Approval history & status
- `approval_rules` - Business rules engine
- `trip_reports` - Post-trip reporting (LPD)
- `trip_report_versions` - Version control

#### References & Config

- `destinations_reference` - Travel destinations
- `transportation_reference` - Transport types
- `daily_allowance` - Allowance rates
- `accommodation` - Lodging options
- `report_templates` - Template configurations

#### Compliance & Audit

- `audit_logs` - All action tracking
- `webhooks` - Event hooks
- `webhook_logs` - Hook execution logs
- `scheduled_reports` - Automated reports
- `spd_followers` - Interest tracking

### Key Indexes

- `spds(employee_id, status, created_at)` - Query optimization
- `approvals(spd_id, approver_id, status)`
- `budgets(unit_id, fiscal_year)`
- Soft delete index on `deleted_at`

---

## 🔗 API Endpoints

### Authentication

```bash
POST /api/auth/login              - Login dengan token Sanctum
POST /api/auth/logout             - Logout
GET  /api/auth/user               - Current user info
```

### SPPD CRUD

```bash
GET    /api/sppd                  - List all SPPD
POST   /api/sppd                  - Create SPPD
GET    /api/sppd/{id}             - Show detail
PUT    /api/sppd/{id}             - Update SPPD
DELETE /api/sppd/{id}             - Delete SPPD
```

### SPPD Actions

```bash
POST /api/sppd/{id}/submit        - Submit to approval
POST /api/sppd/{id}/approve       - Approve SPPD
POST /api/sppd/{id}/reject        - Reject SPPD
POST /api/sppd/{id}/complete      - Mark as completed
```

### Mobile API

```bash
GET  /api/mobile/dashboard        - Mobile dashboard
GET  /api/mobile/sppd             - List SPPD
GET  /api/mobile/sppd/{id}        - SPPD detail
POST /api/mobile/sppd/{id}/submit - Quick submit
POST /api/mobile/sppd/{id}/approve - Quick approve
GET  /api/mobile/notifications    - Notifications
```

### Webhooks

```bash
GET    /api/webhooks              - List webhooks
POST   /api/webhooks              - Create webhook
PUT    /api/webhooks/{id}         - Update webhook
DELETE /api/webhooks/{id}         - Delete webhook
POST   /api/webhooks/{id}/test    - Test webhook
```

---

## 📄 Document Generation

### PDF Documents (via DomPDF)

1. **SPT (Surat Perintah Tugas)** - Task order
2. **SPPD (Surat Perjalanan Dinas)** - Travel authorization
3. **Trip Report (LPD)** - Post-trip report

### DOCX Documents (via Python FastAPI)

1. **Detailed trip itinerary** - Complex formatting
2. **Financial summary** - Embedded tables
3. **Approval record** - Multi-page with signatures

### Python Microservice (Port 8001)

```yaml
Endpoint: http://localhost:8001/docs (Swagger UI)

Fungsi:
- Template rendering (DOCX)
- Batch PDF generation
- Document OCR (future)
- Signature embedding
```

---

## 🔒 Security Features

### Auth & Login

- ✅ NIP-based login (18 digit format)
- ✅ Password hashing (bcrypt 12-round cost: BCRYPT_ROUNDS=12)
- ✅ Rate limiting (3 attempts / 15 minutes per IP, auto-lockout)
- ✅ Force password change on first login
- ✅ LDAP integration support
- ✅ OTP-based password recovery (alternative)
- ✅ Session timeout: 120 minutes (SESSION_LIFETIME=120)
- ✅ Session driver: Redis (encrypted: SESSION_ENCRYPT=true)
- ✅ Remember token: 14 days (336 hours)
- ✅ Session cookies: Secure, HttpOnly, SameSite=lax

### Authorization

- ✅ Role-based access control (RBAC)
- ✅ Level-based hierarchical gating
- ✅ Gate-based permission system
- ✅ Budget enforcement per role level
- ✅ Delegation with audit trail

### Data Protection

- ✅ Soft delete (audit compliance)
- ✅ Audit logging (all CRUD operations)
- ✅ Encryption for sensitive fields (if needed)
- ✅ CORS restrictions
- ✅ CSRF token validation (Livewire built-in)
- ✅ Security headers (Content-Security-Policy, etc.)

### Compliance

- ✅ BPK audit trail (soft delete preserves data)
- ✅ Approval history (immutable records)
- ✅ User action tracking (AuditLog model)
- ✅ Document version control (TripReportVersion)

---

## 🚀 Performance Optimizations

### Caching Strategy

```text
- Dashboard statistics: 15 minutes (Redis)
- User profile: 30 minutes
- Reference data: 1 hour
- Query results: On-demand invalidation
```

### Database Optimization

- Composite indexes on frequently-filtered columns
- Soft delete indexed
- Eager loading (Eloquent relationships)
- Query optimization via Service layer

### Frontend Performance

- Lazy loading components (Livewire)
- CSS/JS bundling via Vite
- Asset versioning for cache-busting
- Minimal JavaScript (Alpine.js only)

### Queue Processing

- Background jobs for:
  - PDF/DOCX generation
  - Email notifications
  - Report scheduling
  - Bulk operations

---

## 📦 External Integrations

### Python Document Service

```yaml
Status: Microservice (FastAPI)
Port: 8001
Capabilities:
  - DOCX template rendering with data binding
  - PDF batch generation
  - OCR & document parsing (future)
  - Signature embedding
```

### LDAP Authentication

```text
Service: LdapAuthService
Usage: Optional integration with institutional directory
Fallback: Local NIP/Password authentication
```

### Firebase Push Notifications

```text
Service: FirebasePushService
Use case: Real-time approval notifications
Deployment: Cloud Messaging (FCM)
```

### SMS Gateway

```text
Service: SmsGatewayService
Use case: Critical approval alerts via SMS
Provider: Configurable (Twilio/AWS SNS/Local)
```

---

## 🚢 Deployment Setup

### Docker Architecture

```text
Internet
   ↓
[Nginx Container] ← Port 8000 & 8001
   ↓
[Laravel App] ← PHP-FPM
   ↓
[PostgreSQL] ← Database
   ↓
[Redis] ← Cache & Queue
   ↓
[Python Service] ← FastAPI (Port 8001)
```

### Compose Services

1. **app** - Laravel PHP-FPM container
2. **nginx** - Web server with SSL support
3. **postgres** - Database
4. **redis** - Cache & queue broker
5. **document-service** - Python FastAPI

### Quick Start Commands

#### Local Development

```bash
# Using Laragon (Windows)
php artisan serve
npm run dev
php artisan queue:work
# Python service: cd document-service && uvicorn main:app --reload

# Or use start_dev.bat (one-click start)
```

#### Docker Production

```bash
docker-compose up -d              # Start all services
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

---

## 📊 Fitur Unggulan

### 1. Smart Approval Workflow

- Auto-route based on org structure
- Budget enforcement
- Delegation support
- Multi-level signing

### 2. Real-time Dashboard

- 6-month trend statistics
- Status distribution charts
- Budget health indicators
- Notifications widget

### 3. Excel Import/Export

- Template-based import
- Validation rules
- Bulk operations
- Error reporting

### 4. Trip Report Management

- Post-travel documentation
- Activity tracking
- Financial reconciliation
- PDF generation

### 5. Audit & Compliance

- Immutable audit logs
- Soft delete with reasons
- Approval history
- Document versioning

### 6. Mobile API

- REST endpoints for mobile app
- Quick approval actions
- Push notifications
- Offline sync support

---

## 🔧 Development Commands

### Artisan Commands

```bash
# Database
php artisan migrate                       # Run migrations
php artisan seed                          # Seed database
php artisan db:fresh                      # Fresh database

# Caching & Optimization
php artisan cache:clear
php artisan config:cache
php artisan view:cache
php artisan optimize

# Queue & Jobs
php artisan queue:work                    # Start queue worker
php artisan queue:failed                  # View failed jobs
php artisan schedule:run                  # Run scheduler

# Authentication
php artisan tinker                        # Interactive shell

# Testing
composer run test                         # Run all tests
composer run test -- --filter=TestName   # Specific test

# Code Quality
./vendor/bin/pint                         # Format code
```

### NPM Commands

```bash
npm run dev                               # Development watch
npm run build                             # Production build
npm run format                            # Format assets
```

---

## 📈 Project Status & Metrics

| Aspek | Status |
| --- | --- |
| **Code Quality** | ✅ Green (Pint formatted) |
| **Testing** | ✅ Green (PHPUnit ready) |
| **Database** | ✅ Stable (31 migrations, optimized) |
| **Security** | ✅ Implemented (RBAC, encryption) |
| **Performance** | ✅ Optimized (caching, indexing) |
| **Documentation** | ✅ Complete (MASTER_DOC.md) |
| **Production Ready** | ✅ Yes (Docker setup confirmed) |

---

## 🎓 Key Learning Points

1. **Livewire Volt** - Modern component-based approach for reactive UI
2. **Role Hierarchy** - Elegant level-based RBAC implementation
3. **Approval Workflow** - Complex business logic with delegation
4. **Microservices** - Python FastAPI integration pattern
5. **Queue Processing** - Background jobs for long-running tasks
6. **Database Design** - Soft deletes for compliance
7. **Docker Deployment** - Multi-service orchestration
8. **API Design** - RESTful endpoints with Sanctum auth

---

## 📝 File Documentation

### Key Documentation Files

- [RUNNING_GUIDE.md](../RUNNING_GUIDE.md) - How to run locally
- [MASTER_DOC.md](../md/MASTER_DOC.md) - Complete feature docs
- [PROJECT_CLOSURE.md](../PROJECT_CLOSURE.md) - Status report
- [RANGKUMAN_PROYEK.md](../RANGKUMAN_PROYEK.md) - Project overview

### Configuration Files

- [config/esppd.php](../config/esppd.php) - e-SPPD specific config
- [docker-compose.yml](../docker-compose.yml) - Container orchestration
- [Dockerfile](../Dockerfile) - App container definition
- [vite.config.js](../vite.config.js) - Asset build config

---

## 🔮 Potential Enhancements

1. **AI-Powered Approval** - ML model untuk approve/reject prediction
2. **Calendar Integration** - Google Calendar sync
3. **Mobile App** - React Native client
4. **Analytics Dashboard** - Advanced reporting
5. **Workflow Automation** - IFTTT-style rules
6. **Document Signing** - Digital signatures (PKI)
7. **Budget Forecasting** - ML-based budget prediction

---

**Generated by:** Depth Scan Analysis  
**Last Updated:** 29 January 2026  
**Status:** Production-Ready for UAT / Go-Live
