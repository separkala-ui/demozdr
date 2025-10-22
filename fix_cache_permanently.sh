#!/bin/bash

# Permanent cache fix script
echo "Fixing cache permissions permanently..."

# Set proper ownership
chown -R www-data:www-data /var/www/zdr/storage
chown -R www-data:www-data /var/www/zdr/bootstrap/cache

# Set proper permissions
chmod -R 775 /var/www/zdr/storage
chmod -R 775 /var/www/zdr/bootstrap/cache

# Ensure cache directories exist
mkdir -p /var/www/zdr/storage/framework/cache/data
mkdir -p /var/www/zdr/storage/framework/sessions
mkdir -p /var/www/zdr/storage/framework/views
mkdir -p /var/www/zdr/bootstrap/cache

# Set specific permissions for cache data
chmod -R 777 /var/www/zdr/storage/framework/cache/data
chown -R www-data:www-data /var/www/zdr/storage/framework/cache/data

# Clear all caches
cd /var/www/zdr
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Cache permissions fixed permanently!"
echo "Setting up systemd service for automatic cache management..."

# Create systemd service for cache management
cat > /etc/systemd/system/zdr-cache-fix.service << 'EOF'
[Unit]
Description=ZDR Cache Permission Fix
After=network.target

[Service]
Type=oneshot
ExecStart=/bin/bash /var/www/zdr/fix_cache_permanently.sh
User=root

[Install]
WantedBy=multi-user.target
EOF

# Create timer for periodic cache fix
cat > /etc/systemd/system/zdr-cache-fix.timer << 'EOF'
[Unit]
Description=ZDR Cache Permission Fix Timer
Requires=zdr-cache-fix.service

[Timer]
OnBootSec=5min
OnUnitActiveSec=1h
Persistent=true

[Install]
WantedBy=timers.target
EOF

# Enable and start the timer
systemctl daemon-reload
systemctl enable zdr-cache-fix.timer
systemctl start zdr-cache-fix.timer

echo "Systemd service and timer created successfully!"
echo "Cache will be automatically fixed every hour."


