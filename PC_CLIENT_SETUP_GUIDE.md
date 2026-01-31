# Laravel 11 PC Client Setup Guide

## 📋 CHECKLIST: Kelengkapan Setup PC Client

### ✅ Sudah Selesai
- [x] git clone ke C:\laragon\www\eSPPD_new
- [x] composer install
- [x] npm install
- [x] npm run dev (Vite dev server running)

### ⏳ Belum Selesai
- [ ] .env configuration
- [ ] Database setup & migration
- [ ] php artisan serve
- [ ] Verify dependencies
- [ ] Test database connection

---

## 1️⃣ Struktur Project (Verify)

Buka `C:\laragon\www\eSPPD_new` di VS Code. Harus ada folder/file:

```
eSPPD_new/
├── app/                    ✅ (Laravel logic)
├── bootstrap/              ✅
├── config/                 ✅
├── database/               ✅ (migrations, factories)
├── public/                 ✅ (assets, index.php)
├── resources/              ✅ (views, css, js)
├── routes/                 ✅
├── storage/                ✅ (logs, cache, uploads)
├── tests/                  ✅
├── vendor/                 ✅ (PHP dependencies - dari composer install)
├── node_modules/           ✅ (JS dependencies - dari npm install)
├── composer.json           ✅
├── composer.lock           ✅ (generated)
├── package.json            ✅
├── package-lock.json       ✅ (generated)
├── .env                    ❌ BELUM ADA (perlu dibuat)
├── .env.example            ✅ (template)
├── .gitignore              ✅
└── vite.config.js          ✅
```

**Command untuk verify:**
```powershell
cd C:\laragon\www\eSPPD_new
dir -Recurse -Depth 1 | Select-Object Name
```

---

## 2️⃣ .env Configuration untuk PC Client

**Step 1: Copy .env.example ke .env**

```powershell
cd C:\laragon\www\eSPPD_new
Copy-Item .env.example .env
notepad .env
```

**Step 2: Edit .env dengan config di bawah**

```ini
# === APP CONFIG ===
APP_NAME="e-SPPD"
APP_ENV=local
APP_KEY=base64:xDfLxBsH6ZP+n8MfCMmrF73u29i8rHtgg6LI25P91MY=
APP_DEBUG=true
APP_URL=http://localhost:8000

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID

# === DATABASE CONFIG (LOCAL) ===
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1          # ← Lokal, JANGAN ubah
DB_PORT=5432
DB_DATABASE=esppd_client   # ← Buat database baru (berbeda dari Server)
DB_USERNAME=postgres
DB_PASSWORD=               # ← Laragon default kosong, atau ada password?

# === CACHE & QUEUE ===
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# === SESSION ===
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

# === MAIL (optional, pakai log untuk dev) ===
MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525

# === LOGGING ===
LOG_CHANNEL=stack
LOG_LEVEL=debug

# === VITE ===
VITE_APP_NAME="e-SPPD"
```

**⚠️ PENTING DIFFERENCES PC Client vs PC Server:**

| Config | PC Server | PC Client |
|--------|-----------|-----------|
| `APP_URL` | https://esppd.infiatin.cloud | http://localhost:8000 |
| `APP_ENV` | local/production | local |
| `DB_DATABASE` | esppd | esppd_client |
| `DB_HOST` | 127.0.0.1 | 127.0.0.1 |
| `REDIS_HOST` | 127.0.0.1 | 127.0.0.1 |

---

## 3️⃣ Verify Dependencies

### Check PHP & Composer

```powershell
# Verify PHP version (should be 8.2+)
php -v

# Verify Composer
composer -v

# Check if all required packages installed
cd C:\laragon\www\eSPPD_new
composer check-platform-reqs
```

**Expected output:** ✅ All requirements met

### Check Node & npm

```powershell
# Verify Node
node -v    # v18+ required

# Verify npm
npm -v     # v9+ recommended

# List installed npm packages
npm list --depth=0

# Verify Vite installed
npm list vite
```

**Expected output:** ✅ vite@latest

### Check Laravel Dependencies

```powershell
cd C:\laragon\www\eSPPD_new

# List installed Laravel packages
php artisan package:list

# Verify key packages:
# - livewire/livewire ✅
# - livewire/volt ✅
# - laravel/framework ✅
```

---

## 4️⃣ Database Setup

### Step 1: Create Database Lokal

Di Laragon:
1. Klik **MySQL/PostgreSQL** di Laragon
2. Open **HeidiSQL** (atau pgAdmin untuk PostgreSQL)
3. Create database baru: `esppd_client`

```sql
-- Atau via command line
createdb -U postgres esppd_client
```

### Step 2: Run Migrations

```powershell
cd C:\laragon\www\eSPPD_new

# Migrate database schema
php artisan migrate

# Verify (optional)
php artisan migrate:status
```

**Expected output:**
```
  Migrated: 2024_01_01_000000_create_users_table
  Migrated: 2024_01_02_000000_create_roles_table
  ...
  Migration table created successfully.
```

