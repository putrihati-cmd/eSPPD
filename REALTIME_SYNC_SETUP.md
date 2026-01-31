# 🔄 Real-Time Git Sync Setup (VS Code)

## Overview
VS Code workspace ini sudah dikonfigurasi dengan real-time synchronization dari GitHub. Setiap perubahan di repository akan otomatis ter-sync ke workspace lokal Anda.

## ✅ Extensions Terpasang

### 1. **Git Auto Pull** (tapasthakkar.auto-git-pull)
- Automatically fetches dan pulls changes dari GitHub
- Interval: 5 detik
- Auto-stash konflik otomatis
- Notifikasi real-time

## 🚀 Fitur Aktif

### Auto Sync Features:
```
✅ Auto-fetch setiap 5 detik
✅ Auto-pull dari branch main
✅ Notifikasi perubahan file
✅ Auto-stash untuk avoid conflicts
✅ Files auto-save on focus change
```

### Keybindings:
| Shortcut | Action |
|----------|--------|
| `Ctrl+Shift+G` | Manual Git Pull |
| `Ctrl+Shift+B` | Check Git Status |

## 📋 Tasks Available

Buka Command Palette (`Ctrl+Shift+P`) dan cari:

### 1. **🔄 Git Auto-Pull (Real-time Sync)**
   - Auto-pull setiap 5 detik (background task)
   - Jalan otomatis saat buka workspace
   - Status ditampilkan di terminal

### 2. **📡 Check Git Status**
   - Lihat status file yang berubah
   - Quick check before commit

### 3. **⬇️ Manual Git Pull**
   - Force pull dari GitHub
   - Gunakan jika ada konflik

## ⚙️ Konfigurasi Detail

File: `.vscode/settings.json`

```json
{
  "autoGitPull.interval": 5000,        // 5 detik
  "autoGitPull.showNotification": true, // Tampilkan notifikasi
  "autoGitPull.pullBeforePush": true,   // Pull sebelum push
  "autoGitPull.autoStash": true,        // Auto-stash conflicts
  "files.autoSave": "onFocusChange"     // Auto-save saat window hilang focus
}
```

## 🔧 Troubleshooting

### Jika sync tidak jalan:
1. Buka Command Palette (`Ctrl+Shift+P`)
2. Cari "Tasks: Run Task"
3. Pilih "🔄 Git Auto-Pull (Real-time Sync)"

### Jika ada conflict:
1. Extension akan auto-stash perubahan lokal
2. Manual merge bisa pakai `⬇️ Manual Git Pull`
3. Atau edit langsung di Source Control panel

### Cek status real-time:
- Lihat di Git panel (Ctrl+Shift+G dalam VS Code)
- Atau jalankan `📡 Check Git Status` task

## 📊 Monitoring

### Status Bar:
- Warna biru = syncing
- Warna hijau = up-to-date
- Warna kuning = ada perubahan pending
- Warna merah = ada conflict

### Terminal Output:
- Buka Terminal (`Ctrl+~`)
- Lihat "Git Auto-Pull" tab untuk activity log

## 🛠️ Production Deployment Setup

### Di production server (192.168.1.27):
```bash
# Auto-pull di production setiap 60 detik
cd /var/www/esppd
(crontab -l 2>/dev/null; echo "* * * * * cd /var/www/esppd && git pull origin main --quiet") | crontab -
```

## 📝 Best Practices

1. **Commit changes frequently** untuk avoid large diffs
2. **Pull manually** (Ctrl+Shift+G) sebelum besar push
3. **Check status** (📡 task) sebelum membuat perubahan
4. **Monitor terminal** untuk sync activity

## 🚨 Important Notes

- Auto-pull TIDAK akan membuat commit
- Perubahan lokal yang uncomitted akan di-stash otomatis
- Push tetap manual (gunakan Source Control panel atau terminal)
- Production server juga sync dengan interval yang sama

## 📞 Support

Jika ada issue dengan sync:
1. Jalankan `git status` di terminal
2. Check `.vscode/settings.json` configuration
3. Restart VS Code
4. Clear VS Code cache jika perlu

---

**Status**: ✅ Real-time sync ACTIVE  
**Last Config Update**: 2026-01-31  
**Environment**: Development + Production Sync
