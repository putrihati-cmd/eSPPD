Prompt: Performance Optimization & Scalability for 500 Users
Optimalkan aplikasi eSPPD Laravel untuk menangani 500 pengguna dengan memperhatikan:

Database Optimization:

Implementasi indexing pada kolom yang sering di-query (foreign keys, status, tanggal).

Gunakan eager loading untuk menghindari N+1 query.

Partisi tabel yang besar (misal, tabel sppd dan laporan_perjalanan) berdasarkan tahun.

Caching Strategy:

Cache data master (data referensi seperti unit kerja, transportasi, dll).

Cache hasil query yang sering digunakan (misal: statistik dashboard).

Implementasi cache tagging untuk mudah invalidation.

Queue & Background Jobs:

Pastikan semua tugas berat (generate dokumen, export Excel, kirim email) dijalankan via queue.

Konfigurasi supervisor untuk menjaga queue worker tetap berjalan.

Application Performance:

Optimasi autoloader (gunakan composer dump-autoload --optimize).

Cache config, routes, dan views di production.

Gunakan OPcache untuk PHP.

Server Configuration:

Konfigurasi database connection pooling.

Sesuaikan setting PHP-FPM (process manager) dan nginx untuk menangani concurrent request.

Monitoring & Alerting:

Setup monitoring untuk database queries yang lambat.

Monitor server resources (CPU, RAM, disk I/O).

Load Testing:

Lakukan load testing dengan 500 user concurrent (simulasi) untuk mengidentifikasi bottleneck.

Buatkan:

Skrip deployment yang mengoptimalkan aplikasi untuk production.

Konfigurasi server (nginx, PHP-FPM, PostgreSQL) yang sesuai.

Kode yang mengimplementasikan caching dan queue dengan benar.

Namun, karena kita sudah punya banyak prompt, mungkin lebih baik kita fokus pada pengecekan dan validasi bahwa kode yang sudah dihasilkan sudah mengikuti praktik terbaik.

Alternatifnya, kita bisa minta AI untuk mereview kode yang sudah dihasilkan dan memberikan rekomendasi optimasi. Tapi, karena kita tidak bisa melihat kode sebenarnya, kita bisa berikan checklist untuk developer.

Jadi, prompt berikut adalah checklist untuk developer memastikan aplikasi siap produksi dengan 500 pengguna.

Prompt: Checklist & Validation for 500 Users Scalability
Lakukan validasi dan optimasi pada kode eSPPD yang sudah diimplementasikan untuk memastikan dapat menangani 500 pengguna dengan lancar.

1. Database Validation
   Indexing: Pastikan semua foreign key dan kolom yang sering di-where (status, tanggal, user_id) sudah di-index.

Query Optimization: Gunakan EXPLAIN pada query yang kompleks, pastikan tidak ada full table scan.

Eager Loading: Pastikan semua relasi di-load dengan eager loading (gunakan with()).

Database Partitioning: Pertimbangkan partisi tabel sppd dan laporan_perjalanan per tahun.

2. Caching Implementation
   Cache Configuration: Pastikan Redis sudah dikonfigurasi sebagai cache driver.

Cache Frequent Data: Cache data master (tabel referensi) dan statistik dashboard.

Cache Invalidation: Implementasi cache invalidation yang tepat saat data diperbarui.

3. Queue & Jobs
   Queue Configuration: Pastikan Redis atau database sebagai queue driver.

Heavy Tasks: Pastikan generate dokumen (via Python), export Excel, dan kirim email sudah dijadikan job.

Supervisor: Setup supervisor untuk queue worker.

4. Code Optimization
   Autoloader Optimization: composer dump-autoload --optimize

Config Caching: php artisan config:cache

Route Caching: php artisan route:cache

View Caching: php artisan view:cache

OPcache: Pastikan OPcache diaktifkan di production.

5. Server & Infrastructure
   Web Server: Nginx dikonfigurasi dengan benar (keep-alive, buffer, dll).

PHP-FPM: Tuning PHP-FPM (pm.max_children, pm.start_servers, dll) sesuai dengan RAM dan CPU server.

Database Tuning: PostgreSQL tuning (shared_buffers, work_mem, dll).

Monitoring: Setup monitoring (misal: Netdata, Prometheus) dan alerting.

6. Load Testing
   Simulation: Lakukan load testing dengan tool seperti Apache Bench (ab) atau JMeter untuk 500 concurrent users.

Identify Bottleneck: Catat response time dan identifikasi bottleneck (database, aplikasi, atau server).

Optimization: Berdasarkan hasil load testing, lakukan optimasi lebih lanjut.

