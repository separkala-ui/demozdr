#!/usr/bin/env bash

################################################################################
# ZDR Enhanced Installation Script
# نسخه: 2.3.0
# سازگار با: Ubuntu, Debian, CentOS, Rocky Linux, و هاست‌های اشتراکی
################################################################################

set -euo pipefail

# رنگ‌ها برای خروجی
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# تشخیص مسیر اصلی پروژه
ROOT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )"
cd "$ROOT_DIR"

# متغیرهای پیش‌فرض
SEED_DATABASE=false
SKIP_FRONTEND=false
PRODUCTION_MODE=false
INTERACTIVE=true
SKIP_DB_SETUP=false
QUICK_MODE=false

# تابع نمایش پیام با رنگ
print_status() {
    echo -e "${BLUE}[$(date +'%H:%M:%S')]${NC} $1"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1" >&2
}

print_header() {
    echo ""
    echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${GREEN}  $1${NC}"
    echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
    echo ""
}

# تجزیه آرگومان‌های ورودی
for arg in "$@"; do
    case "$arg" in
        --seed)
            SEED_DATABASE=true
            shift
            ;;
        --no-build)
            SKIP_FRONTEND=true
            shift
            ;;
        --production)
            PRODUCTION_MODE=true
            shift
            ;;
        --non-interactive)
            INTERACTIVE=false
            shift
            ;;
        --skip-db)
            SKIP_DB_SETUP=true
            shift
            ;;
        --quick)
            QUICK_MODE=true
            shift
            ;;
        --help|-h)
            cat <<'USAGE'
┌────────────────────────────────────────────────────────────┐
│  ZDR - اسکریپت نصب پیشرفته                                 │
│  نسخه: 2.3.0                                               │
└────────────────────────────────────────────────────────────┘

استفاده:
  ./scripts/install-enhanced.sh [گزینه‌ها]

گزینه‌های پایه:
  --seed              اجرای seeder برای داده‌های نمونه
  --no-build          عدم نصب و بیلد فرانت‌اند (npm)
  --production        نصب در حالت production (بدون dev dependencies)
  --non-interactive   اجرای خودکار بدون سوال از کاربر
  --skip-db           عدم اجرای migrations (اگر دیتابیس از قبل آماده است)
  --quick             نصب سریع (بدون بررسی‌های کامل)
  --help, -h          نمایش این راهنما

مثال‌ها:
  # نصب استاندارد با داده‌های نمونه
  ./scripts/install-enhanced.sh --seed

  # نصب برای production
  ./scripts/install-enhanced.sh --production --no-build

  # نصب سریع بدون تعامل
  ./scripts/install-enhanced.sh --quick --non-interactive

  # نصب روی هاست اشتراکی
  ./scripts/install-enhanced.sh --no-build --skip-db

پشتیبانی:
  📧 Email: support@zdr.ir
  🌐 Docs: https://github.com/separkala-ui/zdr
  💬 Issues: https://github.com/separkala-ui/zdr/issues

USAGE
            exit 0
            ;;
        *)
            print_error "گزینه ناشناخته: $arg"
            echo "برای مشاهده راهنما از --help استفاده کنید"
            exit 1
            ;;
    esac
done

# نمایش هدر
clear
cat << "EOF"
╔══════════════════════════════════════════════════════════════╗
║                                                              ║
║   ███████╗██████╗ ██████╗                                   ║
║   ╚══███╔╝██╔══██╗██╔══██╗                                  ║
║     ███╔╝ ██║  ██║██████╔╝                                  ║
║    ███╔╝  ██║  ██║██╔══██╗                                  ║
║   ███████╗██████╔╝██║  ██║                                  ║
║   ╚══════╝╚═════╝ ╚═╝  ╚═╝                                  ║
║                                                              ║
║   سیستم مدیریت تنخواه و فاکتور هوشمند                     ║
║   نسخه: 2.3.0                                               ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝
EOF

print_status "شروع فرآیند نصب..."
sleep 1

