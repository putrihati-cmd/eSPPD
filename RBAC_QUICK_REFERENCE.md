# 🚀 e-SPPD RBAC Quick Reference Card

## Test Accounts (All use password: `password123`)

| Role | Email | Level | Approval Limit | Access |
|------|-------|-------|-----------------|--------|
| **Admin** | admin@esppd.test | 98 | Unlimited | Full System |
| **Rektor** | rektor@esppd.test | 6 | Unlimited | All SPD/Approval |
| **Warek** | warek@esppd.test | 5 | 100M | All SPD/Approval |
| **Dekan** | dekan@esppd.test | 4 | 50M | All SPD/Approval |
| **Wadek** | wadek@esppd.test | 3 | 10M | View All/Delegate |
| **Kaprodi** | kaprodi@esppd.test | 2 | Can Approve | Approve only |
| **Dosen** | dosen@esppd.test | 1 | Cannot | Create SPD only |

---

## Route Access Control

### SPD Routes (`/spd*`)
- **Requirement:** Level 1+ (All authenticated users)
- **Gates:** `@can('create-spd')`
- **Routes:** index, create, show, history, pdf, revisi

### Approval Routes (`/approvals*`)
- **Requirement:** Level 2+ (Kaprodi+)
- **Gates:** `@can('approve-spd')`
- **Routes:** index, queue

### Reports Routes (`/reports*`)
- **Requirement:** Level 1+ (All authenticated users)
- **Gates:** `@can('create-report')`
- **Routes:** index, builder, create, show, download

### Employee Routes (`/employees*`)
- **Requirement:** Admin only (Level 98)
- **Gates:** `@can('admin-manage-users')`
- **Routes:** index

### Budget Routes (`/budgets*`)
- **Requirement:** All authenticated
- **Gates:** `@can('manage-budget')`
- **Routes:** index

### Settings Routes (`/settings*`)
- **Requirement:** All authenticated
- **Routes:** index

### Admin Routes (`/admin*`)
- **Requirement:** Admin only (Level 98)
- **Routes:** users.index, users.show, users.reset-password

---

## Gates for Blade Templates

```blade
{{-- SPD Access --}}
@can('create-spd')           <!-- Show for all users (Level 1+) -->
@can('approve-spd')          <!-- Show for approvers (Level 2+) -->
@can('view-all-spd')         <!-- Show for Wadek+ (Level 3+) -->
@can('reject-spd')           <!-- Show for approvers (Level 2+) -->

{{-- Approvals --}}
@can('delegate-approval')    <!-- Show for Wadek+ (can delegate) -->
@can('override-approval')    <!-- Show for Dekan+ (can override) -->

{{-- Finance --}}
@can('view-budget')          <!-- Show for finance/admin -->
@can('manage-budget')        <!-- Show for dekan+ (is_executive) -->
@can('approve-budget', $amount)  <!-- Amount-based approval -->

{{-- Admin --}}
@can('admin-manage-users')   <!-- Show for admin only -->
@can('has-permission', 'spd.create')  <!-- Dynamic permission check -->
```

---

## Gate Usage in Controllers

```php
// Simple gate check
if ($user->can('approve-spd')) {
    // Allow approval
}

// Using authorization
$this->authorize('approve-spd');  // Throws 403 if denied

// Amount-based budget approval
if ($user->can('approve-budget', $budgetAmount)) {
    // Allow approval
}

// RbacService direct call
use App\Services\RbacService;

if (RbacService::userHasPermission($user, 'spd.approve')) {
    // Allow
}

if (RbacService::canApproveAmount($user, $amount)) {
    // Allow
}

// Check user level
if ($user->role_level >= 3) {
    // Is Wadek+
}

// Check user roles
if ($user->isApprover()) {
    // Can approve
}

if ($user->isAdmin()) {
    // Full access
}

if ($user->canDelegate()) {
    // Can delegate approval
}

if ($user->canOverride()) {
    // Can force cancel/override
}
```

