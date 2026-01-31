# 📚 eSPPD Project Complete System Analysis & Architecture

**Date**: February 1, 2026  
**Status**: ✅ COMPREHENSIVE STUDY COMPLETED  
**Project**: e-SPPD (Sistem Elektronik Surat Perjalanan Dinas)  
**Framework**: Laravel 12 + Livewire 3 + Volt + Tailwind CSS

---

## 🎯 Table of Contents

1. [Project Overview](#project-overview)
2. [System Architecture](#system-architecture)
3. [Entry Points & User Flow](#entry-points--user-flow)
4. [Authentication & Authorization](#authentication--authorization)
5. [Database Schema](#database-schema)
6. [Core Business Logic](#core-business-logic)
7. [API Architecture](#api-architecture)
8. [Frontend Structure](#frontend-structure)
9. [Services & Utilities](#services--utilities)
10. [Deployment & Infrastructure](#deployment--infrastructure)

---

## Project Overview

### 📋 What is eSPPD?

e-SPPD is an electronic travel authorization system for a university that manages official business travel (Surat Perjalanan Dinas). The system handles:

- **Document Creation**: Lecturers/staff create official travel requests (SPD)
- **Multi-Level Approval**: Hierarchical approval from direct supervisor to rector
- **Budget Management**: Tracking travel costs and budget allocation
- **Trip Reporting**: Post-travel documentation and expense reconciliation
- **Access Control**: Role-based permissions (7 roles, 17 permissions)

### 🏛️ Organizational Context

**Institution**: University (1 Main Organization)  
**Structure**: Multiple Faculties (Units) → Departments (Sub-units) → Staff

**Typical Workflow**:
```
Dosen/Pegawai → Kaprodi/Kepala Bagian → Wadek → Dekan → Warek/Rektor
   (Level 1)          (Level 2)         (L3)   (L4)   (L5/L6)
```

### 📊 User Base

- **Total Users**: ~474 active staff + lecturers
- **Roles**: Admin, Rektor, Warek, Dekan, Wadek, Kaprodi, Employee (Dosen/Pegawai)
- **Concurrent Usage**: ~50-100 simultaneous users
- **Peak Times**: Semester start/end for travel requests

---

## System Architecture

### 🏗️ High-Level Architecture

```
┌─────────────────────────────────────────────────────────┐
│              FRONTEND LAYER                              │
│  ┌─ Livewire Components (Real-time)                     │
│  ├─ Volt Components (Single-file)                       │
│  ├─ Blade Templates                                     │
│  └─ Tailwind CSS UI                                     │
└──────────────────┬──────────────────────────────────────┘
                   │ HTTP/HTTPS
┌──────────────────▼──────────────────────────────────────┐
│          WEB ROUTES & API ROUTES                         │
│  ┌─ Auth Routes (Login, Register, Password Reset)      │
│  ├─ Web Routes (Dashboard, SPD, Approvals)             │
│  ├─ API Routes (REST endpoints, Mobile API)            │
│  └─ Admin Routes (Employee, User Management)           │
└──────────────────┬──────────────────────────────────────┘
                   │ Laravel
┌──────────────────▼──────────────────────────────────────┐
│        APPLICATION LAYER (Controllers, Actions)          │
│  ┌─ Web Controllers (HTTP requests)                     │
│  ├─ API Controllers (JSON responses)                    │
│  ├─ Livewire Components (Real-time events)             │
│  └─ Jobs (Async tasks, queuing)                        │
└──────────────────┬──────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────┐
│           BUSINESS LOGIC LAYER                           │
│  ┌─ Services (ApprovalService, RbacService)            │
│  ├─ Policies (SpdPolicy for authorization)             │
│  ├─ Events (for notifications/webhooks)                │
│  └─ Middleware (Auth, RBAC enforcement)                │
└──────────────────┬──────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────┐
│            DATA ACCESS LAYER (Models)                    │
│  ┌─ User, Role, Permission (RBAC)                      │
│  ├─ Spd, Approval, ApprovalDelegation                  │
│  ├─ Employee, Organization, Unit                       │
│  ├─ Budget, Cost, TripReport                           │
│  └─ AuditLog (for tracking changes)                    │
└──────────────────┬──────────────────────────────────────┘
                   │ Eloquent ORM
┌──────────────────▼──────────────────────────────────────┐
│              DATABASE LAYER                              │
│  ┌─ PostgreSQL / MySQL primary database                │
│  ├─ Redis (caching, sessions)                          │
│  └─ Cache (DashboardCacheService)                      │
└──────────────────────────────────────────────────────────┘
```

### 🔄 Request Flow Example

**User Creating SPD**:
```
1. GET /spd/create
   └─ SpdCreate Livewire Component
   └─ Middleware: auth, role.level:1
   └─ SpdCreateComponent loads form

2. POST /spd (via Livewire)
   └─ Validation
   └─ SpdPolicy authorization check
   └─ SpdService creates record
   └─ Event triggered for audit log
   └─ Return success response

3. Approval Triggered
   └─ ApprovalService generates approval queue
   └─ Notification sent to approvers
   └─ SPD status = "submitted"
   └─ Approvers see in ApprovalIndex
```

---

## Entry Points & User Flow

### 🚪 Main Entry Points

| URL | Handler | Purpose |
|-----|---------|---------|
| `/` | Route redirect | Auto-redirect to login or dashboard |
| `/login` | LoginComponent (Livewire/Volt) | User authentication |
| `/dashboard` | DashboardEnhanced | Main hub after login |
| `/spd` | SpdIndex | List all SPDs |
| `/approvals` | ApprovalIndex | Approval queue for Kaprodi+ |
| `/reports` | ReportIndex | Trip reports |
| `/admin` | AdminControllers | Admin panel (Admin only) |

### 📍 Complete User Journey

#### **Journey 1: Create SPD (Dosen/Lecturer)**

```
1. LOGIN
   GET /login
   └─ Username (NIP) or Email
   └─ Password
   └─ Remember me checkbox
   └─ POST → LoginForm validation → Auth::attempt()
   └─ Session created → Redirect to /dashboard

2. VIEW DASHBOARD
   GET /dashboard (middleware: auth, verified)
   └─ DashboardEnhanced loads user metrics
   └─ Display recent SPDs, pending approvals
   └─ Show quick action buttons

3. CREATE SPD
   GET /spd/create
   └─ SpdCreate component loads form
   └─ Form fields:
      ├─ Destination (tujuan)
      ├─ Purpose (maksud)
      ├─ Dates (departure/return)
      ├─ Travel type (dalam_kota/luar_kota/luar_negeri)
      ├─ Transport type
      ├─ Budget estimate
      ├─ Accommodation needs
      └─ Invitation file (optional)
   
   POST /spd
   └─ Validation
   └─ Create Spd record (status: draft)
   └─ Initialize empty costs array
   └─ Return success + show SPD details

4. SUBMIT SPD
   POST /spd/{id}/submit
   └─ Change status: draft → submitted
   └─ Record submitted_at timestamp
   └─ Generate approval queue based on travel_type
   └─ ApprovalService creates Approval records
   └─ Notification sent to first approver (Kaprodi)
   └─ Email sent (optional)

5. WAIT FOR APPROVAL
   └─ Monitor /dashboard for status change
   └─ Check /spd/{id} for approval chain
   └─ Can edit if rejected (resubmit flow)

6. DOWNLOAD DOCUMENTS (if approved)
   GET /spd/{id}/pdf/spt
   └─ Generate PDF using SpdPdfController
   └─ Return download response
   
   GET /spd/{id}/pdf/spd
   └─ Generate SPD document PDF

7. SUBMIT TRIP REPORT
   GET /reports/create/{spd_id}
   └─ TripReportCreate component
   └─ Form fields:
      ├─ Actual dates
      ├─ Activities (multi-line)
      ├─ Outputs/Results
      ├─ Expenses
      └─ Attachments (proof)
   
   POST /reports
   └─ Create TripReport record
   └─ Link to SPD
   └─ Send to approver for verification

8. LOGOUT
   POST /logout
   └─ Destroy session
   └─ Revoke tokens
   └─ Redirect to home
```

#### **Journey 2: Approve SPD (Kaprodi/Approver)**

```
1. LOGIN (same as above)

2. DASHBOARD
   GET /dashboard
   └─ Shows pending approvals (role level >= 2)
   └─ Display approval queue count
   └─ Recent requests needing action

3. VIEW APPROVAL QUEUE
   GET /approvals/queue
   └─ ApprovalQueue component
   └─ List pending SPDs assigned to me
   └─ Filter by status, date
   └─ Sort options

4. REVIEW & APPROVE
   GET /approvals/{id}
   └─ Show SPD details
   └─ Show approval chain (previous approvals)
   └─ Show next approver info
   
   POST /approvals/{id}/approve
   └─ Update Approval status → approved
   └─ Check if final approval needed
   └─ If all approved:
      ├─ Update SPD status → approved
      ├─ Update budget tracking
      └─ Notify requester
   └─ Else:
      ├─ Forward to next level approver
      └─ Notify next approver

5. OR REJECT
   POST /approvals/{id}/reject
   └─ Update Approval status → rejected
   └─ Update SPD status → rejected
   └─ Record rejection_reason
   └─ Notify requester with reason

6. DELEGATION (Optional - Wadek+)
   POST /approvals/delegate
   └─ ApprovalDelegation record created
   └─ valid_from → valid_until timeframe
   └─ All approvals assigned to delegate
   └─ Original approver still tracked
```

---

## Authentication & Authorization

### 🔐 Login Flow

**Location**: `resources/views/livewire/pages/auth/login.blade.php`

```php
// Step 1: Render login form
Volt::route('login', 'pages.auth.login')->name('login')

// Step 2: User enters credentials
wire:model="nip"        // NIP or username
wire:model="password"   // Password
wire:model="remember"   // Remember me

// Step 3: Submit
wire:submit="login"
→ LoginForm::authenticate()

// Step 4: Auth attempt
Auth::attempt([
    'nip' => $nip,                    // Can use nip or email
    'password' => $password
], $remember)

// Step 5: Redirect
Redirect to /dashboard (authenticated)
```

**Key Features**:
- ✅ NIP-based authentication (+ email fallback)
- ✅ Password visibility toggle
- ✅ "Remember me" functionality
- ✅ Modern UI with animations
- ✅ Loading state during submission
- ✅ Error messaging
- ✅ Session regeneration for security

### 🔑 RBAC (Role-Based Access Control)

**7 Roles with Hierarchy**:

```
Level 99: Superadmin (System access)
   ↓
Level 98: Admin (Full application access)
   ↓
Level 6:  Rektor (Rector - final approval authority)
   ↓
Level 5:  Warek (Vice Rector - executive)
   ↓
Level 4:  Dekan (Dean - can override, approve up to 50M)
   ↓
Level 3:  Wadek (Vice Dean - can delegate, approve up to 10M)
   ↓
Level 2:  Kaprodi/Kabag (Dept Head - can approve)
   ↓
Level 1:  Dosen/Pegawai (Staff - create SPD only)
```

**Permission System**:

**Categories**:
1. **SPD Permissions** (4):
   - `spd.create` - Create new SPD
   - `spd.edit` - Edit draft SPD
   - `spd.delete` - Delete SPD
   - `spd.view-all` - View all faculty/institution SPDs

2. **Approval Permissions** (4):
   - `approval.approve` - Approve pending SPDs
   - `approval.reject` - Reject SPDs
   - `approval.delegate` - Delegate to another approver
   - `approval.override` - Force actions

3. **Finance Permissions** (3):
   - `finance.view-budget` - View budget
   - `finance.manage-budget` - Manage budget allocation
   - `finance.approve-cost` - Approve expenses

4. **Report Permissions** (3):
   - `report.create` - Create trip report
   - `report.view-all` - View all reports
   - `report.verify` - Verify completion

5. **Admin Permissions** (3):
   - `admin.manage-users` - Create/edit users
   - `admin.manage-roles` - Manage roles
   - `admin.view-logs` - View audit logs

**Access Control Layers**:

```
┌─────────────────────────────────────────┐
│ 1. MIDDLEWARE LAYER                     │
│ ├─ auth (is user logged in?)            │
│ ├─ verified (email verified?)           │
│ ├─ role.level:2 (minimum level check)  │
│ └─ guest (for auth routes)              │
└─────────────────────────────────────────┘
         ↓
┌─────────────────────────────────────────┐
│ 2. GATE LAYER (in AuthServiceProvider)  │
│ ├─ @can('create-spd')                   │
│ ├─ @can('approve-sppd')                 │
│ ├─ @can('view-all-spd')                 │
│ ├─ @can('delegate-approval')            │
│ └─ @can('approve-budget', $amount)      │
└─────────────────────────────────────────┘
         ↓
┌─────────────────────────────────────────┐
│ 3. POLICY LAYER (model-level)           │
│ └─ SpdPolicy::view, update, delete      │
└─────────────────────────────────────────┘
         ↓
┌─────────────────────────────────────────┐
│ 4. RBAC SERVICE LAYER                   │
│ ├─ RbacService::userHasPermission()     │
│ ├─ RbacService::canApproveAmount()      │
│ └─ RbacService::getUserPermissions()    │
└─────────────────────────────────────────┘
```

### 🚪 Logout Flow

**Location**: `app/Livewire/Actions/Logout.php`

```php
public function __invoke()
{
    Auth::guard('web')->logout();      // Clear auth guard
    Session::invalidate();              // Destroy session
    Session::regenerateToken();         // New CSRF token
    return redirect('/');               // Back to home
}
```

---

## Database Schema

### 📊 Core Tables (28 models)

**Authentication & RBAC**:
- `users` - User accounts (PK: id)
- `roles` - Role definitions (1-99 levels)
- `permissions` - Permission definitions
- `role_permissions` - Role↔Permission (BelongsToMany)
- `user_permissions` - User↔Permission (BelongsToMany)
- `approval_delegations` - Temporary approval assignments

**Organization Structure**:
- `organizations` - Main organization (university)
- `units` - Faculties/departments
- `employees` - Staff/lecturers
- `user_employees` - Relationship between users and employees

**Travel & Approval**:
- `spds` - Official travel requests (Surat Perjalanan Dinas)
  - ├─ Primary fields: spt_number, spd_number
  - ├─ Travel details: destination, purpose, dates
  - ├─ Financial: estimated_cost, actual_cost, budget_id
  - ├─ Approval: status, current_approver_nip, rejection_reason
  - └─ Tracking: created_by, submitted_at, approved_at, approved_by

- `approvals` - Approval chain records
  - ├─ spd_id (FK)
  - ├─ level (1-6)
  - ├─ approver_id (FK → employees)
  - ├─ status (pending|approved|rejected|delegated)
  - └─ approved_at, notes

- `spd_followers` - Users following SPD changes

**Financial**:
- `budgets` - Budget allocation per unit/department
- `costs` - Itemized travel costs

**Trip Documentation**:
- `trip_reports` - Post-travel report
  - ├─ actual_dates
  - ├─ activities, outputs
  - ├─ is_verified, verified_by
  - └─ attachments

- `trip_activities` - Individual trip activities
- `trip_outputs` - Outcomes/deliverables
- `trip_report_versions` - Version history

**Configuration**:
- `sbm_settings` - Travel settings
- `approval_rules` - Custom approval routing
- `master_references` - Lookup tables
- `webhooks` - Webhook configurations
- `report_templates` - Document templates
- `scheduled_reports` - Automated reporting

**Audit & Security**:
- `audit_logs` - All changes logged
- `webhook_logs` - Webhook call history
- `password_resets_otp` - OTP for password reset

### 🔗 Key Relationships

```
User
  ├─ HasOne Employee
  ├─ BelongsTo Organization
  ├─ BelongsTo Role (via role_id)
  ├─ BelongsToMany Permission (user_permissions)
  └─ Timestamps

Spd
  ├─ BelongsTo Employee (creator)
  ├─ BelongsTo Organization
  ├─ BelongsTo Unit
  ├─ BelongsTo Budget
  ├─ HasMany Approval
  ├─ HasMany Cost
  ├─ HasOne TripReport
  ├─ HasMany SpdFollower
  └─ Soft Delete (deleted_at)

Approval
  ├─ BelongsTo Spd
  ├─ BelongsTo Employee (approver)
  └─ HasStatus Scope (pending, approved, rejected)

Role
  ├─ HasMany User
  ├─ BelongsToMany Permission (role_permissions)
  └─ Level (1-99)

Employee
  ├─ BelongsTo Organization
  ├─ BelongsTo Unit
  ├─ BelongsTo User
  └─ HasMany Spd
```

---

## Core Business Logic

### ✅ SPD Lifecycle

```
┌──────────┐
│  DRAFT   │  (Created by employee)
│          │  - Can edit all fields
│          │  - Can delete completely
└────┬─────┘
     │ submit()
     ▼
┌──────────────┐
│  SUBMITTED   │  (Sent for approval)
│              │  - Cannot edit fields
│              │  - Approval chain created
│              │  - First approver notified
└────┬─────────┘
     │
     ├─ approve() → ┌──────────┐
     │              │ APPROVED │
     │              └─────┬────┘
     │                    │
     │                    ▼
     │              ┌──────────────┐
     │              │ Budget Spent │  (Update budget tracking)
     │              └──────────────┘
     │
     └─ reject()  → ┌──────────┐
                    │ REJECTED │  (Can resubmit)
                    └──────────┘
```

### 📋 Approval Chain Logic

**Based on Travel Type**:

```
Travel Type: dalam_kota (Within City)
├─ Required Level: 3 (Wadek)
├─ Approval Chain:
│  1. Direct supervisor (Kaprodi) - Level 2
│  2. Wadek - Level 3 ← FINAL APPROVAL
└─ Process Time: 1-2 days

Travel Type: luar_kota (Out of City)
├─ Required Level: 4 (Dekan)
├─ Approval Chain:
│  1. Kaprodi - Level 2
│  2. Wadek - Level 3
│  3. Dekan - Level 4 ← FINAL APPROVAL
└─ Process Time: 2-3 days

Travel Type: luar_negeri (Overseas)
├─ Required Level: 5 (Warek/Rektor)
├─ Approval Chain:
│  1. Kaprodi - Level 2
│  2. Wadek - Level 3
│  3. Dekan - Level 4
│  4. Warek/Rektor - Level 5/6 ← FINAL APPROVAL
└─ Process Time: 5-7 days
```

**Approval Service** (`ApprovalService.php`):

```php
Key Methods:
├─ process() - Main approval logic
├─ approve() - Mark as approved
├─ reject() - Mark as rejected
├─ checkAndProceed() - Check if next level needed
├─ getNextLevel() - Calculate next approver
├─ createApprovals() - Initialize chain
└─ sendNotifications() - Notify approvers
```

### 🎯 Budget Approval Limits

```
Level 3 (Wadek):      10,000,000 (10 juta)
Level 4 (Dekan):      50,000,000 (50 juta)
Level 5 (Warek):     100,000,000 (100 juta)
Level 6 (Rektor):   Unlimited
```

**Usage**:
```blade
@can('approve-budget', 5000000)
    <!-- User can approve up to 5M -->
@endcan
```

### 🔄 Approval Delegation

**Rules**:
- ✅ Only Level 3+ (Wadek+) can delegate
- ✅ Can delegate to same or higher level only
- ✅ Time-bound (valid_from to valid_until)
- ✅ Original approver still visible in audit trail
- ✅ Delegates return for specific approvals only

**Database**:
```sql
approvals_delegations
├─ delegator_id (who delegates)
├─ delegate_id (who takes over)
├─ valid_from (start date)
├─ valid_until (end date)
├─ is_active (boolean flag)
└─ reason (explanation)
```

---

## Core Services

### 🔧 ApprovalService

**Handles**:
- Creating approval chains
- Processing approvals/rejections
- Validating approver authority
- Checking budget limits
- Managing delegation
- Sending notifications

### 🎨 DashboardCacheService

**Caches**:
- User metrics (SPDs this month)
- Pending approvals count
- Approved/rejected counts
- Recent SPDs

**TTL**: Configured per metric

### 📊 SPDQueryOptimizer

**Optimizes**:
- Eager loading relations
- Filtering by organization
- Status filtering
- Date range queries

**Usage**: `getRecentSpds(count)`

### 📄 RbacService

**Methods**:
```php
- userHasPermission($user, $permission)
- userHasAnyPermission($user, [$perms])
- canApproveAmount($user, $amount)
- getUserPermissions($user)
- canDelegate($user, $delegateTo)
- getAllRolesHierarchy()
```

### 🔗 SmartImportService

**Integrates with Python FastAPI** (port 8002):
- Upload file → Python service
- Auto-detect columns
- Apply AI mapping
- Validate data
- Process import
- Rollback on error

---

## API Architecture

### 🌐 REST API Routes

**Base URL**: `/api/`

**Authentication**: `auth:sanctum` middleware

**Key Endpoints**:

```
AUTH
├─ POST /auth/login              (Public)
├─ POST /auth/logout             (Protected)
└─ GET  /auth/user               (Protected)

SPPD MANAGEMENT
├─ GET    /sppd                  (List with pagination)
├─ POST   /sppd                  (Create)
├─ GET    /sppd/{id}             (Show details)
├─ PUT    /sppd/{id}             (Update)
├─ DELETE /sppd/{id}             (Delete)
├─ POST   /sppd/{id}/submit      (Change status)
├─ POST   /sppd/{id}/approve     (Approve)
├─ POST   /sppd/{id}/reject      (Reject)
└─ POST   /sppd/{id}/complete    (Mark complete)

APPROVALS
├─ GET  /sppd/{id}/approvals      (List approval chain)
└─ POST /sppd/{id}/approvals      (Add approval)

EXPORT
├─ POST /sppd/{id}/export-pdf     (PDF download)
└─ GET  /sppd/{id}/export-excel   (Excel download)

MOBILE API (/api/mobile)
├─ GET  /dashboard                (Mobile metrics)
├─ GET  /sppd                      (Mobile list)
├─ GET  /sppd/{id}                 (Mobile details)
├─ POST /sppd/{id}/submit          (Quick submit)
├─ POST /sppd/{id}/approve         (Quick approve)
└─ GET  /notifications             (Notifications)

WEBHOOKS
├─ GET    /webhooks                (List)
├─ POST   /webhooks                (Create)
├─ PUT    /webhooks/{id}           (Update)
├─ DELETE /webhooks/{id}           (Delete)
└─ POST   /webhooks/{id}/test      (Test trigger)

HEALTH
├─ GET /health                      (Server status)
└─ GET /health/metrics              (Performance metrics)
```

### 📱 Response Format

**Success**:
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    "id": "uuid",
    "spt_number": "SPT-2026-001",
    "status": "approved"
  }
}
```

**Error**:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "destination": ["Destination is required"]
  }
}
```

---

## Frontend Structure

### 🎨 Livewire Components

**Located**: `app/Livewire/` (33+ components)

**Main Components**:

1. **Dashboard**:
   - `DashboardEnhanced` - Main dashboard (all roles)
   - `DashboardAdmin` - Admin-specific
   - `DashboardApprover` - Approval queue view
   - `DashboardStaff` - Staff view

2. **SPD Management**:
   - `Spd/SpdIndex` - List all SPDs
   - `Spd/SpdCreate` - Create new SPD
   - `Spd/SpdShow` - View details
   - `Spd/SpdEdit` - Edit form

3. **Approvals**:
   - `Approvals/ApprovalIndex` - Queue list
   - `Approvals/ApprovalQueue` - Approval workflow
   - `Approvals/ApprovalAction` - Approve/Reject dialog

4. **Reports**:
   - `Reports/ReportIndex` - List reports
   - `Reports/ReportBuilder` - Custom reports
   - `Reports/TripReportCreate` - Trip report form

5. **Admin**:
   - `Employees/EmployeeIndex` - Employee CRUD
   - `Settings/SettingsIndex` - Configuration
   - `Excel/ExcelManager` - Import/Export

6. **Charts**:
   - `Charts/SPDTrendChart` - Monthly trends
   - `Charts/SPDStatusChart` - Status breakdown

### 📄 Blade Templates

**Located**: `resources/views/` (50+ templates)

**Structure**:
```
views/
├─ livewire/
│  ├─ dashboard/          (Dashboard views)
│  ├─ spd/                (SPD form & list)
│  ├─ approvals/          (Approval UI)
│  ├─ reports/            (Report views)
│  ├─ pages/
│  │  └─ auth/            (Login, register, password reset)
│  └─ layout/             (Navigation, sidebar)
├─ components/
│  ├─ sidebar.blade.php   (Main navigation)
│  ├─ dropdown.blade.php  (Menu dropdowns)
│  └─ profile/            (Profile components)
├─ pdf/                   (PDF templates for SPD/reports)
└─ admin/                 (Admin panel views)
```

### 🎯 Volt Components

**Single-file Livewire components** (more modern):

```
resources/views/livewire/pages/auth/
├─ login.blade.php          (Login form)
├─ register.blade.php       (User registration)
├─ forgot-password.blade.php (Password reset request)
├─ reset-password.blade.php  (Password reset form)
└─ verify-email.blade.php    (Email verification)
```

### 🎨 UI Framework

**Tailwind CSS**:
- Custom colors (brand-teal, brand-lime)
- Responsive breakpoints (mobile-first)
- Dark mode support
- Custom animations

**Custom CSS**:
- Login page animations (floating particles, fade-in)
- Form transitions
- Loading spinners
- Status badge colors

### 📱 Responsive Design

**Breakpoints**:
- Mobile: < 640px
- Tablet: 640px - 1024px
- Desktop: 1024px+

**Mobile Features**:
- Touch-friendly buttons (48px minimum)
- Responsive forms
- Mobile API endpoints
- Simplified navigation

---

## Deployment & Infrastructure

### 🚀 Current Deployment

**Server**: 
- IP: `192.168.1.27:8083` (HTTPS)
- Type: Local development/staging

**Technology Stack**:
- **Web Server**: Nginx
- **Application**: Laravel 12 (PHP 8.4)
- **Database**: PostgreSQL / MySQL
- **Cache**: Redis
- **Queue**: Laravel Queue with Redis driver
- **Frontend Build**: Vite + npm

### 📦 Dependencies

**Key Packages**:
```
laravel/framework     ^12.0      (Core)
livewire/livewire    ^3.6.4     (Real-time UI)
livewire/volt        ^1.7.0     (Single-file components)
laravel/sanctum      ^4.3       (API authentication)
maatwebsite/excel    ^3.1       (Excel import/export)
barryvdh/laravel-dompdf ^3.1    (PDF generation)
phpoffice/phpword    ^1.4       (Word documents)
predis/predis        ^3.3       (Redis client)
```

### 🔐 Security Features

**Implemented**:
- ✅ HTTPS/SSL enforcement
- ✅ CSRF token protection
- ✅ Password hashing (bcrypt)
- ✅ Rate limiting on auth routes
- ✅ Session encryption
- ✅ SQL injection prevention (Eloquent)
- ✅ XSS protection (Blade escaping)
- ✅ RBAC enforcement at all layers
- ✅ Audit logging
- ✅ Webhook signature verification

### 📊 Performance Optimization

**Implemented**:
- ✅ Query optimization with eager loading
- ✅ Redis caching (sessions, cache layer)
- ✅ Database indexing
- ✅ Livewire lazy loading
- ✅ CSS/JS minification
- ✅ Asset versioning
- ✅ Vite hot module replacement (dev)

### 🔄 CI/CD

**GitHub Actions** configured for:
- ✅ Automated testing (PHPUnit)
- ✅ Code quality scanning (CodeQL)
- ✅ Pre-commit hooks
- ✅ Security scanning (gitleaks)
- ✅ Auto-merge dependabot PRs

---

## Key Insights & Recommendations

### ✅ Strengths

1. **Clean Architecture**: Well-separated concerns (Models, Services, Controllers)
2. **RBAC Implementation**: Sophisticated multi-level approval system
3. **Real-time UI**: Livewire provides responsive user experience
4. **API-First**: REST API for mobile and third-party integration
5. **Test Coverage**: Comprehensive test suite (17 RBAC tests alone)
6. **Documentation**: Extensive inline documentation
7. **Security**: Multiple layers of authorization
8. **Scalability**: Redis caching, queue jobs, database optimization

### ⚠️ Considerations

1. **Complexity**: Multi-level approval can be confusing without documentation
2. **Data Volume**: Large travel records could impact query performance
3. **Webhook System**: Not fully documented for third-party integrations
4. **Mobile App**: Currently no native mobile app (API-ready though)
5. **Localization**: Currently Indonesian-only (hardcoded strings)

### 🎯 Recommendations for Next Phase

1. **Add Advanced Features**:
   - [ ] Travel budget analytics dashboard
   - [ ] Recurring trip templates
   - [ ] Expense claim integration
   - [ ] Document e-signature capability

2. **Performance**:
   - [ ] Implement caching for approval rules
   - [ ] Optimize SPD list queries (pagination already done)
   - [ ] Add database query logging in production

3. **Integration**:
   - [ ] Bank integration for reimbursement
   - [ ] Email/SMS notifications
   - [ ] Calendar integration (Google/Outlook)
   - [ ] Third-party webhook consumers

4. **User Experience**:
   - [ ] Mobile app (React Native or Flutter)
   - [ ] Batch SPD creation
   - [ ] Template-based forms
   - [ ] Dark mode UI theme

5. **Admin**:
   - [ ] User activity dashboard
   - [ ] Budget utilization reports
   - [ ] Travel statistics analytics
   - [ ] Custom approval rule builder

---

## Quick Reference

### 🔑 Important Files

| Purpose | File Path |
|---------|-----------|
| **Main Routes** | `routes/web.php`, `routes/api.php` |
| **RBAC Setup** | `app/Providers/AuthServiceProvider.php` |
| **RBAC Service** | `app/Services/RbacService.php` |
| **Approval Logic** | `app/Services/ApprovalService.php` |
| **Dashboard** | `app/Livewire/DashboardEnhanced.php` |
| **Login** | `resources/views/livewire/pages/auth/login.blade.php` |
| **Models** | `app/Models/` (28 models) |
| **Tests** | `tests/Feature/RbacTest.php`, etc |
| **Config** | `config/esppd.php`, `config/auth.php` |

### 👥 Test Accounts

All use password: `password123`

| Role | Email | Level | Use Case |
|------|-------|-------|----------|
| Admin | admin@esppd.test | 98 | System admin |
| Rektor | rektor@esppd.test | 6 | Overseas travel approval |
| Warek | warek@esppd.test | 5 | Executive approval |
| Dekan | dekan@esppd.test | 4 | Faculty approval |
| Wadek | wadek@esppd.test | 3 | Within-city approval |
| Kaprodi | kaprodi@esppd.test | 2 | Department approval |
| Dosen | dosen@esppd.test | 1 | Create SPD |

---

## Next Phase Ownership

**Current State**: ✅ Foundation Complete (8.7/10 ready)

**To Improve Performance**:
1. Profile database queries in production
2. Implement caching for approval rules
3. Add background jobs for document generation
4. Monitor Redis usage
5. Setup performance monitoring

**To Scale**:
1. Database: PostgreSQL optimization
2. Cache: Redis cluster
3. Queue: Laravel Horizon
4. Monitoring: APM tool
5. Load: Nginx load balancing

---

**Document**: Complete System Analysis  
**Last Updated**: February 1, 2026  
**Status**: ✅ READY FOR NEXT PHASE  
**Maintainer**: Claude.ai (Coding Agent)