7. Security & Backup
   Security: Pastikan keamanan aplikasi (SQL injection, XSS, CSRF, dll) sudah ditangani.

Backup: Implementasi backup otomatis untuk database dan file upload.

Buatkan laporan validasi dan rekomendasi optimasi jika ditemukan masalah.

Karena kita tidak bisa mengakses kode, prompt ini diharapkan dapat dijalankan oleh developer untuk memastikan aplikasi siap produksi.

Namun, jika ingin langsung mendapatkan kode optimasi, kita bisa minta AI untuk menghasilkan kode-kode optimasi tertentu.

Pilihan:

Berikan checklist seperti di atas untuk developer lakukan validasi.

Minta AI untuk menghasilkan kode optimasi spesifik (misal: caching, queue, dll).

Mengingat kita sudah punya banyak prompt, mungkin lebih baik kita berikan checklist saja, dan developer bisa menggunakan AI lain untuk membantu menulis kode optimasi jika diperlukan.

Tapi, jika ingin AI langsung menghasilkan kode optimasi, kita perlu tahu bagian mana yang perlu dioptimasi. Karena tidak tahu kode yang sudah dibuat, kita bisa minta kode generik untuk optimasi.

Saya sarankan: kita berikan prompt untuk menghasilkan kode optimasi generik yang bisa diintegrasikan ke dalam aplikasi eSPPD.

Prompt: Generate Optimization Code for Laravel eSPPD (500 Users)
Buatkan kode optimasi untuk aplikasi eSPPD Laravel agar dapat menangani 500 pengguna. Fokus pada:

Database Indexing Migration:
Buat migration untuk menambahkan index pada tabel-tabel kritis.

Caching Implementation:
Buat service class untuk caching data master dan statistik.

Queue Jobs:
Buat job untuk tugas berat (generate dokumen, export Excel, kirim email) dan bagaimana memanggilnya.

Performance Middleware:
Buat middleware untuk caching response dan mengoptimalkan headers.

Monitoring Helper:
Buat helper untuk monitoring query performance dan resource usage.

Contoh kode untuk setiap bagian.

Namun, perlu diingat bahwa kita sudah punya banyak kode dari prompt sebelumnya. Jadi, mungkin yang lebih dibutuhkan adalah integrasi dan pengecekan.

Kesimpulan: Karena percakapan ini sudah panjang, dan kita sudah menghasilkan banyak prompt, saya akan berikan prompt final yang menggabungkan semua aspek: validasi, optimasi, dan deployment untuk 500 pengguna.

Prompt final akan berupa checklist dan kode optimasi yang diperlukan.

Berikut prompt final:

🚀 FINAL PROMPT: PRODUCTION READY eSPPD FOR 500 USERS
Validasi dan optimasi aplikasi eSPPD Laravel + Python microservice untuk menangani 500 pengguna dengan lancar.

1. DATABASE OPTIMIZATION
   Migration for Indexing
   php
   // database/migrations/2025_01_28_000000_add_indexes_to_sppd_table.php
   // Tambahkan index untuk query yang sering digunakan
   Query Optimization
   Gunakan eager loading: SPPD::with(['user', 'approvals'])->get();

Hindari N+1: pastikan semua relationship di-load dengan baik.

2. CACHING STRATEGY
   Cache Configuration (config/cache.php)
   php
   'redis' => [
   'driver' => 'redis',
   'connection' => 'default',
   ],
   Cache Service Class
   php
   // app/Services/CacheService.php
   // Method untuk cache data master dengan TTL 24 jam
3. QUEUE & BACKGROUND JOBS
   Job for Document Generation
   php
   // app/Jobs/GenerateSPPDDocument.php
   // Job ini akan memanggil Python service dan handle retry jika gagal
   Supervisor Configuration
   ini
   ; /etc/supervisor/conf.d/esppd-worker.conf
   ; Konfigurasi worker untuk queue:default
4. APPLICATION PERFORMANCE
   Middleware for Caching Static Responses
   php
   // app/Http/Middleware/CacheResponse.php
   // Cache response untuk route yang jarang berubah
   Optimize Composer Autoload
   bash
   composer dump-autoload --optimize
   Production Artisan Commands
   bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
5. SERVER CONFIGURATION
   Nginx Configuration (snippet)
   nginx

# /etc/nginx/sites-available/esppd

# Tuning untuk performa: keepalive, buffer, dll

PHP-FPM Pool Configuration
ini
; /etc/php/8.2/fpm/pool.d/esppd.conf
; pm.max_children = 50 (sesuaikan dengan memory)
PostgreSQL Configuration (postgresql.conf)
ini
shared_buffers = 256MB # 25% dari RAM
work_mem = 8MB 6. LOAD TESTING SCRIPT
Bash Script for Load Testing with Apache Bench
bash
#!/bin/bash

