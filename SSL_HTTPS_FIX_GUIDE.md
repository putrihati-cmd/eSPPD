# 🔐 SSL/HTTPS Setup Guide - Production Fix

**Date:** January 31, 2026  
**Status:** 🔧 SSL Configuration Issue Found & Solution Provided  
**Current Issue:** ERR_SSL_PROTOCOL_ERROR on port 8443

---

## 📊 Current State

| Component | Status | Details |
|-----------|--------|---------|
| **Nginx Process** | ✅ Running | Master + 4 workers active |
| **Port 8083 (HTTP)** | ✅ Working | Returns 302 redirect (OK) |
| **Port 8443 (HTTPS)** | ❌ Failed | ERR_SSL_PROTOCOL_ERROR - Port not listening for HTTPS |
| **SSL Certificates** | ✅ Generated | esppd.crt & esppd.key created in /tmp/ |
| **App Logic** | ✅ OK | Application responding correctly via HTTP |

---

## 🔍 Root Cause

The current Nginx configuration at `/etc/nginx/sites-enabled/esppd` only listens on **port 8083** without any SSL/HTTPS support:

```nginx
# Current Config (HTTP only)
server {
    listen 8083;
    server_name sppd.infiatin.cloud;
    # ... no SSL directives
}
```

When accessing `https://192.168.1.27:8083`, the browser tries to establish HTTPS on a port that's only configured for HTTP, causing the SSL protocol error.

---

## ✅ Solution Provided

### Step 1: SSL Certificates (✅ DONE)

Generated self-signed certificates on production server:
- **Location:** `/tmp/nginx_setup/`
- **Files:**
  - `esppd.crt` - Certificate file (1.3KB)
  - `esppd.key` - Private key (1.7KB)

**Need to move to permanent location:**
```bash
sudo cp /tmp/nginx_setup/esppd.crt /etc/nginx/esppd.crt
sudo cp /tmp/nginx_setup/esppd.key /etc/nginx/esppd.key
sudo chmod 644 /etc/nginx/esppd.crt
sudo chmod 600 /etc/nginx/esppd.key
```

### Step 2: Update Nginx Configuration

**New config file ready at:** `c:\laragon\www\eSPPD\esppd-nginx-https.conf`

**Key changes:**
```nginx
# Redirect HTTP to HTTPS
server {
    listen 8083;
    return 301 https://$host$request_uri;
}

# HTTPS Server
server {
    listen 8443 ssl http2;
    ssl_certificate /etc/nginx/esppd.crt;
    ssl_certificate_key /etc/nginx/esppd.key;
    
    # Enhanced security headers
    add_header Strict-Transport-Security "max-age=31536000" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    # ... more configs
}
```

**To apply:**
```bash
sudo cp esppd-nginx-https.conf /etc/nginx/sites-enabled/esppd
sudo nginx -t  # Test syntax
sudo systemctl reload nginx  # Apply changes
```

### Step 3: Update Your URLs

After HTTPS is enabled:
- **Old:** `http://192.168.1.27:8083` → **New:** `https://192.168.1.27:8443`
- HTTP on port 8083 will auto-redirect to HTTPS on 8443

---

## 🚀 Implementation Steps (For System Admin)

### Option A: Manual Setup (Recommended for first-time)

```bash
# 1. Backup current config
sudo cp /etc/nginx/sites-enabled/esppd /etc/nginx/sites-enabled/esppd.backup

# 2. Install SSL certificates
sudo mkdir -p /etc/nginx
sudo cp /tmp/nginx_setup/esppd.crt /etc/nginx/esppd.crt
sudo cp /tmp/nginx_setup/esppd.key /etc/nginx/esppd.key
sudo chmod 644 /etc/nginx/esppd.crt
sudo chmod 600 /etc/nginx/esppd.key

# 3. Install new nginx config
scp esppd-nginx-https.conf tholib_server@192.168.1.27:/tmp/
sudo cp /tmp/esppd-nginx-https.conf /etc/nginx/sites-enabled/esppd

# 4. Test nginx syntax
sudo nginx -t
# Output should be: nginx: configuration file test is successful

# 5. Reload nginx
sudo systemctl reload nginx

# 6. Verify both ports are listening
sudo ss -tlnp | grep nginx
# Should show: 
#  0.0.0.0:8083  LISTEN
#  0.0.0.0:8443  LISTEN
```

### Option B: Automated Script (For experienced admins)

```bash
#!/bin/bash
set -e

echo "=== eSPPD HTTPS Setup ==="

# 1. Setup SSL certs
sudo mkdir -p /etc/nginx
sudo cp /tmp/nginx_setup/esppd.* /etc/nginx/
sudo chmod 644 /etc/nginx/esppd.crt
sudo chmod 600 /etc/nginx/esppd.key

# 2. Backup and replace config
sudo cp /etc/nginx/sites-enabled/esppd /tmp/esppd.backup.$(date +%s)
sudo cp /tmp/esppd-nginx-https.conf /etc/nginx/sites-enabled/esppd

# 3. Test and reload
sudo nginx -t && sudo systemctl reload nginx
echo "✅ HTTPS enabled successfully!"
```

---

## 🔗 Connection URLs After Setup

| Protocol | URL | Port | Notes |
|----------|-----|------|-------|
| **HTTP** | `http://192.168.1.27:8083` | 8083 | Redirects to HTTPS |
| **HTTPS** | `https://192.168.1.27:8443` | 8443 | **Preferred** |
| **Domain** | `https://sppd.infiatin.cloud` | 443 | If using Cloudflare |

---

