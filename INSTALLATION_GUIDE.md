# 📘 راهنمای نصب کامل پروژه Tankha/ZDR روی سرور جدید

## 📋 فهرست مطالب
1. [پیش‌نیازها](#پیش-نیازها)
2. [نصب وابستگی‌های سیستم](#نصب-وابستگی-های-سیستم)
3. [نصب پروژه از GitHub](#نصب-پروژه-از-github)
4. [پیکربندی پروژه](#پیکربندی-پروژه)
5. [راه‌اندازی دیتابیس](#راه-اندازی-دیتابیس)
6. [Build کردن Assets](#build-کردن-assets)
7. [تنظیمات وب‌سرور](#تنظیمات-وب-سرور)
8. [تنظیمات نهایی و بهینه‌سازی](#تنظیمات-نهایی-و-بهینه-سازی)
9. [تست و اجرا](#تست-و-اجرا)
10. [عیب‌یابی مشکلات رایج](#عیب-یابی-مشکلات-رایج)

---

## 1️⃣ پیش‌نیازها

قبل از شروع، مطمئن شوید:
- سرور لینوکس (Ubuntu 20.04+ یا Debian 11+)
- دسترسی SSH با کاربر root یا sudo
- حداقل 2GB RAM
- حداقل 10GB فضای دیسک
- دامنه (Domain) برای نصب SSL

---

## 2️⃣ نصب وابستگی‌های سیستم

### گام 1: به‌روزرسانی سیستم
```bash
sudo apt update && sudo apt upgrade -y
```
**توضیح:** لیست package ها را به‌روز می‌کند و آپدیت‌های امنیتی را نصب می‌کند.

---

### گام 2: نصب PHP 8.3 یا 8.4
```bash
# اضافه کردن repository PHP
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# نصب PHP و extensionهای مورد نیاز
sudo apt install -y php8.4-fpm php8.4-cli php8.4-common \
    php8.4-mysql php8.4-zip php8.4-gd php8.4-mbstring \
    php8.4-curl php8.4-xml php8.4-bcmath php8.4-intl \
    php8.4-redis php8.4-soap php8.4-imagick \
    php8.4-ldap php8.4-sqlite3

# بررسی نسخه PHP
php -v
```
**توضیح:** 
- `php8.4-fpm`: برای اجرای PHP با Nginx
- `php8.4-mysql`: برای اتصال به MySQL/MariaDB
- `php8.4-redis`: برای Laravel Horizon و Queue
- `php8.4-gd` و `php8.4-imagick`: برای کار با تصاویر
- سایر extensionها برای Laravel ضروری هستند

**نکته:** اگر سیستم شما فقط PHP 8.3 دارد، `8.4` را با `8.3` جایگزین کنید.

---

### گام 3: نصب Composer
```bash
# دانلود Composer
curl -sS https://getcomposer.org/installer -o composer-setup.php

# نصب Composer
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# حذف فایل نصب
rm composer-setup.php

# بررسی نسخه
composer --version
```
**توضیح:** Composer مدیریت package های PHP است (مثل npm برای Node.js)

---

### گام 4: نصب Node.js v20+ و npm
```bash
# نصب Node.js v20 از NodeSource
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# بررسی نسخه‌ها
node --version  # باید v20.x نمایش دهد
npm --version   # باید v10.x نمایش دهد
```
**توضیح:** 
- پروژه از Vite 6 استفاده می‌کند که نیاز به Node.js v20+ دارد
- npm برای نصب package های JavaScript استفاده می‌شود

---

### گام 5: نصب MySQL یا MariaDB
```bash
# نصب MariaDB (توصیه می‌شود)
sudo apt install -y mariadb-server mariadb-client

# امن‌سازی MySQL
sudo mysql_secure_installation
```
**توضیح:** 
در `mysql_secure_installation`:
- رمز root را تنظیم کنید (مثلاً: `YourStrongPassword123`)
- به سوال "Remove anonymous users?" پاسخ `Y` دهید
- به سوال "Disallow root login remotely?" پاسخ `Y` دهید
- به سوال "Remove test database?" پاسخ `Y` دهید
- به سوال "Reload privilege tables?" پاسخ `Y` دهید

```bash
# ورود به MySQL و ایجاد دیتابیس
sudo mysql -u root -p
```

**در MySQL prompt دستورات زیر را اجرا کنید:**
```sql
-- ایجاد دیتابیس
CREATE DATABASE tankha CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ایجاد کاربر و دادن دسترسی
CREATE USER 'tankha_user'@'localhost' IDENTIFIED BY 'YourDBPassword123';
GRANT ALL PRIVILEGES ON tankha.* TO 'tankha_user'@'localhost';
FLUSH PRIVILEGES;

-- خروج از MySQL
EXIT;
```
**توضیح:**
- `tankha`: نام دیتابیس (می‌توانید تغییر دهید)
- `tankha_user`: نام کاربر دیتابیس
- `YourDBPassword123`: رمز عبور دیتابیس (حتماً یک رمز قوی انتخاب کنید)

---

### گام 6: نصب Redis
```bash
# نصب Redis Server
sudo apt install -y redis-server

# فعال‌سازی و راه‌اندازی Redis
sudo systemctl enable redis-server
sudo systemctl start redis-server

# بررسی وضعیت
sudo systemctl status redis-server

# تست Redis
redis-cli ping  # باید "PONG" برگرداند
```
**توضیح:** Redis برای Laravel Horizon (مدیریت queue) و caching استفاده می‌شود.

---

### گام 7: نصب Git
```bash
sudo apt install -y git

# تنظیمات اولیه Git (اختیاری)
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

---

## 3️⃣ نصب پروژه از GitHub

### گام 1: Clone کردن پروژه
```bash
# رفتن به دایرکتوری وب
cd /var/www

# Clone پروژه از GitHub
sudo git clone https://github.com/separkala-ui/tankha.git zdr

# تغییر مالکیت فایل‌ها به www-data
sudo chown -R www-data:www-data /var/www/zdr
sudo chmod -R 755 /var/www/zdr
```
**توضیح:**
- `/var/www/zdr`: مسیر نصب پروژه (می‌توانید تغییر دهید)
- `www-data`: کاربر پیش‌فرض وب‌سرور در Ubuntu/Debian
- `755`: مجوزهای خواندن و اجرا برای همه، نوشتن فقط برای مالک

**نکته:** اگر repository خصوصی است، از Personal Access Token استفاده کنید:
```bash
git clone https://USERNAME:TOKEN@github.com/separkala-ui/tankha.git zdr
```

---

### گام 2: ورود به دایرکتوری پروژه
```bash
cd /var/www/zdr
```

---

## 4️⃣ پیکربندی پروژه

### گام 1: نصب Composer Dependencies
```bash
# نصب package های PHP (برای production)
composer install --no-dev --optimize-autoloader
```
**توضیح:**
- `--no-dev`: package های development نصب نمی‌شوند (برای production)
- `--optimize-autoloader`: بهینه‌سازی autoloading برای سرعت بیشتر

**اگر در حالت development هستید:**
```bash
composer install
```

**نکته:** این فرآیند ممکن است 2-5 دقیقه طول بکشد.

---

### گام 2: کپی و تنظیم فایل .env
```bash
# کپی فایل نمونه
cp .env.example .env

# ویرایش فایل .env
nano .env
```

**تنظیمات مهم در .env:**
```env
# اطلاعات اصلی
APP_NAME="Tankha"
APP_ENV=production
APP_DEBUG=false  # در production حتماً false باشد
APP_URL=https://yourdomain.com

# دیتابیس
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tankha
DB_USERNAME=tankha_user
DB_PASSWORD=YourDBPassword123

# Redis (برای Queue و Cache)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache و Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail (اگر نیاز دارید)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Filament
FILAMENT_FILESYSTEM_DISK=public

# Locale
APP_LOCALE=fa
APP_FALLBACK_LOCALE=en
APP_TIMEZONE=Asia/Tehran
```

**ذخیره فایل در nano:** 
1. فشار دادن `Ctrl + O` (save)
2. فشار دادن `Enter` (تایید نام فایل)
3. فشار دادن `Ctrl + X` (خروج)

---

### گام 3: تولید APP_KEY
```bash
php artisan key:generate
```
**توضیح:** این دستور یک کلید رمزنگاری منحصر به فرد برای Laravel ایجاد می‌کند و در `.env` ذخیره می‌شود.

---

### گام 4: تنظیم مجوزهای فایل‌ها
```bash
# مجوزهای storage و bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# ایجاد symbolic link برای storage
php artisan storage:link
```
**توضیح:**
- `storage`: برای ذخیره لاگ‌ها، cache، session، و فایل‌های آپلود شده
- `bootstrap/cache`: برای cache کردن فایل‌های بوت‌استرپ
- `storage:link`: لینک symbolic از `public/storage` به `storage/app/public` ایجاد می‌کند

---

## 5️⃣ راه‌اندازی دیتابیس

### گام 1: اجرای Migrations
```bash
# اجرای تمام migrations
php artisan migrate --force
```
**توضیح:** 
- `--force`: اجبار به اجرا در محیط production (بدون سوال)
- این دستور جداول دیتابیس را ایجاد می‌کند
- اگر خطایی دریافت کردید، بررسی کنید که اطلاعات دیتابیس در `.env` صحیح است

---

### گام 2: اجرای Seeders (دیتای اولیه)
```bash
# اجرای seeders برای ایجاد کاربر admin و نقش‌ها
php artisan db:seed --force
```
**توضیح:** این دستور کاربر admin پیش‌فرض و دیتاهای اولیه را ایجاد می‌کند.

**یا اگر می‌خواهید همه چیز را از نو شروع کنید:**
```bash
php artisan migrate:fresh --seed --force
```
⚠️ **هشدار:** دستور بالا تمام جداول را حذف و دوباره ایجاد می‌کند (تمام دیتا پاک می‌شود)!

---

### گام 3: ایجاد جداول Queue
```bash
# ایجاد migration برای جداول queue
php artisan queue:table
php artisan queue:failed-table

# اجرای migrations جدید
php artisan migrate --force
```
**توضیح:** این جداول برای Laravel Horizon و Queue system لازم هستند.

---

## 6️⃣ Build کردن Assets

### گام 1: پاک کردن cache npm (اختیاری اما توصیه می‌شود)
```bash
npm cache clean --force
```

---

### گام 2: نصب npm Dependencies
```bash
# پاک کردن node_modules قدیمی (اگر وجود دارد)
rm -rf node_modules package-lock.json

# نصب package های JavaScript
npm install
```
**توضیح:** 
- این فرآیند ممکن است 3-10 دقیقه طول بکشد (حدود 467 package)
- اگر با خطا مواجه شدید، دوباره `npm cache clean --force` را اجرا کنید و مجدداً تلاش کنید

---

### گام 3: Build Assets با Vite
```bash
# برای production (minified و optimized)
npm run build
```
**توضیح:** 
- فایل‌های CSS/JS را minify و بهینه می‌کند
- فایل `public/build/manifest.json` ایجاد می‌شود
- فایل‌های نهایی در `public/build/assets/` قرار می‌گیرند
- این فرآیند ممکن است 1-2 دقیقه طول بکشد

**خروجی موفقیت‌آمیز شبیه این است:**
```
vite v6.4.1 building for production...
✓ 159 modules transformed.
public/build/manifest.json                0.33 kB
public/build/assets/app-*.css           192.46 kB
public/build/assets/app-*.js          1,396.00 kB
✓ built in 11.50s
```

**برای development (اختیاری):**
```bash
npm run dev  # اجرا در پس‌زمینه و watch برای تغییرات
```

---

### گام 4: تنظیم مجوزهای فایل‌های build
```bash
sudo chown -R www-data:www-data public/build
sudo chmod -R 755 public/build
```

---

## 7️⃣ تنظیمات وب‌سرور

### گزینه A: استفاده از Nginx (توصیه می‌شود ⭐)

#### نصب Nginx:
```bash
sudo apt install -y nginx
```

#### ایجاد فایل پیکربندی:
```bash
sudo nano /etc/nginx/sites-available/tankha
```

**محتوای فایل را کامل کپی کنید:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    root /var/www/zdr/public;
    index index.php index.html;

    # SSL Certificates (بعد از نصب Let's Encrypt این خطوط فعال می‌شوند)
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    # SSL Configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Logging
    access_log /var/log/nginx/tankha-access.log;
    error_log /var/log/nginx/tankha-error.log;

    # Max upload size
    client_max_body_size 100M;

    # Compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 256 16k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
        fastcgi_read_timeout 600;
    }

    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Deny access to sensitive files
    location ~ /\.(env|git|svn|htaccess) {
        deny all;
    }
}
```

**نکته مهم:** `yourdomain.com` را با دامنه واقعی خود جایگزین کنید!

**ذخیره و خروج:** `Ctrl + O`, `Enter`, `Ctrl + X`

#### فعال‌سازی سایت:
```bash
# ایجاد symbolic link
sudo ln -s /etc/nginx/sites-available/tankha /etc/nginx/sites-enabled/

# حذف سایت پیش‌فرض (اختیاری)
sudo rm /etc/nginx/sites-enabled/default

# تست تنظیمات Nginx
sudo nginx -t

# اگر "test is successful" نمایش داد، Nginx را راه‌اندازی مجدد کنید
sudo systemctl restart nginx

# فعال‌سازی Nginx در بوت سیستم
sudo systemctl enable nginx
```

---

### گزینه B: استفاده از Apache

#### نصب Apache:
```bash
sudo apt install -y apache2 libapache2-mod-php8.4
```

#### فعال‌سازی ماژول‌های مورد نیاز:
```bash
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
sudo systemctl restart apache2
```

#### ایجاد فایل پیکربندی:
```bash
sudo nano /etc/apache2/sites-available/tankha.conf
```

**محتوای فایل:**
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/zdr/public

    <Directory /var/www/zdr/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/tankha-error.log
    CustomLog ${APACHE_LOG_DIR}/tankha-access.log combined

    # Redirect to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/zdr/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem

    # Security Headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"

    <Directory /var/www/zdr/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/tankha-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/tankha-ssl-access.log combined

    # PHP Settings
    php_value upload_max_filesize 100M
    php_value post_max_size 100M
</VirtualHost>
```

**نکته:** `yourdomain.com` را با دامنه خود جایگزین کنید!

#### فعال‌سازی سایت:
```bash
# غیرفعال کردن سایت پیش‌فرض
sudo a2dissite 000-default.conf

# فعال‌سازی سایت جدید
sudo a2ensite tankha.conf

# تست تنظیمات Apache
sudo apache2ctl configtest

# راه‌اندازی مجدد Apache
sudo systemctl restart apache2

# فعال‌سازی Apache در بوت سیستم
sudo systemctl enable apache2
```

---

### نصب SSL Certificate با Let's Encrypt (رایگان) 🔒

#### نصب Certbot:
```bash
# برای Nginx
sudo apt install -y certbot python3-certbot-nginx

# یا برای Apache
sudo apt install -y certbot python3-certbot-apache
```

#### دریافت SSL Certificate:

**برای Nginx:**
```bash
# ابتدا خطوط SSL را از فایل Nginx کامنت کنید یا حذف کنید
sudo nano /etc/nginx/sites-available/tankha
# خطوط ssl_certificate و ssl_certificate_key را موقتاً حذف کنید

# راه‌اندازی مجدد Nginx
sudo systemctl reload nginx

# دریافت Certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# بعد از موفقیت، فایل Nginx را دوباره ویرایش و خطوط SSL را اضافه کنید
sudo nano /etc/nginx/sites-available/tankha

# راه‌اندازی مجدد Nginx
sudo systemctl reload nginx
```

**برای Apache:**
```bash
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

**توضیح:** 
- Certbot به صورت خودکار certificate را دریافت و نصب می‌کند
- در سوالات، ایمیل خود را وارد کنید
- با شرایط و ضوابط موافقت کنید (Y)
- اگر پرسید آیا redirect به HTTPS انجام شود، `2` (Yes) را انتخاب کنید

#### تنظیم تمدید خودکار:
```bash
# فعال‌سازی timer تمدید خودکار
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer

# تست تمدید
sudo certbot renew --dry-run
```
**توضیح:** Certificate هر 90 روز یکبار باید تمدید شود، این timer به صورت خودکار آن را انجام می‌دهد.

---

## 8️⃣ تنظیمات نهایی و بهینه‌سازی

### گام 1: Cache کردن تنظیمات Laravel
```bash
# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events (اگر از Events استفاده می‌کنید)
php artisan event:cache
```
**توضیح:** این دستورات سرعت برنامه را به طور قابل توجهی افزایش می‌دهند.

**نکته:** هر بار که `.env` یا فایل‌های config را تغییر می‌دهید، باید این دستورات را دوباره اجرا کنید.

---

### گام 2: راه‌اندازی Queue Worker با Supervisor

#### نصب Supervisor:
```bash
sudo apt install -y supervisor
```

#### ایجاد فایل پیکربندی:
```bash
sudo nano /etc/supervisor/conf.d/tankha-worker.conf
```

**محتوای فایل:**
```ini
[program:tankha-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/zdr/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/zdr/storage/logs/worker.log
stopwaitsecs=3600
```

**توضیح:**
- `numprocs=2`: دو worker همزمان اجرا می‌شود
- `max-time=3600`: هر worker بعد از 1 ساعت restart می‌شود
- `tries=3`: هر job حداکثر 3 بار تلاش می‌شود

**ذخیره و خروج:** `Ctrl + O`, `Enter`, `Ctrl + X`

#### راه‌اندازی Supervisor:
```bash
# خواندن تنظیمات جدید
sudo supervisorctl reread

# اعمال تنظیمات
sudo supervisorctl update

# شروع worker
sudo supervisorctl start tankha-worker:*

# بررسی وضعیت
sudo supervisorctl status

# فعال‌سازی Supervisor در بوت سیستم
sudo systemctl enable supervisor
```

**دستورات مفید Supervisor:**
```bash
# توقف workers
sudo supervisorctl stop tankha-worker:*

# راه‌اندازی مجدد workers
sudo supervisorctl restart tankha-worker:*

# مشاهده لاگ‌ها
sudo supervisorctl tail -f tankha-worker:tankha-worker_00 stdout
```

---

### گام 3: تنظیم Cron Job برای Laravel Scheduler

```bash
# ویرایش crontab برای کاربر www-data
sudo crontab -e -u www-data
```

**اولین بار که اجرا می‌کنید، editor را انتخاب کنید (nano را توصیه می‌کنم - معمولاً شماره 1)**

**در انتهای فایل این خط را اضافه کنید:**
```
* * * * * cd /var/www/zdr && php artisan schedule:run >> /dev/null 2>&1
```

**توضیح:** این cron job هر دقیقه اجرا می‌شود و Laravel Scheduler را چک می‌کند.

**ذخیره و خروج:** `Ctrl + O`, `Enter`, `Ctrl + X`

**بررسی crontab:**
```bash
sudo crontab -l -u www-data
```

---

### گام 4: تنظیمات PHP برای Performance

```bash
# ویرایش php.ini
sudo nano /etc/php/8.4/fpm/php.ini
```

**تنظیمات پیشنهادی:**
```ini
memory_limit = 512M
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
max_input_time = 300
```

**راه‌اندازی مجدد PHP-FPM:**
```bash
sudo systemctl restart php8.4-fpm
```

---

## 9️⃣ تست و اجرا

### گام 1: بررسی وضعیت تمام سرویس‌ها
```bash
# بررسی Nginx
sudo systemctl status nginx

# یا بررسی Apache
sudo systemctl status apache2

# بررسی PHP-FPM
sudo systemctl status php8.4-fpm

# بررسی MySQL
sudo systemctl status mysql

# بررسی Redis
sudo systemctl status redis-server

# بررسی Supervisor
sudo supervisorctl status
```

**همه باید وضعیت "active (running)" یا "RUNNING" داشته باشند.**

---

### گام 2: پاک کردن تمام cache ها
```bash
cd /var/www/zdr
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### گام 3: دسترسی به برنامه

🌐 **آدرس‌های دسترسی:**

1. **پنل Admin اصلی (Lara Dashboard):**
   ```
   https://yourdomain.com/admin/login
   ```

2. **پنل Filament (مدیریت فرم‌ها):**
   ```
   https://yourdomain.com/filament/login
   ```

3. **Laravel Horizon (مدیریت Queue):**
   ```
   https://yourdomain.com/admin/horizon
   ```
   فقط کاربران با نقش Superadmin دسترسی دارند.

---

### گام 4: ایجاد کاربر Admin

اگر seeder کاربر ایجاد نکرد، می‌توانید دستی ایجاد کنید:

```bash
php artisan tinker
```

**در tinker این دستورات را اجرا کنید:**
```php
// بررسی وجود کاربر
User::all(['id', 'email', 'full_name']);

// اگر کاربری وجود ندارد، ایجاد کنید:
$user = new App\Models\User();
$user->full_name = 'مدیر سیستم';
$user->email = 'admin@yourdomain.com';
$user->password = bcrypt('AdminPassword123');
$user->email_verified_at = now();
$user->save();

// بررسی وجود نقش Superadmin
$role = Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Superadmin']);

// اختصاص نقش به کاربر
$user->assignRole('Superadmin');

// تایید
$user->fresh();

// خروج
exit
```

**اطلاعات ورود:**
- **ایمیل:** `admin@yourdomain.com`
- **رمز عبور:** `AdminPassword123`

⚠️ **حتماً بعد از اولین ورود، رمز عبور را از پنل تغییر دهید!**

---

### گام 5: تست عملکرد

#### تست 1: دسترسی به صفحه اصلی
```bash
curl -I https://yourdomain.com
```
باید کد `200` یا `302` برگرداند.

#### تست 2: بررسی assets
```bash
curl -I https://yourdomain.com/build/manifest.json
```
باید کد `200` برگرداند.

#### تست 3: بررسی Queue
```bash
php artisan queue:monitor
```

#### تست 4: ارسال یک Job تست
```bash
php artisan tinker
```
```php
dispatch(function () {
    logger('Test job executed successfully!');
});
exit
```

سپس لاگ را بررسی کنید:
```bash
tail -20 storage/logs/laravel.log
```

---

## 🔧 عیب‌یابی مشکلات رایج

### مشکل 1: خطای 500 Internal Server Error

**بررسی لاگ‌ها:**
```bash
# لاگ Laravel
tail -100 /var/www/zdr/storage/logs/laravel.log

# لاگ Nginx
tail -50 /var/log/nginx/tankha-error.log

# لاگ PHP-FPM
tail -50 /var/log/php8.4-fpm.log
```

**راه‌حل‌های رایج:**
```bash
# 1. بررسی مجوزهای فایل‌ها
sudo chown -R www-data:www-data /var/www/zdr
sudo chmod -R 755 /var/www/zdr
sudo chmod -R 775 /var/www/zdr/storage
sudo chmod -R 775 /var/www/zdr/bootstrap/cache

# 2. پاک کردن cache
php artisan optimize:clear

# 3. بررسی .env
cat /var/www/zdr/.env | grep DB_
```

---

### مشکل 2: Assets (CSS/JS) لود نمی‌شوند

**بررسی:**
```bash
# بررسی وجود manifest.json
ls -lah /var/www/zdr/public/build/manifest.json

# بررسی محتوای manifest
cat /var/www/zdr/public/build/manifest.json
```

**راه‌حل:**
```bash
# اگر فایل وجود ندارد یا خالی است
cd /var/www/zdr
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
npm run build

# تنظیم مجوزها
sudo chown -R www-data:www-data public/build
sudo chmod -R 755 public/build

# پاک کردن cache
php artisan optimize:clear
```

---

### مشکل 3: خطای اتصال به دیتابیس

**تست اتصال:**
```bash
php artisan tinker
```
```php
DB::connection()->getPdo();
// اگر موفق باشد: PDO object نمایش می‌دهد
// اگر خطا باشد: Exception نمایش می‌دهد
exit
```

**راه‌حل:**
```bash
# 1. بررسی اطلاعات دیتابیس در .env
cat /var/www/zdr/.env | grep DB_

# 2. تست ورود به MySQL
mysql -u tankha_user -p tankha

# 3. اگر نتوانستید وارد شوید، دوباره کاربر را ایجاد کنید
sudo mysql -u root -p
```
```sql
DROP USER IF EXISTS 'tankha_user'@'localhost';
CREATE USER 'tankha_user'@'localhost' IDENTIFIED BY 'YourDBPassword123';
GRANT ALL PRIVILEGES ON tankha.* TO 'tankha_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

### مشکل 4: Queue کار نمی‌کند

**بررسی:**
```bash
# 1. بررسی Redis
redis-cli ping
# باید "PONG" برگرداند

# 2. بررسی Supervisor
sudo supervisorctl status

# 3. بررسی لاگ worker
tail -50 /var/www/zdr/storage/logs/worker.log
```

**راه‌حل:**
```bash
# راه‌اندازی مجدد workers
sudo supervisorctl restart tankha-worker:*

# اگر خطا داد، مجدد update کنید
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start tankha-worker:*
```

---

### مشکل 5: خطای Permission Denied

**راه‌حل:**
```bash
# تنظیم صحیح مالکیت
sudo chown -R www-data:www-data /var/www/zdr

# تنظیم مجوزهای صحیح
sudo find /var/www/zdr -type f -exec chmod 644 {} \;
sudo find /var/www/zdr -type d -exec chmod 755 {} \;

# مجوزهای خاص
sudo chmod -R 775 /var/www/zdr/storage
sudo chmod -R 775 /var/www/zdr/bootstrap/cache
```

---

### مشکل 6: صفحه سفید (Blank Page)

**راه‌حل:**
```bash
# فعال کردن error reporting موقتی
nano /var/www/zdr/.env
```
تغییر `APP_DEBUG=false` به `APP_DEBUG=true`

```bash
# پاک کردن cache
php artisan optimize:clear

# بررسی لاگ
tail -100 /var/www/zdr/storage/logs/laravel.log
```

⚠️ **مهم:** بعد از حل مشکل، حتماً `APP_DEBUG=false` کنید!

---

### مشکل 7: Filament styles لود نمی‌شود

**راه‌حل:**
```bash
# پاک کردن cache Filament
php artisan filament:clear-cached-components
php artisan filament:optimize-clear

# اگر کمک نکرد، assets را rebuild کنید
npm run build

# پاک کردن کامل cache
php artisan optimize:clear
```

---

## 📊 دستورات مفید روزمره

### مدیریت Cache
```bash
# پاک کردن تمام cache ها
php artisan optimize:clear

# پاک کردن cache خاص
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# ایجاد cache برای production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### مدیریت Queue
```bash
# مشاهده وضعیت Queue
php artisan queue:monitor

# اجرای یک job
php artisan queue:work --once

# پاک کردن failed jobs
php artisan queue:flush

# مشاهده failed jobs
php artisan queue:failed
```

### مشاهده لاگ‌ها
```bash
# لاگ Laravel (live)
tail -f /var/www/zdr/storage/logs/laravel.log

# لاگ Worker (live)
tail -f /var/www/zdr/storage/logs/worker.log

# لاگ Nginx (live)
tail -f /var/log/nginx/tankha-error.log
tail -f /var/log/nginx/tankha-access.log

# 100 خط آخر
tail -100 /var/www/zdr/storage/logs/laravel.log
```

### اطلاعات سیستم
```bash
# اطلاعات Laravel
php artisan about

# لیست routes
php artisan route:list

# لیست migrations
php artisan migrate:status

# بررسی نسخه‌ها
php -v
node -v
npm -v
composer --version
```

### بررسی وضعیت سرویس‌ها
```bash
# همه سرویس‌ها
sudo systemctl status nginx php8.4-fpm mysql redis-server supervisor

# یک سرویس خاص
sudo systemctl status nginx
```

### Backup
```bash
# Backup دیتابیس
mysqldump -u tankha_user -p tankha > /var/backups/tankha_$(date +%Y%m%d).sql

# Backup فایل‌ها
tar -czf /var/backups/tankha_files_$(date +%Y%m%d).tar.gz /var/www/zdr \
  --exclude=/var/www/zdr/node_modules \
  --exclude=/var/www/zdr/vendor \
  --exclude=/var/www/zdr/storage/logs
```

---

## 🔐 توصیه‌های امنیتی مهم

### 1. تغییر رمزهای پیش‌فرض
```bash
# تغییر رمز کاربر admin از پنل
# تغییر APP_KEY (اگر لازم شد)
php artisan key:generate

# تغییر رمز دیتابیس MySQL
sudo mysql -u root -p
```
```sql
ALTER USER 'tankha_user'@'localhost' IDENTIFIED BY 'NewStrongPassword123';
FLUSH PRIVILEGES;
EXIT;
```
سپس `.env` را آپدیت کنید.

### 2. غیرفعال کردن Debug در Production
```bash
nano /var/www/zdr/.env
```
```env
APP_ENV=production
APP_DEBUG=false
```

### 3. محدود کردن دسترسی به Horizon
فقط کاربران با نقش Superadmin می‌توانند به `/admin/horizon` دسترسی داشته باشند.

### 4. تنظیم Firewall
```bash
# فعال‌سازی UFW
sudo ufw enable

# اجازه دسترسی به سرویس‌های ضروری
sudo ufw allow 22/tcp     # SSH
sudo ufw allow 80/tcp     # HTTP
sudo ufw allow 443/tcp    # HTTPS

# بررسی وضعیت
sudo ufw status verbose
```

### 5. غیرفعال کردن listing دایرکتوری‌ها
این قبلاً در تنظیمات Nginx/Apache انجام شده (`Options -Indexes`)

### 6. محافظت از فایل‌های حساس
```bash
# تنظیم مجوز .env
chmod 600 /var/www/zdr/.env
chown www-data:www-data /var/www/zdr/.env
```

### 7. نصب Fail2Ban (اختیاری اما توصیه می‌شود)
```bash
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 8. بروزرسانی منظم سیستم
```bash
# هر هفته اجرا کنید
sudo apt update && sudo apt upgrade -y
```

---

## ✅ چک‌لیست نهایی نصب

پیش از اعلام پایان نصب، این موارد را بررسی کنید:

### سیستم و وابستگی‌ها
- [ ] PHP 8.3/8.4 نصب شده و فعال است
- [ ] Composer نصب شده (`composer --version`)
- [ ] Node.js v20+ نصب شده (`node --version`)
- [ ] npm v10+ نصب شده (`npm --version`)
- [ ] MySQL/MariaDB نصب و فعال است
- [ ] Redis نصب و فعال است (`redis-cli ping`)
- [ ] Git نصب شده

### پروژه
- [ ] پروژه از GitHub clone شده (`/var/www/zdr`)
- [ ] `composer install` با موفقیت اجرا شده
- [ ] فایل `.env` کپی و تنظیم شده
- [ ] `APP_KEY` تولید شده (`php artisan key:generate`)
- [ ] مجوزهای `storage` و `bootstrap/cache` تنظیم شده (775)
- [ ] `storage:link` ایجاد شده

### دیتابیس
- [ ] دیتابیس `tankha` ایجاد شده
- [ ] کاربر `tankha_user` ایجاد و دسترسی داده شده
- [ ] اطلاعات دیتابیس در `.env` صحیح است
- [ ] `php artisan migrate` با موفقیت اجرا شده
- [ ] `php artisan db:seed` با موفقیت اجرا شده
- [ ] جداول queue ایجاد شده

### Assets
- [ ] `npm install` با موفقیت اجرا شده (467 packages)
- [ ] `npm run build` با موفقیت اجرا شده
- [ ] فایل `public/build/manifest.json` وجود دارد
- [ ] فایل‌های CSS/JS در `public/build/assets/` وجود دارند
- [ ] مجوزهای `public/build` تنظیم شده (755)

### وب‌سرور
- [ ] Nginx یا Apache نصب و فعال است
- [ ] فایل پیکربندی سایت ایجاد شده
- [ ] سایت فعال شده (`sites-enabled`)
- [ ] SSL Certificate نصب شده (Let's Encrypt)
- [ ] HTTPS کار می‌کند
- [ ] HTTP به HTTPS redirect می‌شود

### سرویس‌ها
- [ ] PHP-FPM فعال است (`systemctl status php8.4-fpm`)
- [ ] Supervisor نصب و تنظیم شده
- [ ] Queue workers در حال اجرا هستند (`supervisorctl status`)
- [ ] Cron job برای Scheduler تنظیم شده

### Cache و بهینه‌سازی
- [ ] `php artisan config:cache` اجرا شده
- [ ] `php artisan route:cache` اجرا شده
- [ ] `php artisan view:cache` اجرا شده

### تست و دسترسی
- [ ] سایت از طریق HTTPS در دسترس است
- [ ] صفحه `/admin/login` بارگذاری می‌شود
- [ ] صفحه `/filament/login` بارگذاری می‌شود
- [ ] Assets (CSS/JS) بارگذاری می‌شوند
- [ ] کاربر admin ایجاد شده
- [ ] ورود با کاربر admin موفق است
- [ ] صفحه `/admin/horizon` برای Superadmin قابل دسترسی است

### امنیت
- [ ] `APP_DEBUG=false` در `.env`
- [ ] رمز عبور admin تغییر کرده
- [ ] Firewall (UFW) فعال شده
- [ ] SSL Certificate اعتبارسنجی شده
- [ ] مجوز `.env` محدود شده (600)

### اختیاری اما توصیه می‌شود
- [ ] Fail2Ban نصب شده
- [ ] سیستم backup خودکار تنظیم شده
- [ ] Monitoring (مثل Uptime Robot) تنظیم شده
- [ ] Log rotation تنظیم شده

---

## 🎉 تبریک!

اگر تمام موارد چک‌لیست را تایید کردید، نصب شما با موفقیت کامل شده است!

### آدرس‌های دسترسی:
- 🌐 **Admin Panel:** https://yourdomain.com/admin/login
- 📝 **Filament:** https://yourdomain.com/filament/login
- ⚡ **Horizon:** https://yourdomain.com/admin/horizon

### اطلاعات ورود:
- **Email:** admin@yourdomain.com
- **Password:** AdminPassword123

⚠️ **فراموش نکنید:** رمز عبور را از پنل تغییر دهید!

---

## 📞 پشتیبانی و منابع

### مشکل داشتید؟
1. **بررسی لاگ‌ها:**
   ```bash
   tail -100 /var/www/zdr/storage/logs/laravel.log
   ```

2. **GitHub Issues:**
   https://github.com/separkala-ui/tankha/issues

3. **اجرای Artisan About:**
   ```bash
   php artisan about
   ```

### منابع مفید
- 📚 **Laravel Documentation:** https://laravel.com/docs
- 🎨 **Filament Documentation:** https://filamentphp.com/docs
- 🚀 **Lara Dashboard:** https://laradash.dev
- 🔧 **Laravel Horizon:** https://laravel.com/docs/horizon

---

## 📝 یادداشت‌های نهایی

### بروزرسانی پروژه
```bash
cd /var/www/zdr

# دریافت آخرین تغییرات از GitHub
git pull origin main

# نصب dependencies جدید
composer install --no-dev --optimize-autoloader
npm install
npm run build

# اجرای migrations جدید
php artisan migrate --force

# پاک کردن و بازسازی cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# راه‌اندازی مجدد workers
sudo supervisorctl restart tankha-worker:*
```

### Backup منظم
یک cron job برای backup روزانه تنظیم کنید:
```bash
sudo crontab -e
```
اضافه کردن:
```
0 2 * * * mysqldump -u tankha_user -pYourDBPassword123 tankha > /var/backups/tankha_$(date +\%Y\%m\%d).sql
0 3 * * * find /var/backups -name "tankha_*.sql" -mtime +7 -delete
```

### Monitoring
ابزارهای توصیه شده:
- **Uptime Monitoring:** UptimeRobot, Pingdom
- **Server Monitoring:** Netdata, Glances
- **Error Tracking:** Sentry, Bugsnag
- **Performance:** New Relic, Blackfire

---

**نصب موفقیت‌آمیز! 🚀**

این راهنما توسط AI Assistant تهیه شده است.
تاریخ: اکتبر 2025
نسخه: 1.0