# تابع بررسی وجود دستور
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# تابع بررسی نسخه PHP
check_php_version() {
    local php_version=$(php -r 'echo PHP_VERSION;')
    local required_version="8.3.0"
    
    if [ "$(printf '%s\n' "$required_version" "$php_version" | sort -V | head -n1)" = "$required_version" ]; then
        print_success "نسخه PHP: $php_version"
        return 0
    else
        print_error "نسخه PHP باید حداقل $required_version باشد (نسخه فعلی: $php_version)"
        return 1
    fi
}

# تابع بررسی افزونه‌های PHP
check_php_extensions() {
    local required_extensions=("pdo" "pdo_mysql" "mbstring" "xml" "gd" "curl" "openssl" "intl" "fileinfo" "tokenizer" "bcmath")
    local missing_extensions=()
    
    for ext in "${required_extensions[@]}"; do
        if ! php -m | grep -q "^$ext$"; then
            missing_extensions+=("$ext")
        fi
    done
    
    if [ ${#missing_extensions[@]} -eq 0 ]; then
        print_success "تمام افزونه‌های PHP موجود هستند"
        return 0
    else
        print_error "افزونه‌های PHP زیر یافت نشدند:"
        for ext in "${missing_extensions[@]}"; do
            echo "  - $ext"
        done
        return 1
    fi
}

# بررسی پیش‌نیازها
print_header "بررسی پیش‌نیازها"

print_status "بررسی دستورات مورد نیاز..."

# بررسی PHP
if ! command_exists php; then
    print_error "PHP روی سیستم نصب نیست"
    echo "برای نصب PHP به راهنمای نصب مراجعه کنید: INSTALLATION-GUIDE.md"
    exit 1
fi
check_php_version || exit 1

# بررسی افزونه‌های PHP
if [ "$QUICK_MODE" = false ]; then
    check_php_extensions || {
        print_warning "برخی افزونه‌های PHP یافت نشدند. ادامه می‌دهیم اما ممکن است مشکل پیش بیاید."
        if [ "$INTERACTIVE" = true ]; then
            read -p "آیا می‌خواهید ادامه دهید؟ (y/n) " -n 1 -r
            echo
            if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                exit 1
            fi
        fi
    }
fi

# بررسی Composer
if ! command_exists composer; then
    print_error "Composer روی سیستم نصب نیست"
    echo ""
    echo "برای نصب Composer:"
    echo "  curl -sS https://getcomposer.org/installer | php"
    echo "  sudo mv composer.phar /usr/local/bin/composer"
    exit 1
fi
print_success "Composer نصب است: $(composer --version --no-ansi | head -n1)"

# بررسی Node.js و npm
if [ "$SKIP_FRONTEND" = false ]; then
    if ! command_exists node; then
        print_warning "Node.js یافت نشد"
        if [ "$INTERACTIVE" = true ]; then
            read -p "بدون Node.js ادامه می‌دهیم؟ (y/n) " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                SKIP_FRONTEND=true
            else
                exit 1
            fi
        else
            print_status "به صورت خودکار --no-build فعال شد"
            SKIP_FRONTEND=true
        fi
    else
        print_success "Node.js نصب است: $(node --version)"
    fi
    
    if [ "$SKIP_FRONTEND" = false ] && ! command_exists npm; then
        print_error "npm یافت نشد ولی Node.js موجود است"
        exit 1
    fi
fi

# آماده‌سازی محیط
print_header "آماده‌سازی محیط"

# بررسی/ایجاد فایل .env
print_status "بررسی فایل .env..."
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        print_success "فایل .env از .env.example ایجاد شد"
        
        if [ "$PRODUCTION_MODE" = true ]; then
            print_status "تنظیم حالت production در .env..."
            sed -i 's/APP_ENV=.*/APP_ENV=production/' .env
            sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env
            print_success "حالت production فعال شد"
        fi
        
        print_warning "لطفاً فایل .env را ویرایش کنید و اطلاعات دیتابیس را وارد نمایید"
        if [ "$INTERACTIVE" = true ]; then
            read -p "آیا می‌خواهید الان .env را ویرایش کنید؟ (y/n) " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                ${EDITOR:-nano} .env
            fi
        fi
    else
        print_error "فایل .env.example یافت نشد"
        exit 1
    fi
else
    print_success "فایل .env موجود است"
fi

# نصب وابستگی‌های PHP
print_header "نصب وابستگی‌های PHP"

COMPOSER_PARAMS="--no-interaction --prefer-dist --optimize-autoloader"
if [ "$PRODUCTION_MODE" = true ]; then
    COMPOSER_PARAMS="$COMPOSER_PARAMS --no-dev"
    print_status "نصب در حالت production (بدون dev dependencies)"
fi

print_status "اجرای composer install..."
if composer install $COMPOSER_PARAMS; then
    print_success "وابستگی‌های PHP با موفقیت نصب شدند"
else
    print_error "خطا در نصب وابستگی‌های PHP"
    exit 1
fi

# تولید کلید برنامه
print_header "تنظیمات امنیتی"

print_status "تولید APP_KEY..."
if php artisan key:generate --ansi --force; then
    print_success "کلید برنامه با موفقیت تولید شد"
else
    print_error "خطا در تولید کلید"
    exit 1
fi

# پاکسازی اولیه
print_status "پاکسازی کش‌های قبلی..."
php artisan optimize:clear >/dev/null 2>&1 || true
print_success "کش‌ها پاک شدند"

# راه‌اندازی دیتابیس
if [ "$SKIP_DB_SETUP" = false ]; then
    print_header "راه‌اندازی دیتابیس"
    
    print_status "بررسی اتصال به دیتابیس..."
    if php artisan migrate:status >/dev/null 2>&1; then
        print_success "اتصال به دیتابیس برقرار است"
    else
        print_error "خطا در اتصال به دیتابیس"
        echo ""
        echo "لطفاً موارد زیر را بررسی کنید:"
        echo "  1. اطلاعات دیتابیس در فایل .env صحیح است"
        echo "  2. سرویس MySQL/MariaDB در حال اجراست"
        echo "  3. دیتابیس مشخص شده ایجاد شده است"
        echo "  4. کاربر دیتابیس دسترسی لازم را دارد"
        echo ""
        if [ "$INTERACTIVE" = true ]; then
            read -p "آیا می‌خواهید بدون migrations ادامه دهید؟ (y/n) " -n 1 -r
            echo
            if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                exit 1
            fi
            SKIP_DB_SETUP=true
        else
            exit 1
        fi
    fi
    
    if [ "$SKIP_DB_SETUP" = false ]; then
        print_status "اجرای migrations..."
        if php artisan migrate --force; then
            print_success "جداول دیتابیس با موفقیت ایجاد شدند"
        else
            print_error "خطا در اجرای migrations"
            exit 1
        fi
        
        if [ "$SEED_DATABASE" = true ]; then
            print_status "اجرای seeders..."
            if php artisan db:seed --force; then
                print_success "داده‌های نمونه با موفقیت وارد شدند"
            else
                print_warning "خطا در اجرای seeders (غیرضروری)"
            fi
        fi
    fi
fi

# ساخت symbolic link برای storage
print_header "تنظیم Storage"

print_status "ایجاد symbolic link برای storage..."
if php artisan storage:link >/dev/null 2>&1; then
    print_success "Link storage ایجاد شد"
else
    print_warning "Link storage قبلاً ایجاد شده بود یا خطا رخ داد"
fi

# تنظیم دسترسی‌ها
print_status "تنظیم دسترسی‌های فایل..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || chmod -R 755 storage bootstrap/cache
print_success "دسترسی‌ها تنظیم شدند"

# اگر www-data موجود است، owner را تنظیم کن
if id "www-data" &>/dev/null; then
    if chown -R www-data:www-data storage bootstrap/cache 2>/dev/null; then
        print_success "Owner فایل‌ها به www-data تنظیم شد"
    else
        print_warning "نتوانستیم owner را تغییر دهیم (نیاز به sudo دارید)"
    fi
fi

# نصب و بیلد فرانت‌اند
if [ "$SKIP_FRONTEND" = false ]; then
    print_header "نصب و بیلد فرانت‌اند"
    
    print_status "نصب وابستگی‌های npm (ممکن است چند دقیقه طول بکشد)..."
    if npm install --no-audit --no-fund; then
        print_success "وابستگی‌های npm نصب شدند"
    else
        print_error "خطا در نصب npm packages"
        exit 1
    fi
    
    print_status "بیلد دارایی‌های فرانت‌اند..."
    if npm run build; then
        print_success "بیلد فرانت‌اند با موفقیت انجام شد"
    else
        print_error "خطا در بیلد فرانت‌اند"
        exit 1
    fi
else
    print_warning "نصب و بیلد فرانت‌اند رد شد"
fi

# بهینه‌سازی نهایی
print_header "بهینه‌سازی و تمیزکاری"

if [ "$PRODUCTION_MODE" = true ]; then
    print_status "کش کردن تنظیمات برای production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    print_success "تنظیمات کش شدند"
else
    print_status "اجرای optimize..."
    php artisan optimize >/dev/null 2>&1 || true
    print_success "بهینه‌سازی انجام شد"
fi

# نمایش گزارش نهایی
print_header "نصب با موفقیت تکمیل شد! 🎉"

echo ""
echo -e "${GREEN}═══════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  نصب با موفقیت انجام شد!${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════${NC}"
echo ""

echo "📊 خلاصه نصب:"
echo "  ✓ وابستگی‌های PHP نصب شدند"
if [ "$SKIP_DB_SETUP" = false ]; then
    echo "  ✓ دیتابیس راه‌اندازی شد"
    if [ "$SEED_DATABASE" = true ]; then
        echo "  ✓ داده‌های نمونه وارد شدند"
    fi
fi
echo "  ✓ Storage پیکربندی شد"
if [ "$SKIP_FRONTEND" = false ]; then
    echo "  ✓ فرانت‌اند بیلد شد"
fi
echo ""

echo "🚀 گام‌های بعدی:"
echo ""
echo "1. ویرایش فایل .env و تنظیم کلیدهای API:"
echo "   ${YELLOW}nano .env${NC}"
echo ""
echo "2. راه‌اندازی سرور:"
if [ "$PRODUCTION_MODE" = false ]; then
    echo "   ${YELLOW}php artisan serve${NC}"
    echo ""
    echo "3. مشاهده سایت:"
    echo "   ${BLUE}http://localhost:8000${NC}"
    echo ""
    echo "4. ورود به پنل مدیریت:"
    echo "   ${BLUE}http://localhost:8000/admin${NC}"
    if [ "$SEED_DATABASE" = true ]; then
        echo "   Email: ${YELLOW}admin@example.com${NC}"
        echo "   Password: ${YELLOW}password${NC}"
    fi
else
    echo "   - تنظیم Nginx/Apache"
    echo "   - راه‌اندازی Queue Worker"
    echo "   - تنظیم Cron Job"
    echo ""
    echo "   برای جزئیات بیشتر: ${BLUE}INSTALLATION-GUIDE.md${NC}"
fi
echo ""

echo "📚 مستندات مفید:"
echo "   - راهنمای کامل نصب: ${BLUE}INSTALLATION-GUIDE.md${NC}"
echo "   - معماری فاکتور هوشمند: ${BLUE}docs/smart-invoice-architecture.md${NC}"
echo "   - تنظیمات تنخواه: ${BLUE}config/petty-cash.php${NC}"
echo ""

if [ "$PRODUCTION_MODE" = true ]; then
    echo "⚠️  نکات امنیتی برای Production:"
    echo "   - فایل .env را محافظت کنید (chmod 600)"
    echo "   - HTTPS را فعال کنید"
    echo "   - Firewall را تنظیم کنید"
    echo "   - Backup منظم راه‌اندازی کنید"
    echo ""
fi

echo "💡 نکات:"
echo "   - برای راه‌اندازی Queue Worker: ${YELLOW}php artisan queue:work${NC}"
echo "   - برای پاکسازی کش‌ها: ${YELLOW}php artisan optimize:clear${NC}"
echo "   - برای مشاهده لاگ‌ها: ${YELLOW}tail -f storage/logs/laravel.log${NC}"
echo ""

echo "📞 پشتیبانی:"
echo "   📧 Email: support@zdr.ir"
echo "   🌐 Docs: https://github.com/separkala-ui/zdr"
echo "   💬 Issues: https://github.com/separkala-ui/zdr/issues"
echo ""

print_success "همه چیز آماده است! 🎊"
echo ""

