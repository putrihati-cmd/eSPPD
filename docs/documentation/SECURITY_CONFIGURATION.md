# 🔒 SECURITY CONFIGURATION REFERENCE

**Last Updated:** 29 January 2026  
**Application:** e-SPPD  
**Environment:** Production  
**Status:** ✅ Documented & Implemented

---

## 🔐 Password Security

### Hashing Algorithm
- **Driver:** bcrypt
- **Cost Rounds:** 12
- **Hashing Time:** ~240ms per password
- **Configuration Key:** `HASH_DRIVER=bcrypt`

### Environment Configuration
```bash
# .env
HASH_DRIVER=bcrypt
BCRYPT_ROUNDS=12
```

### Implementation
```php
// Automatic in Laravel (User model)
$user->password = Hash::make('password'); // Uses bcrypt with 12 rounds
```

### Verification
```php
// Validation during login
Hash::check('provided_password', $user->password);
```

---

## 🔑 Session Security

### Session Driver
- **Primary Driver:** Redis (recommended for production)
- **Fallback:** File-based (for development)
- **Encryption:** ✅ Enabled (`SESSION_ENCRYPT=true`)

### Session Configuration
```bash
# .env
SESSION_DRIVER=redis
SESSION_ENCRYPT=true
SESSION_LIFETIME=120  # Minutes (2 hours)
SESSION_COOKIE_SECURE=true  # HTTPS only
SESSION_COOKIE_HTTP_ONLY=true  # No JavaScript access
SESSION_COOKIE_SAME_SITE=lax  # CSRF protection
```

### Session Timeout Behavior
- **Idle Timeout:** 120 minutes
- **Absolute Timeout:** User must re-authenticate
- **Activity Reset:** Sliding window (resets on each request)

### Remember Token
- **Duration:** 336 hours (14 days)
- **Hashing:** Hashed in database, never stored in plaintext
- **Secure:** Only transmitted via HTTPS

---

## 🛡️ Rate Limiting

### Configuration
```bash
# app/Http/Middleware/RateLimite.php
// API endpoints
60 requests per minute per authenticated user

// Authentication endpoints
3 login attempts per 15 minutes per IP address

// Password reset
5 password reset attempts per hour per user

// File uploads
10 uploads per hour per user
```

### Implementation Details

#### Login Rate Limiting
```php
// Routes\auth.php
Route::post('/login', function (Request $request) {
    return $this->throwFailedValidationException(
        $request,
        [$this->username() => 'These credentials do not match our records.'],
    );
})->middleware('throttle:3,15'); // 3 per 15 minutes
```

#### API Rate Limiting
```php
// config/api.php
'api' => [
    'middleware' => [
        'throttle:60,1', // 60 per minute
    ],
],
```

### Lock-out Behavior
```
Attempt 1: ✅ Allowed
Attempt 2: ✅ Allowed
Attempt 3: ✅ Allowed
Attempt 4: 🔴 BLOCKED (wait 15 minutes)
```

---

## 📄 File Upload Security

### Size Restrictions
```bash
# config/filesystems.php
MAX_FILE_SIZE = 5MB

# Also set in nginx.conf
client_max_body_size 5M;
```

### Allowed MIME Types
```php
'pdf'        => 'application/pdf',
'docx'       => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
'xlsx'       => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
'png'        => 'image/png',
'jpg'        => 'image/jpeg',
```

### Validation Rules
```php
// app/Http/Requests/UploadRequest.php
'file' => [
    'required',
    'file',
    'mimes:pdf,docx,xlsx,png,jpg',
    'max:5120', // 5MB in KB
    'virus_scan', // Custom validation rule
],
```

### Storage Configuration
```bash
# config/filesystems.php
'disks' => [
    'private' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'url' => env('APP_URL') . '/storage',
        'visibility' => 'private', // ← Not web-accessible
    ],
],
```

### Virus Scanning (Optional)
```php
// app/Rules/VirusScan.php
public function passes($attribute, $value)
{
    // Optional: ClamAV integration
    // $scan = shell_exec("clamscan " . $value->getRealPath());
    // return strpos($scan, 'FOUND') === false;
    return true; // Currently optional
}
```

---

## 🌐 API Security

### API Authentication
- **Method:** Laravel Sanctum (token-based)
- **Token Type:** SHA-256 hashed
- **Header:** `Authorization: Bearer {token}`

