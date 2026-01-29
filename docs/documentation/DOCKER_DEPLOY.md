# Docker Deployment Guide - e-SPPD

## Prerequisites

- Docker & Docker Compose v2.0+
- Git
- 4GB+ RAM available
- 10GB+ disk space

## Quick Start

### Windows User (Easy Start)

Simply run the batch script provided:

```cmd
start_docker.bat
```

This script will verify Docker status, copy the environment file, and automatically launch the stack.

### Manual Setup (Linux/Mac/Manual)

### 1. Clone & Configure

```bash
git clone <repository-url>
cd eSPPD

# Copy environment file
cp .env.docker .env

# Generate secure APP_KEY
docker-compose run --rm app php artisan key:generate --show
# Or generate and set in .env
docker-compose run --rm app php artisan key:generate
```

### 2. Configure Production Environment

Edit `.env` with production values:

```bash
# Database - Use strong passwords!
POSTGRES_PASSWORD=your_secure_random_password
REDIS_PASSWORD=your_secure_random_password

# Application
APP_ENV=production
APP_DEBUG=false

# Mail configuration (SMTP)
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_mail_password
```

### 3. Build & Start

```bash
# Build all containers
docker-compose build --no-cache

# Start services (detached mode)
docker-compose up -d

# Check service status
docker-compose ps
```

### 4. Initialize Database

```bash
# Run migrations
docker-compose exec app php artisan migrate --force

# Seed initial data (if needed)
docker-compose exec app php artisan db:seed

# Clear caches
docker-compose exec app php artisan optimize:clear
docker-compose exec app php artisan optimize
```

### 5. Access Application

- **Web App**: http://localhost:8000
- **Health Check**: http://localhost:8000/health
- **API Base**: http://localhost:8000/api

## Production Deployment

### SSL/HTTPS Configuration

1. Generate SSL certificates (or use Let's Encrypt):

```bash
# Create ssl directory
mkdir -p docker/nginx/ssl

# Generate self-signed certificate (for testing)
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout docker/nginx/ssl/key.pem \
    -out docker/nginx/ssl/cert.pem \
    -subj "/C=ID/ST=Jember/L=Jember/O=UIN Saizu/CN=esppd.yourdomain.com"
```

2. Uncomment SSL server block in `docker/nginx/default.conf`

3. Update `docker-compose.yml` to map port 443 if needed

### Using Docker Secrets (Recommended for Production)

Create `.env.production` with:

```bash
POSTGRES_PASSWORD=your_secure_password
REDIS_PASSWORD=your_secure_password
```

And pass via environment:

```bash
docker-compose --env-file .env.production up -d
```

## Useful Commands

```bash
# View logs
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f postgres

# Enter container shell
docker-compose exec app bash
docker-compose exec postgres psql -U esppd_user -d esppd

# Restart services
docker-compose restart

# Restart with rebuild
docker-compose up -d --build

# Stop all services
docker-compose down

# Stop and remove volumes (CAUTION: deletes all data)
docker-compose down -v

# Scale queue workers
docker-compose up -d --scale queue=3

# Check resource usage
docker stats
```

## Health Checks

All services include health checks:

```bash
# Check all service health
docker-compose ps

# Manual health check
curl http://localhost:8000/health
docker-compose exec postgres pg_isready -U esppd_user
docker-compose exec redis redis-cli ping
```

## Monitoring & Maintenance

### View Application Logs

```bash
# Laravel logs
docker-compose exec app tail -f storage/logs/laravel.log

# Nginx logs
docker-compose exec nginx tail -f /var/log/nginx/access.log
docker-compose exec nginx tail -f /var/log/nginx/error.log
```

### Database Backup

```bash
# Backup database
docker-compose exec postgres pg_dump -U esppd_user esppd > backup_$(date +%Y%m%d).sql

# Restore database
docker-compose exec -T postgres psql -U esppd_user -d esppd < backup_file.sql
```

### Clear Caches

```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Scheduler (Cron)

The scheduler container runs every minute:

```bash
# Verify scheduler is running
docker-compose exec scheduler crontab -l
```

## Troubleshooting

### Containers not starting

```bash
# Check logs for errors
docker-compose logs app
docker-compose logs postgres
```

### Database connection refused

```bash
# Wait for postgres to be ready
docker-compose exec postgres pg_isready -U esppd_user

# Restart app after db is ready
docker-compose restart app
```

### Port already in use

```bash
# Find process using port
netstat -tlnp | grep 8000
# or
lsof -i :8000

# Stop conflicting service or change port in docker-compose.yml
```

## Performance Optimization

### For production, consider:

1. **Increase PHP-FPM workers** in Dockerfile:

    ```dockerfile
    ENV PHP_FPM_PM_MODE=dynamic
    ENV PHP_FPM_PM_MAX_CHILDREN=20
    ENV PHP_FPM_PM_START_SERVERS=2
    ENV PHP_FPM_PM_MIN_SPARE_SERVERS=2
    ENV PHP_FPM_PM_MAX_SPARE_SERVERS=5
    ```

2. **Configure OPcache** in php.ini

3. **Use Redis for sessions and cache**

4. **Enable gzip compression** (already configured in nginx)

5. **Use a reverse proxy** like Traefik or HAProxy for SSL termination

## Security Checklist

- [ ] Change all default passwords
- [ ] Enable SSL/HTTPS
- [ ] Configure firewall rules
- [ ] Enable audit logging
- [ ] Set up regular backups
- [ ] Configure log rotation
- [ ] Monitor system resources
- [ ] Keep dependencies updated
