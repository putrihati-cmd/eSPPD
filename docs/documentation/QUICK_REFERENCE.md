# 🚀 QUICK REFERENCE & DEVELOPER GUIDE

## 📋 Quick Start Checklist

### Prerequisites
- [ ] Laragon installed (PostgreSQL, Redis running)
- [ ] PHP 8.2+ with composer
- [ ] Node.js 20+
- [ ] Python 3.10+

### Local Setup (5 minutes)
```bash
# 1. Clone/Download project
cd c:\laragon\www\eSPPD

# 2. Install dependencies
composer install
npm install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Setup database
php artisan migrate
php artisan db:seed

# 5. Start all services
# Option A: One-click (Windows)
start_dev.bat

# Option B: Manual (Terminal)
# Terminal 1:
php artisan serve

# Terminal 2:
npm run dev

# Terminal 3:
php artisan queue:work

# Terminal 4 (Python):
cd document-service
python -m venv venv
.\venv\Scripts\activate
pip install -r requirements.txt
uvicorn main:app --reload --port 8001
```

### Access Points
| Service | URL | Purpose |
|---------|-----|---------|
| Web App | http://127.0.0.1:8000 | Main interface |
| Python Service | http://127.0.0.1:8001/docs | Document API |
| Database | localhost:5432 | PostgreSQL |
| Redis | localhost:6379 | Cache/Queue |

### Default Login
- **NIP:** Any NIP in database (e.g., `123456789012345678`)
- **Password:** DDMMYYYY (birth date, default)
- **Password Reset:** Flag `is_password_reset` in users table

---

## 🏗️ Project Structure Cheat Sheet

```
eSPPD/
├── app/
│   ├── Models/              ← Eloquent models (28)
│   ├── Http/Controllers/    ← Request handlers
│   ├── Livewire/            ← Reactive UI components
│   ├── Services/            ← Business logic (11 services)
│   ├── Jobs/                ← Background jobs
│   └── Notifications/       ← Email/SMS
├── routes/
│   ├── web.php              ← Web routes (Livewire)
│   └── api.php              ← API routes (Sanctum)
├── database/
│   ├── migrations/          ← 31 schema migrations
│   ├── factories/           ← Model factories
│   └── seeders/             ← Database seeders
├── resources/
│   ├── views/               ← Blade templates
│   ├── css/                 ← Tailwind
│   └── js/                  ← Frontend JS
├── config/
│   ├── app.php              ← App config
│   ├── database.php         ← DB config
│   └── esppd.php            ← Custom config
├── document-service/        ← Python FastAPI
│   ├── main.py              ← FastAPI app
│   ├── services/            ← Document logic
│   └── templates/           ← DOCX templates
├── storage/
│   ├── app/                 ← File storage
│   └── logs/                ← Application logs
└── tests/                   ← PHPUnit tests
```

---

## 🎯 Key Models & Relationships

### User & Authorization
```php
User
├─ role()        → Role (RBAC)
├─ employee()    → Employee
├─ organization()→ Organization
└─ approvals()   → Approval[] (as approver)

Role
├─ level (1-99)
├─ permissions (JSON)
└─ users()      → User[]
```

### SPPD Workflow
```php
Spd (Main document)
├─ employee()        → Employee (who requested)
├─ unit()            → Unit (from org structure)
├─ budget()          → Budget (allocated funds)
├─ costs()           → Cost[] (breakdown)
├─ approvals()       → Approval[] (workflow history)
├─ tripReport()      → TripReport (post-travel)
├─ followers()       → SpdFollower[] (observers)
└─ auditLogs()       → AuditLog[] (changes)

Approval (Workflow tracking)
├─ spd()             → Spd
├─ approver()        → User (who approved)
├─ approvalRule()    → ApprovalRule (business rule)
└─ delegate()        → ApprovalDelegate (if delegated)
```

