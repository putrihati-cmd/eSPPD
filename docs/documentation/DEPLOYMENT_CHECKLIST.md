# 🚀 DETAILED PRODUCTION DEPLOYMENT CHECKLIST

**Last Updated:** 29 January 2026  
**Application:** e-SPPD  
**Environment:** Production  
**Estimated Duration:** 60 minutes  
**Critical Level:** ⚠️ HIGH - Coordinate with team

---

## 📋 PRE-DEPLOYMENT PHASE (T-24 hours)

### Communications & Planning

- [ ] **Post maintenance window announcement**
  - Where: In-app notification banner
  - Message: "Scheduled maintenance on [DATE] from 2:00-3:00 PM JST"
  - Audience: All system users

- [ ] **Send notification email to stakeholders**
  - To: All faculty/department heads
  - Subject: "e-SPPD System Maintenance Notice"
  - Include: Date, time, expected duration, rollback contact

- [ ] **Brief support team**
  - Review rollback procedures
  - Distribute on-call contact list
  - Confirm communication channels

- [ ] **Verify infrastructure is ready**
  - [ ] Check disk space (minimum 10 GB free)
  - [ ] Verify database connectivity
  - [ ] Confirm Redis working
  - [ ] Test backup storage access

---

### Code & Dependencies Review

- [ ] **Review merged pull requests**
  ```bash
  git log main --oneline --since="7 days ago"
  # Review each commit message
  ```
  
- [ ] **Run security audit**
  ```bash
  composer audit
  # Expected: "No security vulnerability advisories found"
  ```

- [ ] **Run code linting**
  ```bash
  ./vendor/bin/pint --test
  # Expected: "No formatting issues"
  ```

- [ ] **Check for migrations pending**
  ```bash
  php artisan migrate:status
  # All should show "Ran" status
  ```

---

### Testing & Validation

- [ ] **Run full test suite**
  ```bash
  composer test
  # Expected: All tests pass
  ```

- [ ] **Test in staging environment**
  - Deploy code to staging
  - Run SPPD creation workflow
  - Test approval process
  - Generate documents (PDF/DOCX)
  - Verify email notifications

- [ ] **Browser compatibility check**
  - Chrome/Chromium ✅
  - Firefox ✅
  - Safari ✅
  - Edge ✅

---

### Database Preparation

- [ ] **Create full database backup**
  ```bash
  ./scripts/backup-db.sh production "pre-deployment-backup"
  # Output: backup-2026-01-29-14-30-45.dump
  ```

- [ ] **Verify backup integrity**
  ```bash
  pg_restore --list backup-2026-01-29-14-30-45.dump | head -20
  # Should list tables without errors
  ```

- [ ] **Test restoration in dev environment**
  ```bash
  pg_restore -d esppd_dev backup-2026-01-29-14-30-45.dump
  php artisan migrate:status
  # Verify all migrations match
  ```

- [ ] **Record backup details**
  - Backup file: `backup-2026-01-29-14-30-45.dump`
  - Size: [Size in MB]
  - Location: `/backups/production/`
  - Verification: ✅ Passed

---

### Release Preparation

- [ ] **Tag release**
  ```bash
  git tag -a v1.0.0 -m "Release: Security patches, documentation improvements"
  git push origin v1.0.0
  ```

- [ ] **Build frontend assets**
  ```bash
  npm install --production
  npm run build
  # Output: Webpack compiled successfully
  ```

- [ ] **Verify built assets**
  ```bash
  ls -lh public/build/
  # Should contain manifest.json and hashed CSS/JS files
  ```

- [ ] **Create release notes**
  - Title: "e-SPPD v1.0.0 Release"
  - Date: 29 January 2026
  - Changes:
    - Security: Fixed CVE-2026-24739 (symfony/process)
    - Docs: Comprehensive documentation improvements
    - Quality: Enhanced security configuration details

---

### Final Pre-Deployment Checks (1 hour before)