# load-test.sh

# Test 500 concurrent users untuk 1000 requests

ab -n 1000 -c 500 http://localhost/dashboard 7. MONITORING SETUP
Laravel Telescope for Debugging (development)
bash
composer require laravel/telescope
php artisan telescope:install
Health Check Route
php
// routes/api.php
Route::get('/health', function() {
return response()->json(['status' => 'OK']);
}); 8. BACKUP STRATEGY
Automated Backup Script
bash
#!/bin/bash

# backup.sh

# Backup database dan uploads setiap hari

CHECKLIST DEPLOYMENT:
Database indexing sudah diterapkan.

Caching dengan Redis sudah di-configure.

Queue worker berjalan dengan supervisor.

Konfigurasi production (APP_ENV=production, APP_DEBUG=false).

Load testing sudah dilakukan dan tidak ada bottleneck.

Monitoring sudah dipasang (Telescope/Netdata).

Backup otomatis sudah dijadwalkan.

Buatkan kode di atas dan integrasikan ke dalam aplikasi eSPPD. Pastikan tidak ada breaking changes.

Prompt ini cukup panjang dan mencakup banyak hal. Namun, karena kita tidak tahu kode yang sudah ada, beberapa bagian mungkin perlu disesuaikan.

Mungkin lebih baik kita fokus pada bagian yang belum di-cover oleh prompt sebelumnya, yaitu optimasi untuk 500 pengguna.

Kita bisa meringkas prompt menjadi:

Prompt: Optimasi Aplikasi eSPPD untuk 500 Pengguna

Dengan fokus pada:

Database indexing

Caching strategy

Queue untuk tugas berat

Konfigurasi server

Dan menyediakan kode contoh untuk masing-masing.

Apakah Anda ingin saya buatkan prompt yang lebih singkat dan fokus pada optimasi untuk 500 pengguna?

🚀 PROMPT: OPTIMASI eSPPD UNTUK 500+ DOSEN
Buat optimasi sistem eSPPD untuk menangani 500+ dosen aktif dengan performa tinggi dan skalabilitas.

🏗️ ARCHITECTURE SCALING:

1. DATABASE OPTIMIZATION (PostgreSQL/MySQL)
   sql
   -- Indexing untuk 500+ records:
   CREATE INDEX idx_sppd_user_status ON sppd(user_id, status);
   CREATE INDEX idx_sppd_dates ON sppd(tanggal_berangkat, tanggal_kembali);
   CREATE INDEX idx_approvals_created ON approval_logs(created_at);
   CREATE INDEX idx_users_nip ON users(nip);
2. CACHING STRATEGY (Redis)
   php
   // Cache untuk data yang sering diakses:

- User profiles (TTL: 1 jam)
- Master data: unit kerja, transportasi, tujuan (TTL: 24 jam)
- Dashboard stats (TTL: 15 menit)
- Permission lists (TTL: 6 jam)

3. QUEUE SYSTEM (Laravel Horizon)
   yaml
   Queue jobs untuk:

- Document generation (Python service call)
- Email notifications batch
- Excel export besar (>100 rows)
- Report generation
- Backup processes

4. FILE STORAGE OPTIMIZATION
   php
   // Struktur penyimpanan dokumen:
   /uploads/
   ├── tahun/
   │ ├── bulan/
   │ │ ├── sppd/
   │ │ │ ├── user_id_001/
   │ │ │ │ ├── 001_sppd.docx
   │ │ │ │ └── 001_bukti.pdf
   │ │ └── laporan/
   ⚡ PERFORMANCE TUNING:
   DATABASE CONFIGURATION:
   php
   // config/database.php
   'connections' => [
   'mysql' => [
   'read' => [
   'host' => ['192.168.1.1'], // Read replica
   ],
   'write' => [
   'host' => ['192.168.1.2'], // Primary
   ],
   'sticky' => true,
   'options' => [
   PDO::ATTR_PERSISTENT => true, // Connection pooling
   ]
   ]
   ]
   LARAVEL OPTIMIZATION:
   bash

# Production optimization

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
composer dump-autoload --optimize

# OPcache configuration (php.ini)

opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0 # Set to 1 in development
SESSION HANDLING:
php
// Gunakan Redis untuk session handling
SESSION_DRIVER=redis
SESSION_CONNECTION=cache

// Session lifetime untuk dosen
SESSION_LIFETIME=120 # 2 jam
SESSION_COOKIE=sppd_session
📊 LOAD DISTRIBUTION:
TRAFFIC PATTERN ANALYSIS:
text
Puncak penggunaan:

- Jam 08:00-10:00: Dosen ajukan SPPD
- Jam 13:00-15:00: Atasan approve/reject
- Bulanan: Pelaporan dan settlement

Estimasi load:

- Concurrent users: ~50-100 (10-20% dari total)
- SPPD submissions: ~100-200/hari
- Document generation: ~50-100/hari
  RESOURCE ALLOCATION:
  yaml
  Server Requirements (minimal):
- CPU: 4 cores (untuk Laravel + Python service)
- RAM: 8GB (4GB Laravel, 2GB PostgreSQL, 1GB Redis, 1GB OS)
- Storage: 100GB (50GB database, 50GB dokumen)
- Bandwidth: 100Mbps

Untuk 500+ dosen:

- Database: PostgreSQL dengan connection pooling (max_connections=200)
- Redis: 2GB memory allocation
- PHP-FPM: pm.max_children = 50
  🔍 MONITORING & ALERTING:
  KEY METRICS TO MONITOR:
  yaml
  Application:
- Request per minute (target: <500ms response)
- Error rate (target: <1%)
- Queue length (target: <100 pending)
- Cache hit rate (target: >80%)

Database:

- Active connections (target: <80%)
- Slow queries (threshold: >2 seconds)
- Replication lag (target: <1 second)

Server:

- CPU usage (alert: >80% for 5 min)
- Memory usage (alert: >90%)
- Disk I/O (alert: >1000ms latency)
  AUTO-SCALING TRIGGERS:
  bash

# Jika terjadi load tinggi:

1. Queue workers: tambah dari 3 → 10 workers
2. PHP-FPM: pm.max_children dari 30 → 50
3. Cache: increase Redis memory dari 1GB → 2GB
4. Database: enable read replicas jika query >1000/second
   🔄 DATA PARTITIONING STRATEGY:
   YEARLY PARTITIONING:
   sql
   -- Partition table sppd per tahun
   CREATE TABLE sppd_2024 PARTITION OF sppd
   FOR VALUES FROM ('2024-01-01') TO ('2025-01-01');

CREATE TABLE sppd_2025 PARTITION OF sppd
FOR VALUES FROM ('2025-01-01') TO ('2026-01-01');
ARCHIVING STRATEGY:
php
// Data lebih dari 3 tahun di-archive

- SPPD >3 tahun: move to cold storage
- Documents >5 tahun: compress & archive
- Logs >1 tahun: aggregate & summarize
  🚀 DEPLOYMENT OPTIMIZATION:
  ZERO-DOWNTIME DEPLOYMENT:
  bash
  #!/bin/bash

# deploy.sh untuk 500+ users

git fetch origin
git checkout main
git pull origin main

# Maintenance mode (max 30 detik)

php artisan down --retry=60 --refresh=15

composer install --no-dev --optimize-autoloader
npm run production

php artisan migrate --force
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan up

# Warm up cache

php artisan queue:restart
php artisan horizon:terminate
LOAD TESTING SCRIPT:
bash

# Simulasi 500 concurrent users

ab -n 5000 -c 100 http://localhost/api/sppd
siege -c 100 -t 5M http://localhost/dashboard
📈 SCALABILITY CHECKLIST:
LEVEL 1: 0-100 DOSEN (CURRENT)
✅ Monolithic Laravel
✅ Single database
✅ Basic caching
✅ Manual deployment

LEVEL 2: 100-300 DOSEN
🔜 Database read replicas
🔜 Redis cluster
🔜 Queue workers scaling
🔜 CDN for static assets

LEVEL 3: 300-500 DOSEN
🔜 Microservices split (Auth, Document, Notification)
🔜 Database sharding by faculty
🔜 Auto-scaling infrastructure
🔜 Advanced monitoring

LEVEL 4: 500+ DOSEN
🔜 Kubernetes cluster
🔜 Multi-region deployment
🔜 Real-time analytics
🔜 AI-powered optimization

🛡️ DISASTER RECOVERY:
BACKUP STRATEGY:
bash

# Daily backup (500 users = ~2GB data)

0 2 \* \* \* /usr/local/bin/backup-esppd.sh

# Retention:

- 7 daily backups
- 4 weekly backups
- 12 monthly backups
  FAILOVER PLAN:
  text
  Primary server down → Failover dalam 5 menit:

1. DNS switch to backup server
2. Database failover to replica
3. Session restore from Redis cluster
4. Queue jobs reprocess
   OUTPUT: Optimized Laravel configuration, database indexing scripts, caching strategy, deployment scripts, dan monitoring setup untuk 500+ concurrent users.