### Trip Report
```php
TripReport
├─ spd()             → Spd
├─ tripActivities()  → TripActivity[]
├─ tripOutputs()     → TripOutput[]
├─ versions()        → TripReportVersion[]
└─ auditLogs()       → AuditLog[]

TripReportVersion (Version control)
├─ tripReport()      → TripReport
├─ changes (JSON)    ← What changed
└─ changedBy()       → User
```

---

## 🔐 Authentication & Authorization Quick Guide

### Check User Role in Code

```php
// Check role level
if (auth()->user()->role->level >= 2) {
    // Can approve (Kaprodi+)
}

// Check specific role
if (auth()->user()->role->name === 'admin') {
    // Admin-only action
}

// Using Gates (AppServiceProvider)
Gate::define('approve-sppd', fn($user) => $user->role->level >= 2);

// In Blade template
@can('approve-sppd')
    <button>Approve</button>
@endcan

// In Controller
Gate::authorize('approve-sppd');

// Check permission
if (auth()->user()->can('view-all-sppd')) {
    // Can view all SPPDs in system
}
```

### Middleware

```php
// In routes/web.php

// Role level check
Route::middleware(['auth', 'role.level:2'])->group(function () {
    // Only users with level >= 2
});

// Specific role check
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Only admin role
});

// Password reset check
Route::middleware(['auth', 'password.reset'])->group(function () {
    // User must have changed password
});
```

---

## 💾 Database Quick Commands

### Migrations & Seeding
```bash
# Run all pending migrations
php artisan migrate

# Rollback last batch
php artisan migrate:rollback

# Rollback & re-run
php artisan migrate:refresh

# Fresh database (destructive!)
php artisan migrate:fresh

# Fresh + seed
php artisan migrate:fresh --seed

# Seed only
php artisan db:seed

# Create new migration
php artisan make:migration create_table_name
```

### Database Status
```bash
# Check migration status
php artisan migrate:status

# Show pending migrations
php artisan migrate:status --pending
```

### Tinker (Interactive Shell)
```bash
php artisan tinker

# Create test user
$user = User::create([
    'name' => 'Test',
    'email' => 'test@domain.com',
    'password' => Hash::make('password'),
    'role_id' => 2, // Admin
    'nip' => '123456789012345678'
]);

# Update user password
User::find(1)->update(['password' => Hash::make('newpass')]);

# Reset password flag
User::find(1)->update(['is_password_reset' => false]);

# Export query results
User::all()->pluck('email')->each(fn($e) => echo "$e\n");
```

---

## 🎨 Frontend (Livewire & Alpine)

### Component Structure
```php
// app/Livewire/Spd/SpdIndex.php
class SpdIndex extends Component
{
    // Properties
    public $search = '';
    public $sortBy = 'created_at';
    
    // Reactive
    #[Computed]
    public function spds()
    {
        return Spd::query()
            ->where('employee_id', auth()->id())
            ->when($this->search, fn($q) => 
                $q->where('destination', 'like', "%{$this->search}%")
            )
            ->orderBy($this->sortBy, 'desc')
            ->paginate();
    }
    
    // Actions
    public function create()
    {
        return redirect()->route('spd.create');
    }
    
    public function delete($id)
    {
        Spd::find($id)->delete();
        // Auto-refresh due to reactive properties
    }
    
    public function render()
    {
        return view('livewire.spd.spd-index');
    }
}
```

### Alpine.js Integration
```blade
<!-- Blade template -->
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    
    <div x-show="open" x-transition>
        <p>Hidden content</p>
    </div>
    
    <!-- Livewire + Alpine combo -->
    <button wire:click="approve($id)" 
            @click="isLoading = true"
            :disabled="isLoading">
        Approve
    </button>
</div>
```

---

## 🔗 API Endpoints Reference

### Authentication
```
POST   /api/auth/login              - Login
POST   /api/auth/logout             - Logout
GET    /api/auth/user               - Current user
```

