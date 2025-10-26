# 📦 راهنمای نصب کامل ZDR - سیستم مدیریت تنخواه

> **نسخه:** 2.3.0  
> **آخرین بروزرسانی:** 26 آبان 1404  
> **سطح پشتیبانی:** سرورهای اختصاصی، VPS، و هاست اشتراکی

---

## 📑 فهرست مطالب

1. [پیش‌نیازها](#پیشنیازها)
2. [نصب سریع (یک دستوری)](#نصب-سریع)
3. [نصب دستی گام به گام](#نصب-دستی)
4. [نصب روی هاست اشتراکی](#نصب-روی-هاست-اشتراکی)
5. [نصب با Docker](#نصب-با-docker)
6. [نصب روی سرورهای مختلف](#نصب-روی-سرورهای-مختلف)
7. [تنظیمات پس از نصب](#تنظیمات-پس-از-نصب)
8. [عیب‌یابی](#عیبیابی)
9. [پشتیبانی](#پشتیبانی)

---

## 🔧 پیش‌نیازها

### الزامات سخت‌افزاری (حداقل)
- **CPU:** 1 Core
- **RAM:** 1 GB (توصیه: 2 GB+)
- **فضای دیسک:** 2 GB (توصیه: 5 GB+)

### الزامات نرم‌افزاری

#### ضروری
| نرم‌افزار | نسخه حداقل | نسخه توصیه‌شده | چک کردن نسخه |
|----------|------------|-----------------|---------------|
| PHP | 8.3 | 8.4.13 | `php -v` |
| Composer | 2.0 | 2.7+ | `composer -V` |
| MySQL/MariaDB | 8.0 / 10.6 | 8.0+ / 10.11+ | `mysql -V` |
| Node.js | 18.0 | 20 LTS | `node -v` |
| npm | 9.0 | 10.0+ | `npm -v` |

#### افزونه‌های PHP ضروری
```bash
# بررسی افزونه‌های نصب شده
php -m | grep -E "(pdo|mysql|mbstring|xml|gd|curl|openssl|intl|fileinfo|tokenizer|bcmath)"
```

**لیست افزونه‌های مورد نیاز:**
- `pdo_mysql` - اتصال به دیتابیس
- `mbstring` - پردازش رشته‌های چندبایتی (فارسی)
- `xml` - پردازش XML
- `gd` یا `imagick` - پردازش تصاویر
- `curl` - ارتباطات HTTP
- `openssl` - رمزنگاری
- `intl` - بین‌المللی‌سازی
- `fileinfo` - شناسایی نوع فایل
- `tokenizer` - تحلیل کد PHP
- `bcmath` - محاسبات دقیق مالی

---

## ⚡ نصب سریع

### روش 1: نصب خودکار (توصیه می‌شود)

```bash
# 1. کلون کردن پروژه
git clone https://github.com/separkala-ui/zdr.git
cd zdr

# 2. اجرای اسکریپت نصب
chmod +x scripts/install.sh
./scripts/install.sh --seed

# 3. اجرای سرور توسعه
php artisan serve
```

**گزینه‌های نصب:**
- `--seed` - نصب با داده‌های نمونه (توصیه برای تست)
- `--no-build` - بدون بیلد فرانت‌اند (برای سرورهای بدون Node.js)
- `--help` - نمایش راهنما

### روش 2: نصب با Composer

```bash
# اگر پروژه در Packagist است
composer create-project laradashboard/laradashboard zdr
cd zdr
./scripts/install.sh --seed
```

**⏱ زمان تخمینی:** 5-10 دقیقه (بسته به سرعت اینترنت)

---

## 📝 نصب دستی گام به گام

اگر می‌خواهید کنترل کامل بر روی فرآیند نصب داشته باشید:

### گام 1: دریافت کد

```bash
# از Git
git clone https://github.com/separkala-ui/zdr.git
cd zdr

# یا از ZIP
wget https://github.com/separkala-ui/zdr/archive/main.zip
unzip main.zip
cd zdr-main
```

### گام 2: نصب وابستگی‌های PHP

```bash
composer install --no-interaction --prefer-dist --optimize-autoloader
```

**توضیح پارامترها:**
- `--no-interaction`: بدون سوال و پاسخ
- `--prefer-dist`: استفاده از فایل‌های zip بجای git
- `--optimize-autoloader`: بهینه‌سازی autoloader

### گام 3: تنظیم فایل محیطی

```bash
# کپی کردن فایل نمونه
cp .env.example .env

# ویرایش فایل
nano .env
# یا
vim .env
```

**تنظیمات اولیه `.env`:**
```env
# اطلاعات برنامه
APP_NAME="مدیریت تنخواه ZDR"
APP_ENV=local
APP_DEBUG=true  # در production باید false باشد
APP_URL=http://localhost:8000

# دیتابیس
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zdr_database
DB_USERNAME=zdr_user
DB_PASSWORD=your_secure_password

# ایمیل (اختیاری)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@zdr.ir
MAIL_FROM_NAME="${APP_NAME}"

# فاکتور هوشمند با Gemini AI
SMART_INVOICE_GEMINI_ENABLED=true
SMART_INVOICE_GEMINI_API_KEY=your_gemini_api_key_here
SMART_INVOICE_GEMINI_MODEL=gemini-2.5-flash
SMART_INVOICE_GEMINI_TIMEOUT=70

# Queue (برای محیط production)
QUEUE_CONNECTION=database  # یا redis
```

### گام 4: تولید کلید برنامه

```bash
php artisan key:generate
```

### گام 5: ایجاد دیتابیس

```bash
# ورود به MySQL
mysql -u root -p

# ایجاد دیتابیس
CREATE DATABASE zdr_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# ایجاد کاربر (اختیاری اما توصیه می‌شود)
CREATE USER 'zdr_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON zdr_database.* TO 'zdr_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### گام 6: اجرای Migrations

```bash
# اجرای migrations
php artisan migrate --force

# اجرای seeders (داده‌های نمونه)
php artisan db:seed --force
```

### گام 7: لینک Storage

```bash
php artisan storage:link
```

### گام 8: تنظیم دسترسی‌ها

```bash
# روی Linux/Mac
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# روی shared hosting (معمولاً کافی است)
chmod -R 755 storage bootstrap/cache
```

### گام 9: نصب وابستگی‌های Frontend

```bash
# نصب packages
npm install

# بیلد برای production
npm run build

# یا برای development
npm run dev
```

### گام 10: بهینه‌سازی نهایی

```bash
# کش کردن config و routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

# یا پاکسازی همه کش‌ها (در صورت مشکل)
php artisan optimize:clear
```

### گام 11: راه‌اندازی سرور

```bash
# سرور توسعه Laravel
php artisan serve --host=0.0.0.0 --port=8000

# سرور توسعه با Queue Worker
php artisan serve & php artisan queue:work
```

**✅ نصب کامل شد!** به `http://localhost:8000/admin` بروید.

---

## 🏠 نصب روی هاست اشتراکی

نصب روی هاست‌های اشتراکی معمولی (مانند cPanel) نیاز به مراحل خاصی دارد:

### پیش‌نیازها
- ✅ دسترسی به cPanel یا مدیریت فایل
- ✅ دسترسی SSH (برای سرعت بیشتر - اختیاری)
- ✅ PHP 8.3+ فعال در هاست
- ✅ دیتابیس MySQL

### روش 1: نصب با SSH (سریع‌تر)

```bash
# 1. اتصال به SSH
ssh username@your-domain.com

# 2. رفتن به پوشه اصلی (معمولاً home یا public_html)
cd ~/public_html

# 3. آپلود فایل‌ها
# اگر Git موجود است:
git clone https://github.com/separkala-ui/zdr.git
cd zdr

# اگر Git موجود نیست:
wget https://github.com/separkala-ui/zdr/archive/main.zip
unzip main.zip
mv zdr-main zdr
cd zdr

# 4. نصب با Composer
# اگر composer موجود است:
composer install --no-dev --optimize-autoloader

# اگر composer موجود نیست:
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php composer.phar install --no-dev --optimize-autoloader

# 5. تنظیمات
cp .env.example .env
nano .env  # ویرایش و ذخیره

# 6. راه‌اندازی
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize

# 7. تنظیم دسترسی‌ها
chmod -R 755 storage bootstrap/cache
```

### روش 2: نصب بدون SSH (از طریق cPanel)

#### مرحله 1: آماده‌سازی فایل‌ها

```bash
# روی کامپیوتر شخصی خود:
git clone https://github.com/separkala-ui/zdr.git
cd zdr
composer install --no-dev --optimize-autoloader
npm install && npm run build

# فشرده‌سازی
zip -r zdr-ready.zip . -x "*.git*" "node_modules/*" "tests/*"
```

#### مرحله 2: آپلود به هاست

1. ورود به cPanel
2. File Manager → public_html
3. Upload فایل `zdr-ready.zip`
4. Extract کردن فایل

#### مرحله 3: تنظیم دیتابیس از cPanel

1. MySQL® Databases
2. Create Database: `username_zdr`
3. Create User: `username_zdr_user` با پسورد قوی
4. Add User To Database با تمام دسترسی‌ها

#### مرحله 4: تنظیم فایل .env

از طریق File Manager:
```
DB_DATABASE=username_zdr
DB_USERNAME=username_zdr_user
DB_PASSWORD=your_password
```

#### مرحله 5: اجرای دستورات اولیه

از طریق Terminal در cPanel (یا SSH):
```bash
cd public_html/zdr
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
```

### تنظیم Document Root

برای اینکه سایت از پوشه `public` لود شود:

**روش 1: تغییر Document Root در cPanel**
1. cPanel → Domains → (Select your domain)
2. Document Root را به `/public_html/zdr/public` تغییر دهید

**روش 2: ایجاد .htaccess در public_html**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ zdr/public/$1 [L]
</IfModule>
```

**روش 3: Symbolic Link**
```bash
cd ~/public_html
ln -s zdr/public zdr_public
# سپس domain را به zdr_public point کنید
```

### نکات مهم برای هاست اشتراکی

#### مدیریت Cron Jobs

برای اجرای schedule ها:
1. cPanel → Cron Jobs
2. افزودن Cron Job جدید:
```bash
* * * * * cd /home/username/public_html/zdr && php artisan schedule:run >> /dev/null 2>&1
```

#### مدیریت Queue ها

روش 1: استفاده از Cron (توصیه می‌شود):
```bash
*/5 * * * * cd /home/username/public_html/zdr && php artisan queue:work --stop-when-empty
```

روش 2: استفاده از Supervisor (اگر در دسترس باشد):
```ini
[program:zdr-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/username/public_html/zdr/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=username
numprocs=1
redirect_stderr=true
stdout_logfile=/home/username/public_html/zdr/storage/logs/worker.log
```

#### محدودیت‌های رایج و راه‌حل‌ها

**1. خطای PHP Memory Limit**
```php
// در public/index.php اضافه کنید:
ini_set('memory_limit', '256M');
```

**2. خطای Max Execution Time**
```php
// در public/index.php اضافه کنید:
ini_set('max_execution_time', '300');
```

**3. خطای Upload Size**
در `.htaccess`:
```apache
php_value upload_max_filesize 64M
php_value post_max_size 64M
```

---

## 🐳 نصب با Docker

برای توسعه سریع یا محیط ایزوله:

### ایجاد docker-compose.yml

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: zdr_app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
    networks:
      - zdr_network
    depends_on:
      - db
      - redis

  nginx:
    image: nginx:alpine
    container_name: zdr_nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www
      - ./docker/nginx:/etc/nginx/conf.d
    networks:
      - zdr_network

  db:
    image: mariadb:10.11
    container_name: zdr_db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: zdr_database
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_USER: zdr_user
      MYSQL_PASSWORD: zdr_password
    volumes:
      - zdr_dbdata:/var/lib/mysql
    networks:
      - zdr_network

  redis:
    image: redis:alpine
    container_name: zdr_redis
    restart: unless-stopped
    networks:
      - zdr_network

networks:
  zdr_network:
    driver: bridge

volumes:
  zdr_dbdata:
    driver: local
```

### ایجاد Dockerfile

```dockerfile
FROM php:8.4-fpm

# نصب وابستگی‌های سیستم
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libicu-dev \
    libzip-dev

# پاکسازی
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# نصب افزونه‌های PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip

# نصب Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تنظیم working directory
WORKDIR /var/www

# کپی فایل‌ها
COPY . /var/www

# نصب وابستگی‌ها
RUN composer install --no-interaction --optimize-autoloader --no-dev

# تنظیم دسترسی‌ها
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/storage

CMD ["php-fpm"]
```

### راه‌اندازی

```bash
# بیلد و اجرا
docker-compose up -d

# نصب وابستگی‌ها
docker-compose exec app composer install

# راه‌اندازی اولیه
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
docker-compose exec app php artisan storage:link

# مشاهده لاگ‌ها
docker-compose logs -f

# متوقف کردن
docker-compose down
```

---

## 💻 نصب روی سرورهای مختلف

### Ubuntu 22.04 / 24.04

```bash
# 1. آپدیت سیستم
sudo apt update && sudo apt upgrade -y

# 2. نصب PHP 8.4
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.4 php8.4-cli php8.4-fpm php8.4-mysql \
    php8.4-mbstring php8.4-xml php8.4-bcmath php8.4-gd \
    php8.4-curl php8.4-zip php8.4-intl php8.4-redis

# 3. نصب Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 4. نصب Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# 5. نصب MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation

# 6. نصب Nginx
sudo apt install -y nginx

# 7. نصب Redis (اختیاری)
sudo apt install -y redis-server
sudo systemctl enable redis-server

# 8. نصب Supervisor (برای Queue Worker)
sudo apt install -y supervisor

# 9. کلون پروژه
cd /var/www
sudo git clone https://github.com/separkala-ui/zdr.git
cd zdr

# 10. اجرای نصب
./scripts/install.sh --seed

# 11. تنظیم دسترسی‌ها
sudo chown -R www-data:www-data /var/www/zdr
sudo chmod -R 755 /var/www/zdr/storage
```

### CentOS / Rocky Linux 9

```bash
# 1. نصب EPEL و Remi
sudo dnf install -y epel-release
sudo dnf install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm

# 2. فعال کردن PHP 8.4
sudo dnf module reset php
sudo dnf module enable php:remi-8.4
sudo dnf install -y php php-cli php-fpm php-mysqlnd php-mbstring \
    php-xml php-bcmath php-gd php-curl php-zip php-intl php-redis

# 3. نصب Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 4. نصب Node.js
curl -fsSL https://rpm.nodesource.com/setup_20.x | sudo bash -
sudo dnf install -y nodejs

# 5. نصب MariaDB
sudo dnf install -y mariadb-server
sudo systemctl start mariadb
sudo systemctl enable mariadb
sudo mysql_secure_installation

# 6. نصب Nginx
sudo dnf install -y nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# 7. باقی مراحل مشابه Ubuntu
```

### Debian 12

```bash
# مشابه Ubuntu با تفاوت‌های جزئی
sudo apt update && sudo apt upgrade -y
sudo apt install -y lsb-release ca-certificates apt-transport-https software-properties-common

# اضافه کردن Sury repository برای PHP
curl -sSL https://packages.sury.org/php/README.txt | sudo bash -x
sudo apt update

# ادامه مشابه Ubuntu
```

### تنظیم Nginx

ایجاد فایل `/etc/nginx/sites-available/zdr`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/zdr/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # افزایش حداکثر سایز آپلود
    client_max_body_size 64M;
}
```

فعال‌سازی:
```bash
sudo ln -s /etc/nginx/sites-available/zdr /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### تنظیم Supervisor

ایجاد فایل `/etc/supervisor/conf.d/zdr-worker.conf`:

```ini
[program:zdr-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/zdr/artisan queue:work --sleep=3 --tries=3 --max-time=3600
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

فعال‌سازی:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start zdr-worker:*
```

---

## ⚙️ تنظیمات پس از نصب

### 1. ورود به پنل مدیریت

```
URL: http://your-domain.com/admin
Email: admin@example.com
Password: password
```

⚠️ **حتماً پسورد را تغییر دهید!**

### 2. تنظیمات اولیه

#### ایجاد کاربر مدیر جدید
1. ورود به پنل → کاربران → افزودن کاربر جدید
2. اختصاص نقش Superadmin
3. ذخیره اطلاعات

#### تنظیم Gemini AI برای فاکتور هوشمند
1. دریافت API Key از [Google AI Studio](https://makersuite.google.com/app/apikey)
2. پنل مدیریت → تنظیمات → فاکتور هوشمند
3. وارد کردن API Key
4. فعال‌سازی Gemini

#### تنظیمات ایمیل
1. پنل → تنظیمات → ایمیل
2. انتخاب SMTP Provider
3. وارد کردن اطلاعات

### 3. بهینه‌سازی برای Production

```bash
# 1. غیرفعال کردن Debug Mode
# در فایل .env
APP_ENV=production
APP_DEBUG=false

# 2. کش کردن تنظیمات
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. تنظیم Queue Connection
QUEUE_CONNECTION=database
# یا برای بهتر بودن
QUEUE_CONNECTION=redis

# 4. راه‌اندازی Queue Worker
php artisan queue:work --daemon

# 5. تنظیم Cron Job
crontab -e
# اضافه کردن:
* * * * * cd /var/www/zdr && php artisan schedule:run >> /dev/null 2>&1

# 6. فعال‌سازی HTTPS
# با Let's Encrypt:
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

### 4. تنظیمات امنیتی

```bash
# 1. محافظت از فایل .env
chmod 600 .env

# 2. غیرفعال کردن listing در Nginx
# در فایل nginx config اضافه کنید:
autoindex off;

# 3. محدود کردن دسترسی به فایل‌های حساس
# در .htaccess یا nginx config:
location ~ /\.env {
    deny all;
}

# 4. فعال‌سازی Firewall
sudo ufw enable
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443

# 5. نصب Fail2Ban
sudo apt install fail2ban
sudo systemctl enable fail2ban
```

---

## 🔧 عیب‌یابی

### مشکلات رایج و راه‌حل‌ها

#### 1. خطای 500 Internal Server Error

**علت‌های احتمالی:**
- مشکل در دسترسی‌های فایل
- خطا در فایل .env
- مشکل در دیتابیس

**راه‌حل:**
```bash
# بررسی لاگ‌ها
tail -f storage/logs/laravel.log

# تنظیم دسترسی‌ها
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# پاکسازی کش
php artisan optimize:clear
php artisan optimize

# بررسی تنظیمات
php artisan config:show
```

#### 2. خطای Database Connection

**راه‌حل:**
```bash
# بررسی اتصال
mysql -h DB_HOST -u DB_USERNAME -pDB_PASSWORD DB_DATABASE

# بررسی تنظیمات .env
cat .env | grep DB_

# پاکسازی کش config
php artisan config:clear

# تست اتصال
php artisan migrate:status
```

#### 3. خطای Composer Dependencies

**راه‌حل:**
```bash
# پاک کردن vendor و نصب مجدد
rm -rf vendor composer.lock
composer clear-cache
composer install

# یا
composer update --no-scripts
```

#### 4. خطای npm / Asset Compilation

**راه‌حل:**
```bash
# پاک کردن node_modules
rm -rf node_modules package-lock.json

# نصب مجدد
npm cache clean --force
npm install

# بیلد مجدد
npm run build
```

#### 5. خطای Permission Denied

**راه‌حل:**
```bash
# تنظیم owner
sudo chown -R $USER:www-data storage bootstrap/cache

# تنظیم permissions
sudo chmod -R 775 storage bootstrap/cache

# SELinux (در CentOS/RHEL)
sudo setenforce 0
sudo setsebool -P httpd_can_network_connect 1
```

#### 6. خطای Memory Limit

**راه‌حل:**
```bash
# افزایش در php.ini
memory_limit = 256M

# یا در runtime
php -d memory_limit=512M artisan migrate

# برای composer
COMPOSER_MEMORY_LIMIT=-1 composer install
```

#### 7. مشکل در آپلود فایل

**راه‌حل:**
```bash
# بررسی تنظیمات PHP
php -i | grep -E "upload_max_filesize|post_max_size"

# افزایش حد آپلود در php.ini
upload_max_filesize = 64M
post_max_size = 64M

# در Nginx
client_max_body_size 64M;

# ریستارت سرویس‌ها
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx
```

#### 8. Queue Jobs اجرا نمی‌شوند

**راه‌حل:**
```bash
# بررسی Queue Connection
php artisan queue:work --once

# مشاهده failed jobs
php artisan queue:failed

# retry کردن
php artisan queue:retry all

# پاک کردن failed jobs
php artisan queue:flush

# راه‌اندازی worker
php artisan queue:work --tries=3 --timeout=90
```

#### 9. Gemini API خطا می‌دهد

**راه‌حل:**
```bash
# بررسی API Key
php artisan tinker
>>> config('services.gemini.api_key')

# تست اتصال
curl -H "x-goog-api-key: YOUR_API_KEY" \
  https://generativelanguage.googleapis.com/v1/models

# بررسی لاگ
tail -f storage/logs/laravel.log | grep -i gemini

# افزایش timeout
SMART_INVOICE_GEMINI_TIMEOUT=120
```

### ابزارهای دیباگ

```bash
# فعال کردن Debug Mode موقت
php artisan down --with-secret="debug-token"
# سپس APP_DEBUG=true کنید و با ?secret=debug-token مشکل را بررسی کنید
php artisan up

# بررسی تنظیمات
php artisan about

# بررسی routes
php artisan route:list

# بررسی migrations
php artisan migrate:status

# بررسی permissions
php artisan permission:show

# تست ایمیل
php artisan tinker
>>> Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });
```

---

## 📊 چک‌لیست نصب

برای اطمینان از نصب صحیح:

### قبل از نصب
- [ ] PHP 8.3+ نصب شده
- [ ] تمام افزونه‌های PHP موجود است
- [ ] Composer نصب شده
- [ ] Node.js و npm نصب شده
- [ ] MySQL/MariaDB نصب و راه‌اندازی شده
- [ ] دیتابیس خالی ایجاد شده
- [ ] دسترسی SSH یا FTP موجود است

### حین نصب
- [ ] فایل‌ها با موفقیت آپلود شدند
- [ ] composer install بدون خطا اجرا شد
- [ ] npm install و build بدون خطا اجرا شد
- [ ] migrations با موفقیت اجرا شدند
- [ ] storage link ایجاد شد
- [ ] دسترسی‌های فایل تنظیم شدند

### بعد از نصب
- [ ] صفحه اصلی بارگذاری می‌شود
- [ ] ورود به پنل مدیریت موفق است
- [ ] آپلود فایل کار می‌کند
- [ ] فاکتور هوشمند فعال است (اگر نیاز است)
- [ ] Queue Worker راه‌اندازی شده
- [ ] Cron Job تنظیم شده
- [ ] HTTPS فعال است (در production)
- [ ] Backup تنظیم شده

### تست عملکرد
- [ ] ایجاد دفتر تنخواه جدید
- [ ] ثبت تراکنش
- [ ] تایید/رد تراکنش
- [ ] گزارش‌گیری
- [ ] استخراج فاکتور هوشمند
- [ ] ارسال ایمیل
- [ ] دسترسی کاربران

---

## 📞 پشتیبانی

### مستندات

- 📖 [مستندات کامل](https://github.com/separkala-ui/zdr/wiki)
- 🏗️ [معماری فاکتور هوشمند](docs/smart-invoice-architecture.md)
- 🎨 [راهنمای UI](docs/ui-guidelines.md)

### ارتباط با تیم

- 💬 **GitHub Issues**: [github.com/separkala-ui/zdr/issues](https://github.com/separkala-ui/zdr/issues)
- 📧 **Email**: support@zdr.ir
- 💬 **Telegram**: [@zdr_support](https://t.me/zdr_support)
- 🌐 **Website**: [zdr.ir](https://zdr.ir)

### گزارش باگ

هنگام گزارش مشکل، لطفاً اطلاعات زیر را ارائه دهید:

```bash
# اطلاعات سیستم
php artisan about > system-info.txt

# لاگ خطا
tail -100 storage/logs/laravel.log > error-log.txt

# نسخه‌ها
php -v > versions.txt
composer --version >> versions.txt
npm -v >> versions.txt
mysql -V >> versions.txt
```

---

## 📄 لایسنس

این پروژه تحت لایسنس [MIT](LICENSE.txt) منتشر شده است.

---

## 🙏 تشکر

از استفاده از ZDR متشکریم! اگر این پروژه برای شما مفید بوده، لطفاً:

- ⭐ به پروژه در GitHub ستاره بدهید
- 🐛 باگ‌ها را گزارش دهید
- 💡 ایده‌های خود را به اشتراک بگذارید
- 🤝 در توسعه مشارکت کنید

---

**نسخه راهنما:** 2.3.0  
**تاریخ بروزرسانی:** 26 آبان 1404  
**وضعیت:** ✅ آماده برای استفاده در محیط تولید

