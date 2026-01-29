DO
$do$
BEGIN
   IF NOT EXISTS (
      SELECT FROM pg_catalog.pg_roles
      WHERE  rolname = 'esppd_user') THEN

      CREATE ROLE esppd_user LOGIN PASSWORD 'Esppd_Secure_2026!';
   END IF;
   IF NOT EXISTS (
      SELECT FROM pg_catalog.pg_roles
      WHERE  rolname = 'mi_user') THEN

      CREATE ROLE mi_user LOGIN PASSWORD 'Mi_Secure_2026!';
   END IF;
END
$do$;

SELECT 'CREATE DATABASE esppd'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'esppd')\gexec

SELECT 'CREATE DATABASE mi_miftahul_ulum'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'mi_miftahul_ulum')\gexec

ALTER DATABASE esppd OWNER TO esppd_user;
ALTER DATABASE mi_miftahul_ulum OWNER TO mi_user;