### Step 3: Seed Database (Optional)

```powershell
# Jalankan seeders untuk populate sample data
php artisan db:seed

# Atau specific seeder
php artisan db:seed --class=UserSeeder
```

### Step 4: Verify Database Connection

```powershell
# Test koneksi via Tinker
php artisan tinker
>>> DB::connection()->getPDO();
# Jika OK, akan return object
# Jika ERROR, check .env DB_HOST, DB_PASSWORD

# Check table count
>>> DB::table('users')->count();
# Harus return integer, bukan 0 jika sudah seed
```

---

## 5️⃣ Running Development Server

### Terminal 1: PHP Server (Port 8000)

```powershell
cd C:\laragon\www\eSPPD_new
php artisan serve

# Output:
# Server running on [http://127.0.0.1:8000]
```

### Terminal 2: Vite Dev Server (Port 5173) - Sudah Running

```powershell
# Ini sudah running dari npm run dev
# Verify di browser: http://localhost:5173
```

### Terminal 3: Redis Server - Via Laragon

Buka Laragon → Redis → Start (jika belum running)

---

## 6️⃣ Verify Everything Working

Open browser:

```
✅ http://localhost:8000           (Laravel app)
✅ http://localhost:5173            (Vite dev server)
✅ http://localhost:8000/login      (Login page)
```

**In VS Code Terminal:**

```powershell
# Test Artisan commands
php artisan route:list     # List all routes
php artisan config:list    # List all config

# Test database
php artisan tinker
>>> \App\Models\User::count();
>>> DB::table('users')->limit(1)->get();
```

---

## 7️⃣ .gitignore (Should Already Exist)

File `C:\laragon\www\eSPPD_new\.gitignore` harus exclude:

```
/vendor
/node_modules
.env                    # ← JANGAN commit .env!
.env.*.php
.env.backup
.env.production.backup
.env.local
storage/logs/
storage/framework/
bootstrap/cache/
.DS_Store
.vscode/
*.log
.idea/
```

**Verify:**
```powershell
# Check yang di-ignore
git check-ignore -v vendor/
git check-ignore -v .env
# Harus return path jika dalam .gitignore
```

---

## 8️⃣ First Push ke GitHub (Optional)

```powershell
cd C:\laragon\www\eSPPD_new

# Status should be clean
git status
# Harus show: "nothing to commit, working tree clean"

# Jika ada untracked files:
git add .
git commit -m "Setup: PC Client development environment"
git push origin main
```

---

## 📝 COMPLETE SETUP COMMAND (Copy-Paste)

```powershell
# Navigate
cd C:\laragon\www\eSPPD_new

# 1. Create .env
Copy-Item .env.example .env

# 2. Generate app key (jika belum)
php artisan key:generate

# 3. Create database (via pgAdmin atau command)
# createdb -U postgres esppd_client

# 4. Run migrations
php artisan migrate

# 5. Optional: seed data
php artisan db:seed

# 6. Verify setup
php artisan tinker
# >>> DB::connection()->getPDO();
# >>> exit

# 7. Start dev server
php artisan serve
# Terminal akan block, buka terminal baru untuk next commands

# 8. Di terminal baru, run Vite (if npm run dev not running)
npm run dev

# 9. Open browser
# http://localhost:8000 (Laravel app)
# http://localhost:5173 (Vite)
```

---

## 🚀 When Ready: Sync dengan PC Server

```powershell
# PC Server ada di 192.168.1.16
# Jika ingin sync code changes:

cd C:\laragon\www\eSPPD_new
git pull origin main

# Database tetap lokal (tidak sync)
```

---

## ⚠️ COMMON ISSUES

| Issue | Solution |
|-------|----------|
| `php not found` | Add to PATH: `C:\laragon\bin\php` |
| `composer not found` | Use: `php composer.phar install` |
| `Database connection error` | Check .env DB_PASSWORD, verify PostgreSQL running |
| `Port 8000 already in use` | Use: `php artisan serve --port=8001` |
| `node_modules error` | Try: `npm install --legacy-peer-deps` |
| `Vite not bundling` | Try: `npm run build` (check errors) |
| `Migration error` | Check database exists, run: `php artisan migrate:refresh` |

---

## ✅ FINAL CHECKLIST

- [ ] Folder structure complete (vendor/, node_modules/)
- [ ] .env created & configured
- [ ] `php -v` works (8.2+)
- [ ] `composer check-platform-reqs` ✅
- [ ] `npm list vite` shows vite
- [ ] Database `esppd_client` created
- [ ] `php artisan migrate` successful
- [ ] `php artisan tinker` → `DB::connection()->getPDO()` ✅
- [ ] `php artisan serve` running (port 8000)
- [ ] Vite dev server running (port 5173)
- [ ] Browser: http://localhost:8000 loads ✅
- [ ] Git status clean

**Ketika semua ✅, PC Client ready untuk development!**
