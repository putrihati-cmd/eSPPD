#!/usr/bin/env bash
# automated-deploy.sh - Automated SSH Deployment untuk eSPPD
# Gunakan: bash automated-deploy.sh

set -e

# Configuration
SERVER_IP="192.168.1.27"
SERVER_USER="tholibserver"
SERVER_PASSWORD="065820Aaaa"
GITHUB_REPO="https://github.com/putrihati-cmd/eSPPD.git"
APP_PATH="/var/www/esppd"

echo "================================"
echo "ESPPD Automated SSH Deployment"
echo "================================"
echo ""
echo "Target Server: $SERVER_IP"
echo "User: $SERVER_USER"
echo "App Path: $APP_PATH"
echo ""
echo "Press Enter to continue or Ctrl+C to cancel..."
read -r

# Check if expect is available (for automated password input)
if ! command -v expect &> /dev/null; then
    echo "⚠ 'expect' command not found. Installing..."

    if [[ "$OSTYPE" == "linux-gnu"* ]]; then
        sudo apt-get update && sudo apt-get install -y expect
    elif [[ "$OSTYPE" == "darwin"* ]]; then
        brew install expect
    else
        echo "❌ Cannot auto-install expect on this system."
        echo "Please install 'expect' manually or use manual SSH login instead."
        exit 1
    fi
fi

# Create expect script for deployment
cat > /tmp/esppd_deploy.exp << 'EXPECT_SCRIPT'
#!/usr/bin/env expect

set SERVER_IP [lindex $argv 0]
set SERVER_USER [lindex $argv 1]
set SERVER_PASSWORD [lindex $argv 2]
set timeout 30

# Start SSH connection
spawn ssh -o StrictHostKeyChecking=no $SERVER_USER@$SERVER_IP

# Handle password prompt
expect {
    "password:" {
        send "$SERVER_PASSWORD\r"
        expect "$ "
    }
    "$ " {
        # Already logged in
    }
}

# Send deployment commands
send "cd /tmp && curl -s https://raw.githubusercontent.com/putrihati-cmd/eSPPD/main/deploy-production-auto.sh | bash\r"
expect {
    "DEPLOYMENT COMPLETE" {
        puts "✓ Deployment successful"
    }
    timeout {
        puts "⚠ Deployment timed out (might still be running)"
    }
    eof {
        puts "✓ Connection closed"
    }
}

send "exit\r"
expect eof
EXPECT_SCRIPT

# Make script executable
chmod +x /tmp/esppd_deploy.exp

# Run deployment
echo "Starting automated deployment via expect..."
expect /tmp/esppd_deploy.exp "$SERVER_IP" "$SERVER_USER" "$SERVER_PASSWORD"

echo ""
echo "================================"
echo "✓ Deployment initiated!"
echo "================================"
echo ""
echo "Check deployment progress:"
echo "  ssh $SERVER_USER@$SERVER_IP"
echo "  tail -f $APP_PATH/storage/logs/laravel.log"
echo ""