### SPPD Management
```
GET    /api/sppd                    - List (paginated)
POST   /api/sppd                    - Create
GET    /api/sppd/{id}               - Show
PUT    /api/sppd/{id}               - Update
DELETE /api/sppd/{id}               - Delete
POST   /api/sppd/{id}/submit        - Submit
POST   /api/sppd/{id}/approve       - Approve
POST   /api/sppd/{id}/reject        - Reject
POST   /api/sppd/{id}/complete      - Complete
```

### Mobile API
```
GET    /api/mobile/dashboard        - Dashboard data
GET    /api/mobile/sppd             - List SPPDs
GET    /api/mobile/sppd/{id}        - SPPD detail
POST   /api/mobile/sppd/{id}/submit - Quick submit
POST   /api/mobile/sppd/{id}/approve- Quick approve
GET    /api/mobile/notifications    - Get notifications
POST   /api/mobile/notifications/{id}/read - Mark read
```

### Testing API with Sanctum

```php
// Create token
$token = User::find(1)->createToken('api-token')->plainTextToken;

// Use in requests
curl -H "Authorization: Bearer $token" \
     http://127.0.0.1:8000/api/sppd
```

---

## 🛠️ Common Development Tasks

### Create New Livewire Component
```bash
php artisan livewire:make Spd/SpdEdit --volt
```

### Generate Model + Migration
```bash
php artisan make:model Cost -m
```

### Create API Resource
```bash
php artisan make:resource SppdResource
```

### Create Job Class
```bash
php artisan make:job GeneratePdfJob
```

### Create Service Class
```bash
# Create manually: app/Services/YourService.php
class YourService
{
    public function handle() {}
}
```

### Run Tests
```bash
# All tests
composer run test

# Specific test
composer run test -- tests/Feature/SpdTest.php

# With coverage
php artisan test --coverage
```

---

## 🐛 Debugging Tips

### Enable Query Logging
```php
// In routes/web.php or controller
use Illuminate\Support\Facades\DB;

DB::listen(function ($query) {
    \Log::info($query->sql, $query->bindings);
});
```

### Log Messages
```php
use Illuminate\Support\Facades\Log;

Log::info('SPPD submitted', ['spd_id' => $spd->id]);
Log::error('Approval failed', ['error' => $e->getMessage()]);
Log::debug('Debug info', $data);
```

### View Logs
```bash
# Real-time log viewer
php artisan pail

# Or view file
storage/logs/laravel-*.log
```

### Tinker Debugging
```bash
php artisan tinker

# Get recent errors
Log::latest()->first()

# Check cache
Cache::get('key')

# Check queue
\Illuminate\Queue\Events\JobFailed::resolved()
```

### Browser DevTools
```php
// In controller, before returning
dd($data);  // Dump & die
dump($data);  // Dump only

// In blade template
@dd($variable)
@dump($variable)
```

---

## 📊 Performance Tuning

### Optimize Queries
```php
// ❌ Bad: N+1 query problem
$spds = Spd::all();
foreach ($spds as $spd) {
    echo $spd->employee->name; // Separate query each time
}

// ✅ Good: Eager load
$spds = Spd::with('employee')->get();
foreach ($spds as $spd) {
    echo $spd->employee->name; // Data already loaded
}
```

### Chunking Large Datasets
```php
// Process 1000 records at a time
Spd::chunk(1000, function ($spds) {
    foreach ($spds as $spd) {
        // Process
    }
});
```

### Use Indexes
```php
// In migration
Schema::table('spds', function (Blueprint $table) {
    $table->index(['employee_id', 'status']);
    $table->index('created_at');
});
```

### Caching Patterns
```php
// Cache with key invalidation
Cache::remember('dashboard:stats:' . auth()->id(), 
    now()->addMinutes(15), 
    function () {
        return Spd::getDashboardStats();
    }
);

// Invalidate when data changes
Cache::forget('dashboard:stats:' . auth()->id());
```

---

## 🚀 Deployment Checklist

