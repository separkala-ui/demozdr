# 🚀 راهنمای استقرار ZDR در محیط Production

> این راهنما مراحل استقرار امن و بهینه در محیط تولید را شرح می‌دهد

---

## 📋 فهرست

1. [آماده‌سازی سرور](#آمادهسازی-سرور)
2. [نصب و پیکربندی](#نصب-و-پیکربندی)
3. [تنظیمات امنیتی](#تنظیمات-امنیتی)
4. [بهینه‌سازی عملکرد](#بهینهسازی-عملکرد)
5. [مانیتورینگ](#مانیتورینگ)
6. [Backup](#backup)
7. [CI/CD](#cicd)

---

## 🖥 آماده‌سازی سرور

### سخت‌افزار توصیه‌شده

| موارد | حداقل | توصیه‌شده | Enterprise |
|-------|-------|------------|------------|
| CPU | 2 Core | 4 Core | 8+ Core |
| RAM | 2 GB | 4 GB | 8+ GB |
| Storage | 20 GB SSD | 50 GB SSD | 100+ GB NVMe |
| Bandwidth | 1 TB/mo | 3 TB/mo | 10+ TB/mo |

### نصب نیازمندی‌های اولیه (Ubuntu 22.04/24.04)

```bash
# بروزرسانی سیستم
sudo apt update && sudo apt upgrade -y

# نصب ابزارهای پایه
sudo apt install -y software-properties-common curl wget git unzip

# نصب PHP 8.4
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.4 php8.4-fpm php8.4-cli php8.4-common \
    php8.4-mysql php8.4-mbstring php8.4-xml php8.4-bcmath \
    php8.4-gd php8.4-curl php8.4-zip php8.4-intl \
    php8.4-redis php8.4-opcache

# نصب Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# نصب Node.js 20 LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# نصب MariaDB
sudo apt install -y mariadb-server mariadb-client
sudo mysql_secure_installation

# نصب Redis
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server

# نصب Nginx
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx

# نصب Supervisor (برای Queue Workers)
sudo apt install -y supervisor
sudo systemctl enable supervisor
sudo systemctl start supervisor

# نصب Certbot (برای SSL)
sudo apt install -y certbot python3-certbot-nginx

# نصب Fail2Ban (امنیت)
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
```

---

## 📦 نصب و پیکربندی

### 1. آماده‌سازی مسیرها

```bash
# ایجاد کاربر اختصاصی (توصیه می‌شود)
sudo adduser --disabled-password --gecos "" zdr
sudo usermod -aG www-data zdr

# ایجاد دایرکتوری
sudo mkdir -p /var/www/zdr
sudo chown -R zdr:www-data /var/www/zdr
```

### 2. دریافت و نصب

```bash
# تبدیل به کاربر zdr
sudo su - zdr

# کلون پروژه
cd /var/www
git clone https://github.com/separkala-ui/zdr.git
cd zdr

# چک کردن آخرین نسخه stable
git checkout main  # یا نسخه خاص: git checkout v2.3.0
```

### 3. تنظیم Environment

```bash
# کپی .env
cp .env.example .env

# ویرایش .env
nano .env
```

**تنظیمات ضروری برای Production:**

```env
APP_NAME="ZDR Production"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zdr_production
DB_USERNAME=zdr_user
DB_PASSWORD=STRONG_RANDOM_PASSWORD

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=STRONG_REDIS_PASSWORD
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_production_email@gmail.com
MAIL_PASSWORD=app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com

SMART_INVOICE_GEMINI_ENABLED=true
SMART_INVOICE_GEMINI_API_KEY=your_production_api_key

TELESCOPE_ENABLED=false  # غیرفعال در production
DEBUGBAR_ENABLED=false   # غیرفعال در production
```

### 4. ایجاد دیتابیس

```bash
sudo mysql -u root -p

CREATE DATABASE zdr_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'zdr_user'@'localhost' IDENTIFIED BY 'STRONG_RANDOM_PASSWORD';
GRANT ALL PRIVILEGES ON zdr_production.* TO 'zdr_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. اجرای نصب

```bash
# نصب با اسکریپت
./scripts/install-enhanced.sh --production

# یا نصب دستی
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. تنظیم دسترسی‌ها

```bash
# مالکیت
sudo chown -R zdr:www-data /var/www/zdr
sudo find /var/www/zdr -type f -exec chmod 644 {} \;
sudo find /var/www/zdr -type d -exec chmod 755 {} \;

# دسترسی ویژه
sudo chmod -R 775 /var/www/zdr/storage
sudo chmod -R 775 /var/www/zdr/bootstrap/cache
sudo chmod 600 /var/www/zdr/.env

# SELinux (اگر فعال است)
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/zdr/storage(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/zdr/bootstrap/cache(/.*)?"
sudo restorecon -Rv /var/www/zdr
```

---

## 🔒 تنظیمات امنیتی

### 1. تنظیم Nginx

ایجاد فایل `/etc/nginx/sites-available/zdr.conf`:

```nginx
# Rate Limiting
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
limit_req_zone $binary_remote_addr zone=api:10m rate=60r/m;

# Upstream PHP-FPM
upstream php-fpm {
    server unix:/run/php/php8.4-fpm.sock;
}

server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    
    root /var/www/zdr/public;
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'self' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:;" always;
    
    # Logging
    access_log /var/log/nginx/zdr-access.log combined buffer=32k;
    error_log /var/log/nginx/zdr-error.log warn;
    
    # Max upload size
    client_max_body_size 64M;
    client_body_buffer_size 128k;
    
    # Timeouts
    fastcgi_read_timeout 300;
    fastcgi_send_timeout 300;
    
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json application/javascript image/svg+xml;
    
    # Root Location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Rate limit login
    location = /admin/login {
        limit_req zone=login burst=3 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Rate limit API
    location ^~ /api/ {
        limit_req zone=api burst=10 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP Handler
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass php-fpm;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_buffering on;
        fastcgi_buffer_size 32k;
        fastcgi_buffers 8 16k;
    }
    
    # Deny access to sensitive files
    location ~ /\.(env|git|htaccess) {
        deny all;
        return 404;
    }
    
    location ~ /(composer\.json|composer\.lock|package\.json|webpack\.mix\.js) {
        deny all;
        return 404;
    }
    
    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # Favicon & Robots
    location = /favicon.ico {
        access_log off;
        log_not_found off;
    }
    
    location = /robots.txt {
        access_log off;
        log_not_found off;
    }
}
```

فعال‌سازی:
```bash
sudo ln -s /etc/nginx/sites-available/zdr.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 2. دریافت SSL Certificate

```bash
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

### 3. تنظیم Firewall

```bash
# UFW
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable

# یا iptables
sudo iptables -A INPUT -p tcp --dport 22 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 443 -j ACCEPT
sudo iptables -A INPUT -i lo -j ACCEPT
sudo iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT
sudo iptables -P INPUT DROP
sudo iptables-save | sudo tee /etc/iptables/rules.v4
```

### 4. تنظیم Fail2Ban

ایجاد `/etc/fail2ban/jail.local`:

```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
logpath = /var/log/nginx/zdr-error.log

[nginx-botsearch]
enabled = true
filter = nginx-botsearch
logpath = /var/log/nginx/zdr-access.log
```

```bash
sudo systemctl restart fail2ban
```

---

## ⚡ بهینه‌سازی عملکرد

### 1. تنظیم PHP-FPM

ویرایش `/etc/php/8.4/fpm/pool.d/www.conf`:

```ini
[www]
user = www-data
group = www-data
listen = /run/php/php8.4-fpm.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500

; PHP settings
php_admin_value[memory_limit] = 256M
php_value[max_execution_time] = 300
php_value[upload_max_filesize] = 64M
php_value[post_max_size] = 64M
```

### 2. تنظیم OPcache

ویرایش `/etc/php/8.4/fpm/conf.d/10-opcache.ini`:

```ini
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.max_wasted_percentage=5
opcache.validate_timestamps=0
opcache.revalidate_freq=0
opcache.save_comments=1
opcache.fast_shutdown=1
```

### 3. تنظیم Redis

ویرایش `/etc/redis/redis.conf`:

```conf
maxmemory 256mb
maxmemory-policy allkeys-lru
save ""
appendonly yes
appendfsync everysec
```

### 4. تنظیم Supervisor (Queue Workers)

ایجاد `/etc/supervisor/conf.d/zdr-worker.conf`:

```ini
[program:zdr-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/zdr/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=zdr
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/zdr/storage/logs/worker.log
stopwaitsecs=3600

[program:zdr-scheduler]
command=/bin/sh -c "while [ true ]; do (php /var/www/zdr/artisan schedule:run --verbose --no-interaction & ); sleep 60; done"
autostart=true
autorestart=true
user=zdr
redirect_stderr=true
stdout_logfile=/var/www/zdr/storage/logs/scheduler.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

### 5. تنظیم Cron

```bash
sudo crontab -e -u zdr
```

اضافه کردن:
```cron
* * * * * cd /var/www/zdr && php artisan schedule:run >> /dev/null 2>&1
0 0 * * * cd /var/www/zdr && php artisan telescope:prune
0 0 * * * cd /var/www/zdr && php artisan pulse:purge
```

---

## 📊 مانیتورینگ

### 1. Log Rotation

ایجاد `/etc/logrotate.d/zdr`:

```conf
/var/www/zdr/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 zdr www-data
    sharedscripts
}
```

### 2. Health Check Endpoint

در routes/api.php:
```php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'database' => DB::connection()->getDatabaseName(),
    ]);
});
```

### 3. مانیتورینگ با Prometheus (اختیاری)

نصب exporter:
```bash
composer require ensi/laravel-prometheus
php artisan vendor:publish --provider="Ensi\LaravelPrometheus\PrometheusServiceProvider"
```

---

## 💾 Backup

### اسکریپت Backup خودکار

ایجاد `/var/www/zdr/scripts/backup.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/zdr"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="zdr_production"
DB_USER="zdr_user"
DB_PASS="YOUR_PASSWORD"

# ایجاد دایرکتوری
mkdir -p $BACKUP_DIR

# Backup دیتابیس
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup فایل‌ها
tar -czf $BACKUP_DIR/files_$DATE.tar.gz \
    --exclude='node_modules' \
    --exclude='vendor' \
    /var/www/zdr

# حذف backupهای قدیمی (بیشتر از 7 روز)
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup completed: $DATE"
```

اضافه به cron:
```cron
0 2 * * * /var/www/zdr/scripts/backup.sh >> /var/log/zdr-backup.log 2>&1
```

---

## 🔄 CI/CD با GitHub Actions

ایجاد `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]
    
jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - name: Deploy to Server
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.SSH_KEY }}
        script: |
          cd /var/www/zdr
          git pull origin main
          composer install --no-dev --optimize-autoloader
          npm install && npm run build
          php artisan migrate --force
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
          sudo supervisorctl restart zdr-worker:*
```

---

## ✅ چک‌لیست نهایی

- [ ] سرور به‌روز شده (apt update && upgrade)
- [ ] تمام سرویس‌ها نصب شده (PHP, Nginx, MySQL, Redis)
- [ ] SSL نصب و تنظیم شده
- [ ] Firewall پیکربندی شده
- [ ] Fail2Ban فعال است
- [ ] دسترسی‌های فایل صحیح است
- [ ] .env در حالت production است
- [ ] APP_DEBUG=false
- [ ] Queue Worker راه‌اندازی شده
- [ ] Cron Jobs تنظیم شده
- [ ] Backup خودکار فعال است
- [ ] مانیتورینگ راه‌اندازی شده
- [ ] Health check endpoint کار می‌کند
- [ ] پسورد admin تغییر کرده
- [ ] Telescope و Debugbar غیرفعال شده

---

**✅ استقرار کامل شد!**

برای پشتیبانی: support@zdr.ir

