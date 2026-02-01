# ✅ LOGIN SYSTEM - COMPREHENSIVE FIX GUIDE

## Issues Identified & Fixed

### 1. **NIP to Email Conversion Error**
❌ **SEBELUMNYA**: Convert `198302082015031501` → `198302082015031501@uinsaizu.ac.id` (Wrong!)
✅ **SEKARANG**: Find Employee by NIP → Get User email from relation

### 2. **Missing is_password_reset Field**
❌ **SEBELUMNYA**: Field belum ada di semua users
✅ **SEKARANG**: Migration `2026_02_01_000000_ensure_auth_schema.php` adds field

### 3. **Wrong Employee-User Relationship**
❌ **SEBELUMNYA**: Assuming relasi yang tidak jelas
✅ **SEKARANG**: Employee belongsTo User (employee.user_id = user.id)

---

## Correct Login Flow (Step-by-Step)

```php
Step 1: User input NIP (18 digit) + Password
    ↓
Step 2: Find Employee WHERE nip = input
    ├─→ Not found? → Error: "NIP tidak ditemukan"
    ↓
Step 3: Get User from Employee relation ($employee->user)
    ├─→ User null? → Error: "Akun belum terdaftar"
    ↓
Step 4: Auth::attempt(['email' => $user->email, 'password' => input])
    ├─→ Failed? → Error: "Password salah"
    ├─→ Success? Continue
    ↓
Step 5: Check is_password_reset flag
    ├─→ is_password_reset = false? → Redirect to force-change-password
    ├─→ is_password_reset = true? → Redirect to dashboard
```

---

## Database Schema (Source of Truth)

```sql
-- employees table
CREATE TABLE employees (
    id UUID PRIMARY KEY,
    nip VARCHAR(18) UNIQUE NOT NULL,    -- 18-digit NIP
    name VARCHAR(255),
    birth_date DATE,                     -- For DDMMYYYY password
    user_id BIGINT UNSIGNED,             -- FK to users
    approval_level INT,                  -- 1-6 hierarchy (not roles table)
    ...
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- users table
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY,
    nip VARCHAR(18) UNIQUE,              -- NIP (secondary login identifier)
    email VARCHAR(255) UNIQUE,           -- Primary login identifier
    password VARCHAR(255),               -- Hashed DDMMYYYY initially
    is_password_reset BOOLEAN DEFAULT 0, -- Flag for first login
    role_id BIGINT UNSIGNED,             -- FK to roles (RBAC)
    employee_id BIGINT UNSIGNED,         -- FK to employees
    ...
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL
);
```

---

## Eloquent Relationships (CORRECT WAY)

```php
// User Model
public function employee(): BelongsTo
{
    return $this->belongsTo(Employee::class);
}

// Get user's approval level
$level = auth()->user()->employee->approval_level; // 1-6

// Employee Model
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

---

## Updated Login Logic (login.blade.php)

```php
public function login(): void
{
    // Validate input
    $this->validate([
        'nip' => 'required|numeric|digits:18',
        'password' => 'required|string',
    ]);

    // Step 1: Find Employee by NIP
    $employee = Employee::where('nip', $this->nip)->first();
    if (!$employee) {
        throw ValidationException::withMessages([
            'nip' => 'NIP tidak ditemukan dalam sistem.',
        ]);
    }

    // Step 2: Get User from Employee
    $user = $employee->user;
    if (!$user) {
        throw ValidationException::withMessages([
            'nip' => 'Akun pengguna belum terdaftar untuk NIP ini.',
        ]);
    }

    // Step 3: Authenticate using User's email
    if (!Auth::attempt(['email' => $user->email, 'password' => $this->password])) {
        throw ValidationException::withMessages([
            'nip' => 'NIP atau password salah.',
        ]);
    }

    Session::regenerate();

    // Step 4: Check password reset flag
    $user = Auth::user();
    if ($user && !$user->is_password_reset) {
        $this->redirect(route('auth.force-change-password'), navigate: true);
        return;
    }

    $this->redirectIntended(default: route('dashboard'), navigate: true);
}
```

---

## Blade Template Logic (CORRECT WAY)

```blade
{{-- ✅ CORRECT: Get approval level from Employee relation --}}
@if(auth()->user()->employee->approval_level >= 4)
    <a href="/dekan-dashboard">Dekan Dashboard</a>
@endif

{{-- ✅ CORRECT: Get employee info --}}
<p>Nama: {{ auth()->user()->employee->name }}</p>
<p>NIP: {{ auth()->user()->employee->nip }}</p>
<p>Unit: {{ auth()->user()->employee->unit->name }}</p>

{{-- ❌ WRONG (Don't do this): --}}
@if(auth()->user()->role == 'dekan')    {{-- role field tidak jadi string identifier --}}
@if(auth()->user()->approval_level)     {{-- approval_level ada di Employee, bukan User --}}
@role('dekan')                          {{-- Blade directive tidak exist tanpa Spatie Permission --}}
```

---

## Middleware Usage (Route Protection)

```php
// routes/web.php

// Check level >= 4 (Dekan and above)
Route::get('/dekan-dashboard', DekanDashboard::class)
    ->middleware('auth')
    ->middleware(function ($request, $next) {
        if ($request->user()->employee->approval_level < 4) {
            abort(403, 'Anda harus Dekan atau lebih tinggi');
        }
        return $next($request);
    });

// Or with custom middleware: CheckApprovalLevel
Route::get('/dekan-dashboard', DekanDashboard::class)
    ->middleware('auth', 'approval-level:4,5,6');
```

---

## Password Reset Flow

```php
// Force Change Password Component
public function changePassword(): void
{
    $this->validate([
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    auth()->user()->update([
        'password' => Hash::make($this->password),
        'is_password_reset' => true,  // ← Set flag to true
    ]);

    $this->redirect(route('dashboard'));
}
```

---

## Files Updated

1. ✅ `resources/views/livewire/pages/auth/login.blade.php` - Fixed NIP lookup
2. ✅ `app/Livewire/Pages/Auth/ForceChangePassword.php` - Already correct
3. ✅ `database/migrations/2026_02_01_000000_ensure_auth_schema.php` - Ensure schema
4. ✅ `app/Http/Middleware/CheckApprovalLevel.php` - New middleware for level checking

---

## Testing Checklist

- [ ] Migration runs without error
- [ ] Existing users have is_password_reset field
- [ ] 461 users imported successfully
- [ ] Login with NIP → redirects to force-change-password
- [ ] Change password → is_password_reset = true
- [ ] Re-login → redirects to dashboard
- [ ] Dashboard shows role-specific content per approval_level
- [ ] Middleware blocks unauthorized access

---

## Deploy Instructions

```bash
# 1. Pull latest code
git pull origin main

# 2. Run new migration
php artisan migrate

# 3. Import 461 users (production only)
php import-461-users.php

# 4. Test login
php test-461-users-login.php

# 5. Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Deploy to production
git push origin main
```

---

## Common Errors & Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| "NIP tidak ditemukan" | Employee not in DB | Check employee import/seed |
| "Akun belum terdaftar" | Employee.user_id is null | Link User to Employee |
| "NIP atau password salah" | Email not found or password mismatch | Check email derivation logic |
| Dashboard not showing data | employee relation not eager loaded | Use `User::with('employee')` |
| Middleware 403 error | approval_level < required | Check Employee.approval_level value |

