-- Create Database
CREATE DATABASE esppd;

-- Create User (Role)
CREATE USER esppd_user WITH PASSWORD 'Esppd_Secure_2026!';

-- Grant Privileges
ALTER DATABASE esppd OWNER TO esppd_user;
GRANT ALL PRIVILEGES ON DATABASE esppd TO esppd_user;

-- Connect to database to grant schema privileges (Optional, owner usually has access)
\c esppd
GRANT ALL ON SCHEMA public TO esppd_user;