- [ ] **Confirm no last-minute code changes**
  ```bash
  git status
  # Should be: "On branch main, nothing to commit"
  ```

- [ ] **Verify all team members notified**
  - [ ] Dev team
  - [ ] DevOps team
  - [ ] QA team
  - [ ] Support team
  - [ ] Project manager

- [ ] **Check system resources**
  ```bash
  # Check disk space
  df -h
  # Check memory
  free -h
  # Check CPU
  nproc
  # Expected: Sufficient resources available
  ```

- [ ] **Verify database connection**
  ```bash
  php artisan tinker
  echo "Connected";
  ```

- [ ] **Test backup & restore one final time**
  ```bash
  ./scripts/backup-db.sh test
  ./scripts/restore-db.sh test
  # Expected: Both succeed
  ```

- [ ] **Get approval from project lead**
  - [ ] Approval received: ________________
  - [ ] Time: ________________
  - [ ] Signature: ________________

---

## ⚙️ DEPLOYMENT PHASE (T-0 to T+10 minutes)

### Step 1: Enable Maintenance Mode (T-0:00)

```bash
php artisan down --message="System maintenance - expected to complete at 3:00 PM JST"
```

**Verification:**
```bash
curl https://esppd.uinsaizu.ac.id/
# Expected: 503 Service Unavailable with maintenance message
```

**⏱️ Time Elapsed: 1 minute**

---

### Step 2: Pull Latest Code (T-0:30)

```bash
cd /var/www/esppd
git fetch origin
git checkout main
git reset --hard origin/main
git log --oneline -5
```

**Expected Output:**
```
3f8a2c1 (HEAD -> main, tag: v1.0.0) Release: Security patches
d8b5e9f Merge pull request #45: Documentation improvements
c2a4f7e Security: Fix CVE-2026-24739
...
```

**⏱️ Time Elapsed: 2 minutes**

---

### Step 3: Install Dependencies (T-1:00)

```bash
composer install --no-dev --optimize-autoloader
```

**Verification:**
```bash
composer show | head -5
# Should list packages without errors
```

**⏱️ Time Elapsed: 3 minutes**

---

### Step 4: Run Database Migrations (T-1:30)

**First, list migrations to verify:**
```bash
php artisan migrate:status
```

**Then run with force flag for production:**
```bash
php artisan migrate --force
```

**Verification:**
```bash
php artisan migrate:status | tail -5
# All should show "Ran" status
```

**⏱️ Time Elapsed: 5 minutes**

---

### Step 5: Clear Stale Caches (T-2:00)

```bash
php artisan optimize:clear
```

**Verification:**
```bash
# Check cache directory
ls -la storage/framework/cache/
# Should be mostly empty or minimal files
```

**⏱️ Time Elapsed: 6 minutes**

---

### Step 6: Warm Up Caches (T-2:15)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

**Verification:**
```bash
php artisan config:cache
# Expected: "Configuration cached successfully"
```

**⏱️ Time Elapsed: 7 minutes**

---

### Step 7: Restart Background Services (T-2:30)

```bash
# Restart Laravel queue
php artisan queue:restart

# Verify if using supervisord
supervisorctl restart all
supervisorctl status
```

**Expected Output:**
```
laravel_worker                   RUNNING   pid 1234, uptime 0:00:05
```

**⏱️ Time Elapsed: 8 minutes**

---

### Step 8: Disable Maintenance Mode (T-3:00)

```bash
php artisan up
```

**Verification:**
```bash
curl https://esppd.uinsaizu.ac.id/ -I
# Expected: 200 OK (not 503)
```

**⏱️ Time Elapsed: 10 minutes**

---

## ✅ POST-DEPLOYMENT VALIDATION PHASE (T+5 to T+30 minutes)

### Immediate System Checks (T+5 minutes)

#### Check 1: Homepage Accessibility
```bash
curl -I https://esppd.uinsaizu.ac.id/
```

