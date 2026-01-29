CREATE DATABASE IF NOT EXISTS esppd;
CREATE USER IF NOT EXISTS 'esppd_user'@'localhost' IDENTIFIED BY 'Esppd_Secure_2026!';
GRANT ALL PRIVILEGES ON esppd.* TO 'esppd_user'@'localhost';
FLUSH PRIVILEGES;

-- For Cloudflare Tunnel (Optional: if we want to store tunnel state in db? No, usually not needed)
