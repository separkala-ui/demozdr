#!/usr/bin/env bash

################################################################################
# ZDR Shared Hosting Installation Script
# اسکریپت نصب ویژه هاست‌های اشتراکی
# نسخه: 2.3.0
# سازگار با: cPanel, DirectAdmin, Plesk
################################################################################

set -e

# رنگ‌ها
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

# پیام‌ها
print_info() { echo -e "${BLUE}ℹ${NC} $1"; }
print_success() { echo -e "${GREEN}✓${NC} $1"; }
print_warning() { echo -e "${YELLOW}⚠${NC} $1"; }
print_error() { echo -e "${RED}✗${NC} $1"; }

clear
cat << "EOF"
╔══════════════════════════════════════════════════════════════╗
║           ZDR - نصب روی هاست اشتراکی                        ║
║           Shared Hosting Installation                        ║
║           نسخه: 2.3.0                                        ║
╚══════════════════════════════════════════════════════════════╝
EOF

echo ""
print_info "این اسکریپت برای نصب روی هاست‌های اشتراکی طراحی شده است"
echo ""

# پیدا کردن مسیر اصلی پروژه
if [ -f "artisan" ]; then
    ROOT_DIR="$(pwd)"
elif [ -f "../artisan" ]; then
    ROOT_DIR="$(cd .. && pwd)"
else
    print_error "فایل artisan یافت نشد. لطفاً اسکریپت را از پوشه scripts اجرا کنید"
    exit 1
fi

cd "$ROOT_DIR"
print_success "پوشه پروژه: $ROOT_DIR"
echo ""

# بررسی PHP
print_info "بررسی PHP..."
if ! command -v php >/dev/null 2>&1; then
    print_error "PHP یافت نشد"
    exit 1
fi

PHP_VERSION=$(php -r 'echo PHP_VERSION;')
print_success "نسخه PHP: $PHP_VERSION"

# بررسی نسخه PHP
REQUIRED_VERSION="8.3.0"
if [ "$(printf '%s\n' "$REQUIRED_VERSION" "$PHP_VERSION" | sort -V | head -n1)" != "$REQUIRED_VERSION" ]; then
    print_warning "نسخه PHP باید حداقل $REQUIRED_VERSION باشد"
    echo "در cPanel می‌توانید نسخه PHP را تغییر دهید:"
    echo "  1. ورود به cPanel"
    echo "  2. Select PHP Version"
    echo "  3. انتخاب PHP 8.3 یا بالاتر"
    read -p "آیا می‌خواهید با همین نسخه ادامه دهید؟ (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi
echo ""

# بررسی Composer
print_info "بررسی Composer..."
if command -v composer >/dev/null 2>&1; then
    print_success "Composer نصب است"
    COMPOSER_CMD="composer"
elif [ -f "composer.phar" ]; then
    print_success "composer.phar موجود است"
    COMPOSER_CMD="php composer.phar"
else
    print_warning "Composer یافت نشد. در حال دانلود..."
    if curl -sS https://getcomposer.org/installer | php; then
        print_success "Composer دانلود شد"
        COMPOSER_CMD="php composer.phar"
    else
        print_error "خطا در دانلود Composer"
        exit 1
    fi
fi
echo ""

# ایجاد فایل .env
print_info "تنظیم فایل محیطی..."
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        print_success "فایل .env ایجاد شد"
        print_warning "لطفاً فایل .env را ویرایش و اطلاعات زیر را وارد کنید:"
        echo "  - DB_DATABASE (نام دیتابیس)"
        echo "  - DB_USERNAME (نام کاربری دیتابیس)"
        echo "  - DB_PASSWORD (رمز عبور دیتابیس)"
        echo "  - APP_URL (آدرس سایت شما)"
        echo ""
        read -p "برای ادامه Enter بزنید..."
    else
        print_error ".env.example یافت نشد"
        exit 1
    fi
else
    print_success "فایل .env موجود است"
fi
echo ""

