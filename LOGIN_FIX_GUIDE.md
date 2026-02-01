# Login Form Fix - NIP to Email Authentication

## Problem Identified
The login form was attempting to authenticate users using the `nip` field directly, but Laravel's authentication system uses the `email` field by default. This caused authentication failures.

## Solution Implemented

### 1. Updated Login Form (login.blade.php)
**File**: `resources/views/livewire/pages/auth/login.blade.php`

Changed the authentication logic to convert NIP to email format before attempting authentication:
```php
// Before: Auth::attempt(['nip' => $this->nip, ...])
// After: Auth::attempt(['email' => $emailToAuth, ...])

// Convert NIP to email format: NIP@uinsaizu.ac.id
$emailToAuth = $this->nip . '@uinsaizu.ac.id';
```

### 2. Updated Database Seeder
**File**: `database/seeders/DatabaseSeeder.php`

Added NIP field when creating users to ensure NIP is stored in database:
```php
User::create([
    'name' => $empData['name'],
    'email' => $empData['email'],
    'nip' => $empData['nip'],  // ← Added this line
    'password' => Hash::make('password'),
    // ... other fields
]);
```

## How It Works
1. User enters NIP (e.g., `198302082015031501`) in login form
2. Form converts NIP to email format: `198302082015031501@uinsaizu.ac.id`
3. Laravel authenticates against the `email` field in users table
4. Session is created and user is redirected to dashboard

## Test Credentials
Default test accounts created by DatabaseSeeder:

| Name | NIP | Password | Role |
|------|-----|----------|------|
| Mawi Khusni Albar | 198302082015031501 | password | admin |
| Ansori | 197505152006041001 | password | dekan |
| Ahmad Fauzi | 196708151988021002 | password | dosen |
| Siti Nur Haliza | 198005102008012001 | password | dosen |
| Budi Santoso | 197301051997031001 | password | dosen |

## Manual Testing Steps

1. Navigate to login page: `https://esppd.infiatin.cloud/login`
2. Enter NIP: `198302082015031501`
3. Enter Password: `password`
4. Click "Masuk ke Dashboard"
5. Should redirect to dashboard successfully

## Deployment

Changes pushed to GitHub with commits:
- `8684b5c` - Fix login form NIP to email conversion for authentication
- `902436a` - Add NIP field to user creation in database seeder

### On Production Server
After deploying these changes, you may need to:
1. Run `git pull origin main` to get latest code
2. Optionally run `php artisan migrate` if fresh installation
3. Optionally run `php artisan db:seed` if you need test users
4. Clear Laravel cache: `php artisan cache:clear` (optional)

## Troubleshooting

### Still getting "NIP atau password salah" error?
1. Verify user exists in database with correct email format
2. Check that password hash in database matches `Hash::make('password')`
3. Verify .env file has correct database credentials

### User not found?
1. Run `php artisan db:seed` to populate test users
2. Ensure migration `2026_01_31_000001_add_nip_to_users_table` has run
3. Check that NIP field exists in users table with `php artisan tinker`

## Related Files
- **Login View**: [resources/views/livewire/pages/auth/login.blade.php](resources/views/livewire/pages/auth/login.blade.php)
- **Database Seeder**: [database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php)
- **User Model**: [app/Models/User.php](app/Models/User.php)
- **Auth Config**: [config/auth.php](config/auth.php)

## Migration
- **Migration File**: [database/migrations/2026_01_31_000001_add_nip_to_users_table.php](database/migrations/2026_01_31_000001_add_nip_to_users_table.php)
- Creates/maintains `nip` column in users table as UNIQUE nullable