### Pre-Production
```bash
# Clear caches
php artisan cache:clear
php artisan config:cache
php artisan view:cache
php artisan route:cache
php artisan optimize

# Compile assets
npm run build

# Update database
php artisan migrate --force

# Verify configuration
php artisan config:cache
env APP_ENV=production
env APP_DEBUG=false
```

### Docker Production
```bash
# Build image
docker-compose build

# Start services
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate --force

# Seed data
docker-compose exec app php artisan db:seed
```

### Monitoring
```bash
# Check queue status
php artisan queue:failed

# View logs
docker-compose logs -f app

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

---

## 🔗 Important Files & Locations

| File/Folder | Purpose |
|------------|---------|
| `config/esppd.php` | Custom e-SPPD configuration |
| `app/Services/ApprovalService.php` | Approval workflow logic |
| `app/Services/NomorSuratService.php` | Auto letter numbering |
| `app/Http/Middleware/CheckRoleLevel.php` | Role-based access |
| `database/migrations/` | Schema definitions |
| `routes/web.php` | Web routes & Livewire |
| `routes/api.php` | REST API routes |
| `document-service/` | Python microservice |
| `storage/documents/` | Generated PDFs/DOCX |
| `storage/logs/` | Application logs |

---

## 📚 Documentation Files

- **DEPTH_SCAN_ANALYSIS.md** - Complete project analysis
- **ARCHITECTURE_ANALYSIS.md** - System architecture & dataflow
- **RUNNING_GUIDE.md** - How to run locally
- **MASTER_DOC.md** - Feature documentation
- **PROJECT_CLOSURE.md** - Status report
- **RANGKUMAN_PROYEK.md** - Project overview

---

## ⚡ Key Artisan Commands

```bash
# Serving & Development
php artisan serve                    # Start dev server
php artisan tinker                  # Interactive shell
php artisan queue:work              # Start queue worker

# Database
php artisan migrate                 # Run migrations
php artisan db:seed                 # Seed database
php artisan migrate:refresh         # Reset database

# Code Quality
./vendor/bin/pint                   # Format code
php artisan test                    # Run tests

# Cache & Config
php artisan cache:clear             # Clear cache
php artisan config:cache            # Cache config
php artisan optimize                # Optimize app

# Mail & Notifications
php artisan tinker                  # Test notifications
# In tinker: Notification::send(User::first(), new YourNotification())

# Viewing & Debugging
php artisan pail                    # View logs in real-time
php artisan route:list              # Show all routes
php artisan model:list              # Show all models
```

---

## 🎓 Learning Resources

### Laravel Documentation
- https://laravel.com/docs/11
- https://livewire.laravel.com
- https://laravel.com/docs/11/authentication

### Tailwind CSS
- https://tailwindcss.com/docs
- https://tailwindui.com

### Alpine.js
- https://alpinejs.dev

### PostgreSQL
- https://www.postgresql.org/docs/

### Redis
- https://redis.io/docs

---

## 🆘 Troubleshooting

### Common Issues

| Problem | Solution |
|---------|----------|
| **Port 8000 already in use** | Change port: `php artisan serve --port=8080` |
| **Redis connection failed** | Ensure Redis running: `redis-cli ping` |
| **Database connection error** | Check `.env` DB config, ensure PostgreSQL running |
| **Queue not processing** | Start worker: `php artisan queue:work` |
| **Vite asset 404** | Run: `npm run build` or `npm run dev` |
| **Python service error** | Check venv activated, ports not conflicting |
| **Permission denied** | Run: `chmod -R 775 storage bootstrap/cache` |

### Get Help

1. Check logs: `storage/logs/laravel-*.log`
2. Enable query logging for SQL issues
3. Use `php artisan tinker` to debug models
4. Check browser console for JavaScript errors
5. Review `.env` configuration

---

**Last Updated:** 29 January 2026  
**Version:** 1.0  
**Status:** Production Ready