# نصب وابستگی‌های PHP
print_info "نصب وابستگی‌های PHP (ممکن است چند دقیقه طول بکشد)..."
if $COMPOSER_CMD install --no-interaction --prefer-dist --optimize-autoloader --no-dev; then
    print_success "وابستگی‌ها نصب شدند"
else
    print_error "خطا در نصب وابستگی‌ها"
    print_warning "اگر خطای memory limit دریافت کردید، این دستور را اجرا کنید:"
    echo "  php -d memory_limit=512M composer.phar install --no-dev"
    exit 1
fi
echo ""

# تولید کلید
print_info "تولید کلید امنیتی..."
if php artisan key:generate --force; then
    print_success "کلید تولید شد"
else
    print_error "خطا در تولید کلید"
    exit 1
fi
echo ""

# پاکسازی کش
print_info "پاکسازی کش‌ها..."
php artisan optimize:clear >/dev/null 2>&1 || true
print_success "کش‌ها پاک شدند"
echo ""

# بررسی اتصال دیتابیس
print_info "بررسی اتصال به دیتابیس..."
if php artisan migrate:status >/dev/null 2>&1; then
    print_success "اتصال به دیتابیس موفق بود"
    echo ""
    
    # اجرای migrations
    print_info "اجرای migrations..."
    read -p "آیا می‌خواهید جداول دیتابیس ایجاد شوند؟ (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        if php artisan migrate --force; then
            print_success "جداول دیتابیس ایجاد شدند"
        else
            print_error "خطا در ایجاد جداول"
            exit 1
        fi
        
        # seeders
        echo ""
        read -p "آیا می‌خواهید داده‌های نمونه وارد شوند؟ (y/n) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            if php artisan db:seed --force; then
                print_success "داده‌های نمونه وارد شدند"
            else
                print_warning "خطا در وارد کردن داده‌های نمونه"
            fi
        fi
    fi
else
    print_error "خطا در اتصال به دیتابیس"
    echo ""
    echo "لطفاً موارد زیر را بررسی کنید:"
    echo "  1. دیتابیس در cPanel ایجاد شده است"
    echo "  2. کاربر دیتابیس ساخته و به دیتابیس متصل شده است"
    echo "  3. اطلاعات در فایل .env صحیح است"
    echo ""
    read -p "آیا می‌خواهید بدون راه‌اندازی دیتابیس ادامه دهید؟ (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi
echo ""

# ایجاد symbolic link
print_info "تنظیم storage..."
if php artisan storage:link >/dev/null 2>&1; then
    print_success "Storage link ایجاد شد"
else
    print_warning "مشکل در ایجاد storage link"
    echo "در برخی هاست‌ها نیاز به ایجاد دستی دارد:"
    echo "  ln -s $ROOT_DIR/storage/app/public $ROOT_DIR/public/storage"
fi
echo ""

# تنظیم دسترسی‌ها
print_info "تنظیم دسترسی‌های فایل..."
chmod -R 755 storage bootstrap/cache 2>/dev/null || true
print_success "دسترسی‌ها تنظیم شدند"
echo ""

# کش کردن config
print_info "بهینه‌سازی برای production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "تنظیمات بهینه شدند"
echo ""

# راهنمای تنظیم Document Root
print_warning "⚠️  نکات مهم برای راه‌اندازی نهایی:"
echo ""
echo "1️⃣  تنظیم Document Root:"
echo "   باید document root دامنه خود را به پوشه public تنظیم کنید:"
echo ""
echo "   روش 1 - از cPanel:"
echo "     - Domains → (انتخاب دامنه) → Document Root"
echo "     - تغییر به: ${ROOT_DIR}/public"
echo ""
echo "   روش 2 - ایجاد .htaccess در public_html:"
cat > "${ROOT_DIR}/.htaccess.sample" << 'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
HTACCESS
echo "     - فایل نمونه در .htaccess.sample ایجاد شد"
echo ""

echo "2️⃣  تنظیم Cron Job برای Schedule:"
echo "   در cPanel → Cron Jobs اضافه کنید:"
echo "   * * * * * cd ${ROOT_DIR} && php artisan schedule:run >> /dev/null 2>&1"
echo ""

