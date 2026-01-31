# PC Client: Quick Setup Checklist

## 🚀 Copy-Paste Command Sequence

Run ini di PC Client (`C:\laragon\www\eSPPD_new`):

```powershell
# ========== STEP 1: SETUP .ENV ==========
Copy-Item .env.example .env
notepad .env
# Edit dengan config dari PC_CLIENT_SETUP_GUIDE.md section 2️⃣

# ========== STEP 2: VERIFY DEPENDENCIES ==========
php -v
composer -v
node -v
npm -v

# ========== STEP 3: CREATE DATABASE ==========
# Via Laragon HeidiSQL/pgAdmin, create database: esppd_client
# Atau via command:
# psql -U postgres
# CREATE DATABASE esppd_client;

# ========== STEP 4: RUN MIGRATIONS ==========
php artisan migrate
php artisan db:seed  # optional

# ========== STEP 5: VERIFY DATABASE ==========
php artisan tinker
# Type: DB::connection()->getPDO();
# Type: exit

# ========== STEP 6: START DEV SERVER ==========
# Terminal 1:
php artisan serve

# Terminal 2 (new):
npm run dev

# ========== STEP 7: VERIFY IN BROWSER ==========
# Open browser:
# http://localhost:8000
# http://localhost:5173
```

---

## ✅ Verification Checklist

```powershell
# Verify php & composer
composer check-platform-reqs
# Expected: ✅ All requirements met

# Verify npm packages
npm list vite
npm list --depth=0
# Expected: ✅ All packages installed

# Verify Laravel setup
php artisan config:list | grep -i database
# Expected: ✅ Shows database config

# Verify database connection
php artisan tinker
# >>> DB::table('users')->count();
# >>> exit
# Expected: Integer (0 atau lebih)

# Verify migrations
php artisan migrate:status
# Expected: ✅ All migrations show "Ran"

# Verify routes
php artisan route:list | head -10
# Expected: ✅ Lists routes

# Verify cache
php artisan cache:clear
# Expected: Application cache cleared
```

---

## 📊 Status Comparison: PC Server vs PC Client

| Item | PC Server (192.168.1.16) | PC Client (192.168.1.11) |
|------|--------------------------|--------------------------|
| **Location** | C:\laragon\www\eSPPD | C:\laragon\www\eSPPD_new |
| **Database** | esppd | esppd_client |
| **APP_URL** | http://192.168.1.16:8083 | http://localhost:8000 |
| **Vite Port** | (built) | 5173 |
| **Laravel Port** | 8083 (nginx) | 8000 (artisan serve) |
| **Database Sync** | Independent | Independent |
| **Source Code** | Master | Clone from GitHub |
| **.env** | Production-like | Development |

---

## 🔄 Git Workflow (After Setup)

```powershell
# Start day
git pull origin main

# Edit files
# ...

# Before switching PC
git add .
git commit -m "Feat: [description]"
git push origin main

# On other PC
git pull origin main
```

---

## 📚 Reference Docs

- **Full Setup Guide**: `PC_CLIENT_SETUP_GUIDE.md`
- **Git Workflow**: `GIT_WORKFLOW_2PC.md`
- **RBAC Guide**: `RBAC_IMPLEMENTATION_GUIDE.txt`

---

## 🆘 Issues?

Check:
1. `PHP -version` (must be 8.2+)
2. `.env` configured correctly
3. Database exists: `psql -U postgres -l | grep esppd_client`
4. PostgreSQL running: `psql -U postgres -c "SELECT version();"`
5. Port 8000 available: `netstat -ano | findstr :8000`

**Stuck?** Review `PC_CLIENT_SETUP_GUIDE.md` section 8️⃣ (Common Issues)
