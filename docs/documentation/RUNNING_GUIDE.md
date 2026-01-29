# 🚀 Panduan Menjalankan e-SPPD di Lokal

Untuk menjalankan aplikasi ini secara penuh, Anda perlu menjalankan 4 komponen berbeda.

## 📋 Prasyarat

1. **Laragon / XAMPP** (PostgreSQL & Redis harus jalan)
2. **Node.js** (untuk Vite)
3. **Python 3.10+** (untuk Document Service)

---

## ⚡ Cara Cepat (Windows)

Cukup double-click file **`start_dev.bat`** di root folder.
Script ini akan membuka 4 terminal cmd secara otomatis.

---

## 🛠 Cara Manual (Terminal)

Jika ingin menjalankan satu per satu melalui terminal (Git Bash / PowerShell):

### 1. Backend (Laravel)

```bash
php artisan serve
# Akses: http://127.0.0.1:8000
```

### 2. Frontend (Vite)

```bash
npm run dev
# Menghompilasi aset CSS/JS secara realtime
```

### 3. Queue Worker (Wajib untuk Generate PDF)

```bash
php artisan queue:work
# Memproses job background (export PDF/Excel)
```

### 4. Python Microservice

Service ini menangani pembuatan dokumen DOCX/PDF yang kompleks.

```bash
cd document-service

# Setup (pertama kali saja)
python -m venv venv
.\venv\Scripts\activate
pip install -r requirements.txt

# Run
uvicorn main:app --reload --port 8001
# Akses: http://127.0.0.1:8001/docs
```

---

## ⚠️ Troubleshooting

1. **Redis Error**: Pastikan Redis server sudah start di Laragon (`Menu > Redis > Start Redis`).
2. **Database Error**: Pastikan config DB di `.env` sudah sesuai dengan PostgreSQL lokal Anda.
3. **Python Error**: Jika `docxtpl` error, pastikan sudah `pip install` di dalam venv.