echo "3️⃣  تنظیم Queue Worker:"
echo "   برای هاست اشتراکی بهتر است از این cron استفاده کنید:"
echo "   */5 * * * * cd ${ROOT_DIR} && php artisan queue:work --stop-when-empty"
echo ""

echo "4️⃣  افزایش حدود PHP (در صورت نیاز):"
echo "   در cPanel → Select PHP Version → Options:"
echo "     - memory_limit = 256M"
echo "     - max_execution_time = 300"
echo "     - upload_max_filesize = 64M"
echo "     - post_max_size = 64M"
echo ""

# ایجاد فایل راهنمای سریع
cat > "${ROOT_DIR}/SHARED-HOSTING-NOTES.txt" << 'NOTES'
═══════════════════════════════════════════════════════════
ZDR - یادداشت‌های هاست اشتراکی
═══════════════════════════════════════════════════════════

✅ نصب با موفقیت انجام شد!

📝 تنظیمات لازم:

1. Document Root:
   باید به پوشه public اشاره کند
   مثال: /home/username/public_html/zdr/public

2. Cron Jobs (در cPanel):
   
   Schedule Runner:
   * * * * * cd /home/username/public_html/zdr && php artisan schedule:run >> /dev/null 2>&1
   
   Queue Worker:
   */5 * * * * cd /home/username/public_html/zdr && php artisan queue:work --stop-when-empty

3. تنظیمات PHP (Select PHP Version → Options):
   - memory_limit = 256M
   - max_execution_time = 300
   - upload_max_filesize = 64M
   - post_max_size = 64M

4. SSL:
   در cPanel → SSL/TLS → فعال‌سازی AutoSSL

🔧 دستورات مفید:

پاکسازی کش:
php artisan optimize:clear

بازسازی کش:
php artisan config:cache
php artisan route:cache
php artisan view:cache

مشاهده لاگ:
tail -100 storage/logs/laravel.log

بررسی وضعیت:
php artisan about

⚠️ نکات امنیتی:

1. حتماً فایل .env را محافظت کنید:
   chmod 600 .env

2. رمز عبور ادمین پیش‌فرض را تغییر دهید

3. APP_DEBUG را در .env غیرفعال کنید:
   APP_DEBUG=false

4. APP_ENV را به production تغییر دهید:
   APP_ENV=production

📧 پشتیبانی:
   Email: support@zdr.ir
   Docs: https://github.com/separkala-ui/zdr
   Issues: https://github.com/separkala-ui/zdr/issues

NOTES

print_success "فایل راهنما در SHARED-HOSTING-NOTES.txt ذخیره شد"
echo ""

# نتیجه نهایی
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║          نصب با موفقیت تکمیل شد! 🎉                         ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════════════╝${NC}"
echo ""

print_success "✓ وابستگی‌های PHP نصب شدند"
print_success "✓ دیتابیس راه‌اندازی شد"
print_success "✓ تنظیمات امنیتی اعمال شدند"
print_success "✓ فایل‌های راهنما ایجاد شدند"
echo ""

echo "🚀 مراحل بعدی:"
echo "  1. تنظیم Document Root به پوشه public"
echo "  2. تنظیم Cron Jobs"
echo "  3. ویرایش .env و اضافه کردن کلیدهای API"
echo "  4. مشاهده SHARED-HOSTING-NOTES.txt برای جزئیات"
echo ""

echo "🌐 آدرس سایت:"
echo "  http://your-domain.com/admin"
echo ""

if grep -q "admin@example.com" .env 2>/dev/null; then
    echo "🔐 اطلاعات ورود اولیه:"
    echo "  Email: admin@example.com"
    echo "  Password: password"
    echo "  ${RED}⚠️  حتماً رمز عبور را تغییر دهید!${NC}"
    echo ""
fi

print_info "برای مشاهده راهنمای کامل: cat SHARED-HOSTING-NOTES.txt"
echo ""