**Expected:**
```
HTTP/1.1 200 OK
Content-Type: text/html; charset=UTF-8
```

- [ ] **PASS** - Status 200 OK
- [ ] **FAIL** - Error encountered

#### Check 2: Application Not in Maintenance Mode
```bash
curl https://esppd.uinsaizu.ac.id/ | grep -i "maintenance" || echo "✅ OK - No maintenance mode"
```

**Expected:**
```
✅ OK - No maintenance mode
```

- [ ] **PASS** - Not in maintenance
- [ ] **FAIL** - Still showing maintenance

#### Check 3: Database Connectivity
```bash
php artisan tinker --execute="echo 'DB Connection: OK';"
```

**Expected:**
```
DB Connection: OK
```

- [ ] **PASS** - Connected
- [ ] **FAIL** - Connection failed

#### Check 4: Verify Migrations Applied
```bash
php artisan migrate:status | tail -3
```

**Expected:**
```
Batch column value: 1
All migrations have been executed.
```

- [ ] **PASS** - All migrations executed
- [ ] **FAIL** - Migrations pending

---

### Functional Validation (T+10 to T+20 minutes)

#### Test 1: User Login Flow
```
1. Open browser: https://esppd.uinsaizu.ac.id/login
2. Enter test credentials:
   - NIP: 123456789012345678
   - Password: test
3. Click "Login"
```

**Expected Result:**
- [ ] Login page loads ✅
- [ ] Redirect to dashboard ✅
- [ ] User profile visible ✅
- [ ] No error messages ✅

**Log File Check:**
```bash
tail -5 storage/logs/laravel.log
# Should NOT contain: ERROR, CRITICAL, Exception
```

#### Test 2: SPPD Creation
```
1. Login as test user
2. Navigate to "New SPPD"
3. Fill form:
   - Destination: Jakarta
   - Purpose: Training
   - Start Date: [Today]
   - Duration: 3 days
4. Click "Save Draft"
```

**Expected Result:**
- [ ] Form loads without errors ✅
- [ ] Save succeeds ✅
- [ ] SPPD appears in list ✅
- [ ] Status: DRAFT ✅

#### Test 3: SPPD Submission
```
1. Select created SPPD
2. Click "Submit for Approval"
3. Confirm action
```

**Expected Result:**
- [ ] Submission succeeds ✅
- [ ] Status changed to: SUBMITTED ✅
- [ ] Notification sent (check inbox) ✅
- [ ] No errors in logs ✅

#### Test 4: Document Generation
```
1. Open SPPD (APPROVED status)
2. Click "Generate PDF"
3. Wait for download
```

**Expected Result:**
- [ ] PDF generates successfully ✅
- [ ] File downloads to browser ✅
- [ ] File size > 50KB ✅
- [ ] PDF viewable ✅

---

### System Monitoring (T+20 to T+25 minutes)

#### Check 1: Queue Processing
```bash
php artisan queue:monitor
```

**Expected Output:**
```
RUNNING (pid 1234) [0 waiting] [0 failed]
```

- [ ] **PASS** - Queue running, no failures
- [ ] **FAIL** - Queue stopped or high failures

#### Check 2: Error Logs
```bash
tail -50 storage/logs/laravel.log
```

**Look for:**
- [ ] No ERROR level entries ✅
- [ ] No CRITICAL level entries ✅
- [ ] No unhandled exceptions ✅
- [ ] Info/Debug logs only ✅

#### Check 3: Response Times
```bash
tail -20 /var/log/nginx/access.log | grep "https://esppd"
```

**Expected:**
- Response times: **< 1000ms** (most requests)
- [ ] **PASS** - All under 1 second
- [ ] **WARNING** - Some > 2 seconds
- [ ] **FAIL** - Many > 5 seconds

#### Check 4: Database Integrity
```bash
php artisan db:check
```