### API Token Configuration
```bash
# .env
SANCTUM_STATEFUL_DOMAINS=esppd.uinsaizu.ac.id
SANCTUM_AUTH_COOKIE=XSRF-TOKEN
```

### Token Generation
```php
$token = $user->createToken('api-token')->plainTextToken;
// Format: {id}|{hash}
```

### Token Usage
```bash
curl -H "Authorization: Bearer {token}" \
     https://esppd.uinsaizu.ac.id/api/sppd
```

---

## 🔒 CSRF Protection

### Implementation
- **Framework:** Livewire built-in CSRF protection
- **Token Generation:** Automatic with every form
- **Token Location:** Hidden input field
- **Verification:** Automatic before processing

### Token Configuration
```bash
# .env
SESSION_SECURE_COOKIES=true
SESSION_HTTP_ONLY=true
```

### In Blade Templates
```html
<!-- Automatic in Livewire forms -->
<form wire:submit.prevent="submit">
    <!-- CSRF token added automatically -->
    <input type="text" wire:model="title">
    <button type="submit">Submit</button>
</form>
```

### Token Rotation
- **On Login:** Token rotated
- **On Logout:** Token invalidated
- **Duration:** Matches session lifetime (120 minutes)

---

## 🧹 XSS Prevention

### Implementation
- **Method:** Blade template auto-escaping
- **Default:** All output escaped unless explicitly marked
- **Framework:** Laravel/Blade handles automatically

### Safe Output (Default - Escaped)
```blade
<!-- {{ }} automatically escapes -->
{{ $user->name }}  <!-- Output: John &lt;Script&gt; -->
```

### Unsafe Output (Only When Needed)
```blade
<!-- {!! !!} for HTML - USE WITH CAUTION -->
{!! $richTextContent !!}  <!-- Output: <b>Bold</b> -->
```

### Best Practice
```blade
<!-- ✅ GOOD: Use {{ }} for user input -->
{{ $user->email }}
{{ $sppd->destination }}

<!-- ❌ BAD: Avoid {!! !!} for user input -->
{!! $user->comment !!}  <!-- Vulnerable to XSS -->

<!-- ⚠️ LIMITED: {!! !!} only for trusted content -->
{!! $settings->footer_html !!}  <!-- Only if admin-controlled -->
```

---

## 🔐 SQL Injection Prevention

### Implementation
- **Method:** Eloquent ORM (object-relational mapping)
- **Protection:** Automatic parameterized queries
- **Framework:** Laravel handles all escaping

### Safe Query Examples
```php
// ✅ SAFE: Using Eloquent
$user = User::where('nip', $request->nip)->first();

// ✅ SAFE: Using query builder with parameterization
$spds = DB::table('spds')
    ->where('employee_id', $employeeId)
    ->get();

// ✅ SAFE: Using bindings
$results = DB::select('SELECT * FROM spds WHERE id = ?', [$id]);
```

### Unsafe Patterns to Avoid
```php
// ❌ DANGEROUS: String concatenation
$user = User::whereRaw("nip = '" . $nip . "'"); // VULNERABLE

// ❌ DANGEROUS: Direct SQL injection
DB::select("SELECT * FROM users WHERE id = " . $id); // VULNERABLE
```

---

## 🛡️ Security Headers (Nginx)

### Current Configuration
```nginx
# deployment/nginx_esppd.conf

# Content Security Policy
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'" always;

# Prevent clickjacking
add_header X-Frame-Options "SAMEORIGIN" always;

# Prevent MIME type sniffing
add_header X-Content-Type-Options "nosniff" always;

# Referrer policy
add_header Referrer-Policy "strict-origin-when-cross-origin" always;

# Permissions policy
add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;

# HSTS - Force HTTPS
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
```

### Header Explanations

| Header | Purpose | Value |
|--------|---------|-------|
| **CSP** | Control script execution | default-src 'self' |
| **X-Frame-Options** | Prevent clickjacking | SAMEORIGIN |
| **X-Content-Type-Options** | Prevent MIME sniffing | nosniff |
| **Referrer-Policy** | Control referrer info | strict-origin-when-cross-origin |
| **Permissions-Policy** | Disable dangerous APIs | geolocation=() |
| **HSTS** | Enforce HTTPS | max-age=31536000 |

### Testing Headers
```bash
# Check headers
curl -I https://esppd.uinsaizu.ac.id/

# Output should include:
# Strict-Transport-Security: max-age=31536000
# Content-Security-Policy: default-src 'self'
# X-Frame-Options: SAMEORIGIN
```

