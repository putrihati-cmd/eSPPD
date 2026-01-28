#!/bin/bash
# monitor.sh - Check service status and system health

echo "=========================================="
echo "eSPPD System Monitor"
echo "$(date)"
echo "=========================================="

# Check services
echo ""
echo ">>> Service Status:"
echo "-------------------------------------------"

check_service() {
    if systemctl is-active --quiet $1; then
        echo "✅ $1: Running"
    else
        echo "❌ $1: Stopped"
    fi
}

check_service nginx
check_service php8.2-fpm
check_service mysql
check_service redis-server
check_service supervisor

# Check disk space
echo ""
echo ">>> Disk Usage:"
echo "-------------------------------------------"
df -h / | tail -1 | awk '{print "Used: " $3 " / " $2 " (" $5 ")"}'

# Check memory
echo ""
echo ">>> Memory Usage:"
echo "-------------------------------------------"
free -h | grep Mem | awk '{print "Used: " $3 " / " $2}'

# Check CPU load
echo ""
echo ">>> CPU Load:"
echo "-------------------------------------------"
uptime | awk -F'load average:' '{print "Load Average:" $2}'

# Check Laravel queue
echo ""
echo ">>> Queue Status:"
echo "-------------------------------------------"
cd /var/www/esppd
QUEUE_SIZE=$(php artisan queue:size 2>/dev/null || echo "N/A")
echo "Queue size: $QUEUE_SIZE"

# Check recent errors
echo ""
echo ">>> Recent Errors (last 10):"
echo "-------------------------------------------"
tail -10 /var/www/esppd/storage/logs/laravel.log 2>/dev/null | grep -E "ERROR|CRITICAL" | tail -5 || echo "No recent errors"

# Check Nginx access
echo ""
echo ">>> Recent Requests (last 5):"
echo "-------------------------------------------"
tail -5 /var/log/nginx/access.log 2>/dev/null || echo "No access log"

echo ""
echo "=========================================="
echo "Monitor complete"
echo "=========================================="