**Expected:**
```
✅ Database integrity verified
```

- [ ] **PASS** - All checks OK
- [ ] **FAIL** - Constraint violations

#### Check 5: Cache Verification
```bash
php artisan tinker
Cache::put('deploy_test', 'success', 60);
echo Cache::get('deploy_test');
exit;
```

**Expected Output:**
```
success
```

- [ ] **PASS** - Cache working
- [ ] **FAIL** - Cache not responding

---

### Performance Check (T+25 to T+30 minutes)

#### Check 1: Resource Utilization
```bash
top -b -n 1 | head -15
```

**Expected:**
```
CPU usage: < 50%
Memory usage: < 70%
Load average: < 4
```

- [ ] **PASS** - All within normal range
- [ ] **WARNING** - One metric elevated
- [ ] **FAIL** - Multiple metrics elevated

#### Check 2: Disk Space
```bash
df -h /var/www/esppd
```

**Expected:**
```
Avail: > 10 GB
```

- [ ] **PASS** - Sufficient disk space
- [ ] **WARNING** - < 20 GB remaining
- [ ] **FAIL** - < 5 GB remaining

#### Check 3: Database Size
```bash
psql -c "SELECT pg_size_pretty(pg_database_size('esppd'));"
```

**Expected:**
```
Normal: < 2 GB
Acceptable: < 5 GB
```

- [ ] **PASS** - Database size normal
- [ ] **WARNING** - Larger than expected
- [ ] **FAIL** - Very large (> 10 GB)

---

### Final Deployment Sign-Off (T+30 minutes)

**All validation checks passed?**

- [ ] **YES** - Proceed to operations mode
- [ ] **NO** - Return to rollback procedures

**Sign-Off:**
- [ ] Deployment Lead: ___________________ Time: _______
- [ ] DevOps Engineer: ___________________ Time: _______
- [ ] QA Tester: ___________________ Time: _______
- [ ] Project Manager: ___________________ Time: _______

---

## 🔄 ROLLBACK PROCEDURE (If Critical Issues Found)

### Decision Criteria

**Initiate rollback if:**
- [ ] Homepage returns HTTP 500
- [ ] Login fails for all users
- [ ] SPPD creation broken
- [ ] Approval workflow broken
- [ ] Database corrupted
- [ ] Performance degraded > 200%
- [ ] Security vulnerability discovered

### Rollback Steps (T+0 to T+15 minutes)

#### Step 1: Enable Maintenance Mode
```bash
php artisan down --message="Deployment issue detected. Reverting to previous version."
```

#### Step 2: Identify Rollback Point
```bash
git log --oneline -10
# Identify last stable release: v0.9.0
```

#### Step 3: Revert Code
```bash
git checkout v0.9.0
git reset --hard HEAD
```

**Verification:**
```bash
php artisan --version
# Should show previous version
```

#### Step 4: Rollback Migrations
```bash
php artisan migrate:rollback --step=1
```

**Verification:**
```bash
php artisan migrate:status
# Should match pre-deployment state
```

#### Step 5: Restore Database
```bash
./scripts/restore-db.sh backup-2026-01-29-14-30-45.dump
```

**Verification:**
```bash
psql -c "SELECT COUNT(*) FROM spds;"
# Should match backup count
```

#### Step 6: Clear & Warm Caches
```bash
php artisan optimize:clear
php artisan optimize
```

#### Step 7: Restart Services
```bash
php artisan queue:restart
supervisorctl restart all
```

#### Step 8: Disable Maintenance Mode
```bash
php artisan up
```

#### Step 9: Verify Rollback
```bash
curl -I https://esppd.uinsaizu.ac.id/
# Expected: 200 OK with previous version
```

---

### Post-Rollback Actions

- [ ] **Immediate notification to team** (within 15 min)
  - Status: Rollback complete
  - Time: [Timestamp]
  - Reason: [Specific issue]
  - Next steps: [Investigation plan]

