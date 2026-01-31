# 🚀 PRODUCTION DEPLOYMENT GUIDE - eSPPD

## Server Details
- **IP Address**: 192.168.1.27
- **Domain**: esppd.infiatin.cloud (HTTPS)
- **User**: tholib_server
- **Password**: 065820Aaaa
- **App Directory**: /var/www/esppd
- **Database**: PostgreSQL on 192.168.1.27:5432

## Features Deployed in This Release

### ✅ Phase 1: Admin Management System
- **User Management** (`/admin/user-management`)
  - CRUD operations for users
  - Role assignment
  - Organization assignment
  - Password management

- **Role Management** (`/admin/role-management`)
  - Create/edit roles with custom slugs
  - Permission mapping with categories
  - Level-based access control

- **Organization Management** (`/admin/organization-management`)
  - Dual-tab interface (Organizations & Units)
  - Hierarchical structure
  - Unit count aggregates

- **Delegation Management** (`/admin/delegation-management`)
  - Approval delegation lifecycle
  - Active/inactive status toggle
  - Date range validation

### ✅ Phase 2: Audit & Monitoring System
- **Audit Log Viewer** (`/admin/audit-logs`)
  - Advanced 5-filter system (entity, action, user, date range)
  - CSV export capability
  - Change detail inspection

- **Activity Dashboard** (`/admin/activity-dashboard`)
  - Period-based analytics (7/30/90 days, all-time)
  - Top users & entities tracking
  - Activity breakdown by action type

### ✅ Phase 3: User-Centric SPD Management
- **Approval Status Page** (`/dashboard/approval-status`)
  - Personal approval tracking
  - Timeline visualization (L1-L4 approval levels)
  - Recent activity section

- **My Delegation Page** (`/dashboard/my-delegations`)
  - Personal delegation management
  - Active/inactive status toggle
  - Self-service approval authority delegation

---

## Deployment Steps

### Option 1: Manual SSH Deployment (Recommended for First Deploy)

```bash
# SSH into production server
ssh tholib_server@192.168.1.27
# When prompted, enter password: 065820Aaaa

# Navigate to app directory
cd /var/www/esppd

# Deploy using provided script
bash deployment/deploy_production.sh
```

**What the script does:**
1. ✅ Git pull latest changes
2. ✅ Install Composer dependencies
3. ✅ Run database migrations
4. ✅ Cache configuration & routes
5. ✅ Optimize application
6. ✅ Restart Supervisord services

### Option 2: Automated via Git Webhook (Setup Required)

For automatic deployment on each push to `main` branch:

1. **On Production Server** (`192.168.1.27`):
   ```bash
   # Install GitHub webhook handler (e.g., webhook)
   cd /var/www
   sudo apt-get install webhook
   
   # Create webhook config at /etc/webhook.conf.json
   # Point to deployment script
   ```

2. **On GitHub**:
   - Go to Repository Settings → Webhooks
   - Add Payload URL: `http://192.168.1.27:9000/hooks/deploy`
   - Select `push` events
   - Content type: `application/json`

---

## Testing Deployed Features

### Test Checklist

After deployment, verify these 8 pages are accessible at `https://esppd.infiatin.cloud`:

- [ ] `/admin/user-management` - User CRUD with role assignment
- [ ] `/admin/role-management` - Role & permission management
- [ ] `/admin/organization-management` - Organization & unit management
- [ ] `/admin/delegation-management` - Approval delegation management
- [ ] `/admin/audit-logs` - Audit log viewer with filters & export
- [ ] `/admin/activity-dashboard` - Activity analytics dashboard
- [ ] `/dashboard/approval-status` - Personal approval status tracking
- [ ] `/dashboard/my-delegations` - Personal delegation management

### Test User Roles Required
- **Admin Level** (role.level >= 98): For all admin pages
- **User Level** (role.level >= 1): For dashboard pages

### Database Models Verified
```
✅ Users (with roles, organizations)
✅ Roles (with permissions)
✅ Organizations & Units (hierarchical)
✅ ApprovalDelegations
✅ AuditLogs (for all changes)
✅ Approvals (SPD approval workflow)
```

---

## Git Commits in This Release

1. **19c36c2** - feat: implement comprehensive admin management pages
   - UserManagement, RoleManagement, OrganizationManagement, DelegationManagement

2. **52e7890** - feat: implement audit logs viewer and activity dashboard
   - AuditLogViewer, ActivityDashboard

3. **a986488** - feat: implement approval status and delegation management pages
   - ApprovalStatusPage, MyDelegationPage

4. **5107aeb** - chore: add production deployment scripts
   - deployment/deploy_production.sh, deploy_production.bat, deploy_production.ps1

---

## Environment Configuration (Production)

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://esppd.infiatin.cloud

DB_HOST=192.168.1.27
DB_DATABASE=esppd
DB_USERNAME=postgres
DB_PASSWORD=Esppd@123456

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=file
```

---

## Troubleshooting

### If deployment fails:

1. **Check Git Access**:
   ```bash
   ssh tholib_server@192.168.1.27
   cd /var/www/esppd
   git pull origin main
   ```

2. **Check Database Connection**:
   ```bash
   php artisan tinker
   # Test: DB::connection()->getPdo()
   ```

3. **Check PHP Version** (should be >= 8.4):
   ```bash
   php -v
   ```

4. **Check File Permissions**:
   ```bash
   sudo chown -R www-data:www-data /var/www/esppd
   sudo chmod -R 775 /var/www/esppd/storage
   ```

5. **View Application Logs**:
   ```bash
   tail -f /var/www/esppd/storage/logs/laravel.log
   ```

---

## Next Steps (Phase 4+)

Potential features for next deployment:
- Notification Center (real-time alerts)
- Budget Analytics Dashboard
- SPD Timeline & History
- Mobile-responsive optimization
- Advanced reporting with filters

---

**Last Updated**: February 1, 2026  
**Status**: ✅ Ready for Production Deployment  
**Deployment Date**: _[To be filled after deployment]_