## ⚠️ Important Notes

### About Self-Signed Certificate
- ✅ **Browser warning:** This is normal for self-signed certs
- ✅ **How to proceed:** Click "Advanced" → "Proceed anyway"
- ⚠️ **Production note:** Consider getting a proper certificate later
- 📝 **Certificate info:**
  - Issued to: 192.168.1.27
  - Valid for: 365 days (Jan 31, 2026 - Jan 31, 2027)
  - Algorithm: RSA 2048-bit

### Port Changes
- 🔄 Old HTTPS attempts on 8083 will now redirect
- ✅ New HTTPS port: 8443
- ✅ Users should bookmark: `https://192.168.1.27:8443`

### Security Headers Added
```
- Strict-Transport-Security: Force HTTPS for 1 year
- X-Frame-Options: Prevent clickjacking
- X-Content-Type-Options: Prevent MIME sniffing
- X-XSS-Protection: XSS protection enabled
- Referrer-Policy: Strict referrer handling
- Permissions-Policy: Disable unnecessary APIs
```

---

## 🧪 Verification Steps

After applying the configuration, verify:

```bash
# 1. Check nginx is running
sudo systemctl status nginx

# 2. Verify both ports are listening
sudo ss -tlnp | grep nginx

# 3. Test HTTPS connection
curl -k https://localhost:8443
# Should return HTML response (not SSL error)

# 4. Test HTTP redirect
curl -i http://localhost:8083
# Should return 301 redirect to HTTPS

# 5. Check certificate
openssl x509 -in /etc/nginx/esppd.crt -text -noout
```

---

## 📋 Troubleshooting

### Issue: nginx: configuration file test fails
**Solution:**
```bash
# Check what's wrong
sudo nginx -t -c /etc/nginx/nginx.conf

# Review the error and fix syntax
sudo nano /etc/nginx/sites-enabled/esppd

# Test again
sudo nginx -t
```

### Issue: Permission denied when copying files
**Solution:**
```bash
# Ensure you have sudo access
sudo -l

# If needed, use sudo with copy
sudo cp /tmp/esppd.crt /etc/nginx/
```

### Issue: Port 8443 already in use
**Solution:**
```bash
# Find what's using the port
sudo lsof -i :8443

# Kill the process if not needed
sudo kill -9 <PID>

# Or change the port in nginx config and update URLs
```

### Issue: Certificate verification errors
**Solution:**
```bash
# Regenerate if certificate was corrupt
cd /tmp
rm esppd.* 2>/dev/null || true
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout esppd.key -out esppd.crt \
  -subj '/C=ID/ST=Jawa_Tengah/L=Purwokerto/O=UIN_SAIZU/CN=192.168.1.27'

# Then copy to nginx directory
sudo cp esppd.* /etc/nginx/
sudo chmod 644 /etc/nginx/esppd.crt
sudo chmod 600 /etc/nginx/esppd.key
```

---

## 📊 Current Configuration Status

### Nginx Configuration Comparison

**BEFORE (Current - HTTP only):**
```nginx
server {
    listen 8083;
    # ... HTTP only, no HTTPS
}
```

**AFTER (Proposed - HTTP + HTTPS):**
```nginx
# HTTP Redirect
server {
    listen 8083;
    return 301 https://$host$request_uri;
}

# HTTPS Main
server {
    listen 8443 ssl http2;
    ssl_certificate /etc/nginx/esppd.crt;
    ssl_certificate_key /etc/nginx/esppd.key;
    # ... full configuration
}
```

---

## ✨ Additional Improvements

The new configuration also includes:

✅ **HTTP/2 Support** - Faster page loads  
✅ **Security Headers** - HSTS, XSS protection, CSP  
✅ **Static File Caching** - 1 year cache for assets  
✅ **SSL Optimizations** - Strong ciphers, session caching  
✅ **Error Logging** - Better debugging capability  
✅ **PHP-FPM Hardening** - HTTPS environment variables  

---

## 📞 Support

If you encounter issues during setup:

1. **Check logs:**
   ```bash
   tail -50 /var/log/nginx/error.log
   ```

2. **Test manually:**
   ```bash
   sudo nginx -t -c /etc/nginx/nginx.conf
   ```

3. **Restart service:**
   ```bash
   sudo systemctl restart nginx
   ```

---

## 📝 Files Involved

| File | Location | Purpose |
|------|----------|---------|
| `esppd-nginx-https.conf` | Repository root | New Nginx config (ready) |
| `esppd.crt` | `/tmp/nginx_setup/` → `/etc/nginx/` | SSL Certificate |
| `esppd.key` | `/tmp/nginx_setup/` → `/etc/nginx/` | SSL Private Key |
| Current config | `/etc/nginx/sites-enabled/esppd` | To be replaced |
| Backup | `/etc/nginx/sites-enabled/esppd.backup` | Safety backup |

---

## 🎯 Next Steps

**For IT/System Admin:**
1. SSH into production server
2. Execute the manual or automated setup steps
3. Verify with test commands
4. Communicate new HTTPS URL to users

**For Users:**
1. Update bookmarks to `https://192.168.1.27:8443`
2. Accept self-signed certificate warning
3. Login as before (no application changes)

---

## 📅 Timeline

- ✅ **Jan 31, 2026 16:56** - SSL certificates generated
- ⏳ **Pending** - Admin applies configuration
- ⏳ **Pending** - Users updated to new HTTPS URL

---

**Last Updated:** January 31, 2026 | **Status:** Ready for Implementation  
**Config File:** `esppd-nginx-https.conf` (Ready to deploy)