- [ ] **Root cause analysis meeting** (within 24 hours)
  - What went wrong?
  - Why wasn't it caught in testing?
  - How to prevent recurrence?

- [ ] **Incident report** (within 48 hours)
  - Timeline of events
  - Root cause analysis
  - Preventive actions
  - Follow-up tasks

- [ ] **Users notification** (immediately after rollback)
  - Message: "Issue resolved, system restored to previous version"
  - ETA for next deployment: [Date/Time]
  - Support contact: [Contact info]

---

## ✨ SUCCESS CRITERIA

**Deployment is successful when:**

- [ ] All migration ran without errors
- [ ] Homepage loads with 200 OK response
- [ ] Login works for all user types
- [ ] Core workflows function:
  - [ ] SPPD creation
  - [ ] SPPD submission
  - [ ] Approval process
  - [ ] Document generation
  - [ ] Reporting
- [ ] No critical errors in logs
- [ ] Response times within acceptable range (< 1000ms)
- [ ] All team members notified
- [ ] System remains stable for minimum 1 hour post-deployment

---

## 🚨 EMERGENCY CONTACTS

| Role | Name | Phone | Email | Notes |
|------|------|-------|-------|-------|
| **Deployment Lead** | [Name] | [Phone] | [Email] | Primary contact |
| **DevOps Engineer** | [Name] | [Phone] | [Email] | Infrastructure issues |
| **Database Admin** | [Name] | [Phone] | [Email] | Database recovery |
| **Senior Developer** | [Name] | [Phone] | [Email] | Code issues |
| **Project Manager** | [Name] | [Phone] | [Email] | Stakeholder communication |
| **Escalation Manager** | [Name] | [Phone] | [Email] | Final authority |

---

## 📋 POST-DEPLOYMENT TASKS (Next 24 hours)

- [ ] Monitor logs continuously (watch for errors)
- [ ] Collect user feedback (send survey/email)
- [ ] Performance monitoring (CPU, memory, DB)
- [ ] Database maintenance (analyze tables, reindex)
- [ ] Security scanning (run composer audit again)
- [ ] Update release notes with actual timing
- [ ] Team retrospective (if issues encountered)
- [ ] Update deployment documentation (lessons learned)

---

## 📊 DEPLOYMENT SUMMARY TEMPLATE

```markdown
## Deployment Summary

**Date:** 29 January 2026
**Start Time:** 2:00 PM JST
**End Time:** 3:00 PM JST
**Duration:** 60 minutes
**Status:** ✅ SUCCESSFUL / ❌ ROLLED BACK

### Checklist Completion
- Pre-deployment: [ ] 100% / [ ] Partial
- Deployment: [ ] 100% / [ ] Partial
- Post-deployment: [ ] 100% / [ ] Partial
- Rollback (if needed): [ ] N/A / [ ] Completed

### Issues Encountered
- Issue 1: [Description]
  - Resolution: [How fixed]
- Issue 2: [Description]
  - Resolution: [How fixed]

### Notable Metrics
- Migration time: [X minutes]
- Response times: [X-Y ms average]
- Error rate: [0%]
- Success rate: [100%]

### Lessons Learned
1. [Learning]
2. [Learning]

### Next Deployment Date
[Date if planned]

### Sign-Off
- Deployment Lead: _________________ Date: _______
- DevOps: _________________ Date: _______
- Project Manager: _________________ Date: _______
```

---

**Document Version:** 1.0  
**Last Updated:** 29 January 2026  
**Status:** ✅ Ready for First Deployment  
**Review Cycle:** After each deployment or before major changes

---

## 📚 Related Documentation

- [Security Configuration](./SECURITY_CONFIGURATION.md)
- [Quick Reference](./QUICK_REFERENCE.md)
- [Running Guide](./RUNNING_GUIDE.md)
- [Architecture Analysis](./ARCHITECTURE_ANALYSIS.md)