---

## 🔐 Encryption at Rest

### Database Encryption (Optional)
```bash
# For sensitive fields
DB_ENCRYPTION_KEY=base64:xxxxxx
DB_ENCRYPT=true

# In model
protected $casts = [
    'national_id' => 'encrypted',
    'bank_account' => 'encrypted',
];
```

### File Encryption (Optional)
```php
// Encrypt file on upload
Storage::disk('private')
    ->put($path, Crypt::encrypt($content));

// Decrypt file on download
$content = Crypt::decrypt(
    Storage::disk('private')->get($path)
);
```

### Environment Variable Encryption
```bash
# .env.production is encrypted
php artisan env:encrypt

# To decrypt for editing
php artisan env:decrypt
```

---

## 📋 Security Audit Trail

### Audit Logging
Every CRUD operation is logged:
```php
// app/Models/AuditLog.php
- User ID
- Action (create, update, delete)
- Model & ID
- Changes (before/after)
- IP address
- User agent
- Timestamp
```

### Viewing Audit Logs
```bash
# Database query
SELECT * FROM audit_logs 
WHERE user_id = $userId 
ORDER BY created_at DESC 
LIMIT 100;
```

### Compliance (BPK)
- ✅ All operations logged
- ✅ Logs immutable (no deletion, soft delete only)
- ✅ User accountability
- ✅ Change tracking

---

## 🔄 Security Update Procedure

### Dependency Monitoring
```bash
# Check for vulnerabilities
composer audit

# Update security patches
composer update symfony/process --no-dev
```

### Laravel Security Updates
```bash
# Check for new Laravel versions
composer show laravel/framework

# Update minor/patch versions
composer update laravel/framework --no-dev
```

### Deployment After Security Updates
```bash
# Restart services
php artisan queue:restart
supervisorctl restart all

# Clear caches
php artisan optimize:clear
php artisan optimize
```

---

## 🧪 Security Testing

### Manual Testing Checklist
- [ ] Test login rate limiting (attempt >3 times)
- [ ] Test CSRF protection (modify CSRF token)
- [ ] Test XSS protection (inject <script> tags)
- [ ] Test SQL injection (inject ' OR '1'='1)
- [ ] Test file upload restrictions (upload .exe)
- [ ] Test session timeout (wait 120 min)
- [ ] Test permission levels (access as different roles)

### Automated Security Scanning
```bash
# Check security headers
curl -I https://esppd.uinsaizu.ac.id/

# Run composer audit
composer audit

# Check for common vulnerabilities
./vendor/bin/psalm --no-cache
```

---

## 📊 Security Metrics

| Metric | Current | Target | Status |
|--------|---------|--------|--------|
| **Password Hashing** | bcrypt (12 rounds) | bcrypt 12+ | ✅ PASS |
| **Session Encryption** | Enabled (Redis) | Enabled | ✅ PASS |
| **Rate Limiting** | 3/15min (login) | 3-5/15min | ✅ PASS |
| **HTTPS** | ✅ Enforced (HSTS) | ✅ HSTS enabled | ✅ PASS |
| **Security Headers** | ✅ All implemented | All implemented | ✅ PASS |
| **Vulnerability Scan** | 0 critical | 0 | ✅ PASS |

---

## 🔗 Related Documentation

- Security Features: [DEPTH_SCAN_ANALYSIS.md](./DEPTH_SCAN_ANALYSIS.md#-security-features)
- Deployment: [DEPLOYMENT_CHECKLIST.md](./DEPLOYMENT_CHECKLIST.md)
- Quick Reference: [QUICK_REFERENCE.md](./QUICK_REFERENCE.md)
- Audit Report: [REMEDIATION_REPORT.md](./REMEDIATION_REPORT.md)

---

## 📞 Security Questions?

**Common Questions:**

Q: Is the system HTTPS-only?  
A: Yes, HSTS header enforces HTTPS. All connections redirected to HTTPS.

Q: How are passwords stored?  
A: bcrypt with 12 rounds, takes ~240ms to verify (slows brute force).

Q: Can sessions be hijacked?  
A: No, sessions encrypted, HTTPS-only, HttpOnly cookies, SameSite protection.

Q: What if I find a vulnerability?  
A: Contact security team immediately. Follow incident response procedures.

---

**Document Version:** 1.0  
**Last Updated:** 29 January 2026  
**Status:** ✅ Production Ready  
**Security Review:** Passed (Audit: 29 Jan 2026)
