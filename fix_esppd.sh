#!/bin/bash
# 🚀 Script perbaikan final esppd.infiatin.cloud

echo "[1/3] Menyiapkan struktur folder..."
if [ -d "/home/tholib_server/esppd_production" ]; then
    echo "Ditemukan esppd_production di home, memindahkan ke /var/www/esppd..."
    sudo rm -rf /var/www/esppd_old
    [ -d "/var/www/esppd" ] && sudo mv /var/www/esppd /var/www/esppd_old
    sudo mv /home/tholib_server/esppd_production /var/www/esppd
else
    echo "esppd_production tidak ditemukan di home, berasumsi sudah berada di /var/www/esppd."
fi

echo "[2/3] Mengatur perizinan & pemilik..."
if [ -d "/var/www/esppd" ]; then
    sudo chown -R www-data:www-data /var/www/esppd
    sudo chmod -R 775 /var/www/esppd/storage /var/www/esppd/bootstrap/cache
else
    echo "GAGAL: /var/www/esppd tidak ditemukan!"
    exit 1
fi

echo "[3/3] Memperbaiki konfigurasi Nginx..."
if [ -f "/home/tholib_server/esppd_nginx.conf" ]; then
    sudo cp /home/tholib_server/esppd_nginx.conf /etc/nginx/sites-available/esppd
    sudo ln -sf /etc/nginx/sites-available/esppd /etc/nginx/sites-enabled/esppd
    sudo nginx -t && sudo systemctl restart nginx
    sudo systemctl restart php8.5-fpm
    echo "✅ Berhasil! Sila cek https://esppd.infiatin.cloud"
else
    echo "GAGAL: /home/tholib_server/esppd_nginx.conf tidak ditemukan!"
    exit 1
fi