---

## User Methods Available

```php
// Role checks
$user->isAdmin()              // Level 98+ or role='admin'
$user->isApprover()           // Level 2+ or role='approver'
$user->isFinance()            // Level 3+ or role='finance'
$user->isExecutive()          // Level 4+ (Dekan+)
$user->canDelegate()          // Level 3+ and has permission
$user->canOverride()          // Level 4+ and has permission
$user->hasRole('employee')    // Check specific role
$user->hasMinLevel(3)         // Check level >= value

// Relationships
$user->roleModel()            // BelongsTo Role
$user->role()                 // Alias for roleModel()
$user->permissions()          // BelongsToMany Permission

// Attributes
$user->role_level             // Auto-computed from roleModel->level
```

---

## Sidebar Integration

Sidebar is automatically updated with @can directives:
- SPD section shows only to users with `@can('create-spd')`
- Approval section shows only to users with `@can('approve-spd')`
- Reports section shows only to users with `@can('create-report')`
- Admin section shows only to users with `@can('admin-manage-users')`

---

## Testing

### Run All Tests
```bash
php artisan test --no-coverage
```

### Run RBAC Tests Only
```bash
php artisan test tests/Feature/RbacTest.php tests/Feature/RbacGatesTest.php --no-coverage
```

### Run Simple Tests (Smoke Test)
```bash
php artisan test tests/Feature/SimpleRbacTest.php --no-coverage
```

---

## Key Files Reference

| File | Purpose | Lines |
|------|---------|-------|
| `app/Models/User.php` | User model with RBAC methods | 184 |
| `app/Models/Role.php` | Role model with hierarchy | 80+ |
| `app/Models/Permission.php` | Permission model | 20+ |
| `app/Models/ApprovalDelegation.php` | Delegation system | 58 |
| `app/Services/RbacService.php` | Unified RBAC logic | 144 |
| `app/Providers/AuthServiceProvider.php` | 16 permission gates | 80 |
| `resources/views/components/sidebar.blade.php` | UI with @can | 160 |
| `database/seeders/PermissionSeeder.php` | Permissions setup | 72 |
| `database/seeders/DatabaseSeeder.php` | Production accounts | 10 |

---

## Quick Checks

### Verify RBAC is working:
1. Login with dosen@esppd.test
2. Check sidebar - should see SPD section only
3. Try accessing `/approvals` - should get 403
4. Logout, login with kaprodi@esppd.test
5. Now should see Approval section and can access `/approvals`

### Test budget approval limits:
1. Login with wadek@esppd.test (10M limit)
2. Check gate: `$user->can('approve-budget', 5000000)` → true
3. Check gate: `$user->can('approve-budget', 50000000)` → false

### Test delegation:
1. Login with dekan@esppd.test
2. Can delegate to wadek/warek/rektor (same or higher level)
3. Cannot delegate to kaprodi (lower level)
4. Cannot delegate to self

---

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| "No route found" | Check `/test-routes` page for all routes |
| "403 Unauthorized" | Verify user role level has permission |
| "Gate always returns false" | Check RbacService roleModel usage |
| "Sidebar items not showing" | Check @can directive syntax in view |
| "Test failures" | Run `php artisan migrate:fresh --seed` |

---

## Important Notes

⚠️ **Admin Bypass:** Admin users (level 98) automatically pass ALL gates  
⚠️ **Role Level:** Computed from `roleModel->level`, not stored in users table  
⚠️ **Gates Registered:** All gates registered in `AuthServiceProvider.php`  
⚠️ **Permissions:** Create permissions in `PermissionSeeder.php`  
✅ **Production Data:** DatabaseSeeder.php includes all 10 production accounts with proper roles and DDMMYYYY passwords  

---

**Last Updated:** January 31, 2026  
**Version:** 1.0 (Production Ready)
