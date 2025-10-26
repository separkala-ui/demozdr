#!/usr/bin/env bash

# اسکریپت ایجاد بسته نصب افزونه زودری
# این اسکریپت یک بسته کامل و قابل نصب ایجاد می‌کند

set -euo pipefail

ROOT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )"
cd "$ROOT_DIR"

VERSION=$(git describe --tags --always --dirty 2>/dev/null || echo "dev-$(git rev-parse --short HEAD)")
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
PACKAGE_NAME="zdr-petty-cash-${VERSION}-${TIMESTAMP}"
PACKAGE_DIR="packages/${PACKAGE_NAME}"

echo "🚀 ایجاد بسته نصب افزونه زودری..."
echo "📦 نام بسته: ${PACKAGE_NAME}"
echo "📅 تاریخ: $(date)"
echo "🏷️  نسخه: ${VERSION}"
echo ""

# ایجاد دایرکتوری بسته
mkdir -p "$PACKAGE_DIR"

# کپی فایل‌های اصلی
echo "📋 کپی فایل‌های اصلی..."
cp -r app "$PACKAGE_DIR/"
cp -r config "$PACKAGE_DIR/"
cp -r database "$PACKAGE_DIR/"
cp -r resources "$PACKAGE_DIR/"
cp -r routes "$PACKAGE_DIR/"
cp -r scripts "$PACKAGE_DIR/"
cp -r tests "$PACKAGE_DIR/"

# کپی فایل‌های ریشه
cp composer.json "$PACKAGE_DIR/"
cp composer.lock "$PACKAGE_DIR/"
cp package.json "$PACKAGE_DIR/"
cp package-lock.json "$PACKAGE_DIR/"
cp artisan "$PACKAGE_DIR/"
cp server.php "$PACKAGE_DIR/"
cp webpack.mix.js "$PACKAGE_DIR/"
cp vite.config.js "$PACKAGE_DIR/"
cp phpstan.neon "$PACKAGE_DIR/"
cp phpstan-baseline.neon "$PACKAGE_DIR/"
cp pint.json "$PACKAGE_DIR/"
cp rector.php "$PACKAGE_DIR/"
cp phpunit.xml "$PACKAGE_DIR/"

# کپی فایل‌های پیکربندی
cp .env.example "$PACKAGE_DIR/"
cp .gitignore "$PACKAGE_DIR/"

# کپی مستندات
cp README.md "$PACKAGE_DIR/"
cp INSTALL.md "$PACKAGE_DIR/"
cp LICENSE.txt "$PACKAGE_DIR/"
cp package-info.json "$PACKAGE_DIR/"

# کپی فایل‌های اضافی
if [ -d "docs" ]; then
    cp -r docs "$PACKAGE_DIR/"
fi

if [ -d "modules" ]; then
    cp -r modules "$PACKAGE_DIR/"
fi

# حذف فایل‌های غیرضروری
echo "🧹 پاکسازی فایل‌های غیرضروری..."
find "$PACKAGE_DIR" -name "*.log" -delete 2>/dev/null || true
find "$PACKAGE_DIR" -name ".DS_Store" -delete 2>/dev/null || true
find "$PACKAGE_DIR" -name "Thumbs.db" -delete 2>/dev/null || true
find "$PACKAGE_DIR" -name "*.tmp" -delete 2>/dev/null || true

# حذف دایرکتوری‌های غیرضروری
rm -rf "$PACKAGE_DIR/storage/logs" 2>/dev/null || true
rm -rf "$PACKAGE_DIR/storage/framework/cache" 2>/dev/null || true
rm -rf "$PACKAGE_DIR/storage/framework/sessions" 2>/dev/null || true
rm -rf "$PACKAGE_DIR/storage/framework/views" 2>/dev/null || true
rm -rf "$PACKAGE_DIR/bootstrap/cache" 2>/dev/null || true
rm -rf "$PACKAGE_DIR/node_modules" 2>/dev/null || true
rm -rf "$PACKAGE_DIR/vendor" 2>/dev/null || true

# ایجاد فایل اطلاعات بسته
cat > "$PACKAGE_DIR/PACKAGE_INFO.txt" << EOF
بسته نصب افزونه زودری (ZDR)
================================

نام بسته: ${PACKAGE_NAME}
نسخه: ${VERSION}
تاریخ ایجاد: $(date)
تاریخچه گیت: $(git log --oneline -5 | tr '\n' '; ')

ویژگی‌های اصلی:
- سیستم مدیریت تنخواه پیشرفته
- فاکتور هوشمند با AI (Gemini/OpenAI)
- مدیریت چند شعبه
- سیستم تایید چندمرحله‌ای
- آرشیو دوره‌ای
- گزارش‌گیری کامل
- پشتیبانی از تاریخ جلالی
- رابط کاربری مدرن

پیش‌نیازها:
- PHP 8.3+
- Composer 2+
- Node.js 18+
- MySQL/MariaDB

نصب:
1. فایل‌ها را در دایرکتوری پروژه کپی کنید
2. دستورات زیر را اجرا کنید:
   chmod +x scripts/install.sh
   ./scripts/install.sh --seed

مستندات:
- راهنمای نصب: INSTALL.md
- README: README.md
- تنظیمات: config/

پشتیبانی:
- ایمیل: support@zdr.com
- تلگرام: @zdr_support
- وب‌سایت: https://zdr.com

مجوز: MIT
EOF

# ایجاد فایل checksum
echo "🔐 محاسبه checksum..."
cd "$PACKAGE_DIR"
find . -type f -exec md5sum {} \; > CHECKSUMS.md5 2>/dev/null || find . -type f -exec md5 {} \; > CHECKSUMS.md5 2>/dev/null || true
cd "$ROOT_DIR"

# ایجاد آرشیو
echo "📦 ایجاد آرشیو..."
cd packages
tar -czf "${PACKAGE_NAME}.tar.gz" "$PACKAGE_NAME"
zip -r "${PACKAGE_NAME}.zip" "$PACKAGE_NAME" >/dev/null 2>&1 || true
cd "$ROOT_DIR"

# نمایش نتایج
echo ""
echo "✅ بسته نصب با موفقیت ایجاد شد!"
echo ""
echo "📁 مکان فایل‌ها:"
echo "   - دایرکتوری: packages/${PACKAGE_NAME}/"
echo "   - آرشیو tar.gz: packages/${PACKAGE_NAME}.tar.gz"
echo "   - آرشیو zip: packages/${PACKAGE_NAME}.zip"
echo ""
echo "📊 آمار بسته:"
echo "   - تعداد فایل‌ها: $(find packages/${PACKAGE_NAME} -type f | wc -l)"
echo "   - حجم دایرکتوری: $(du -sh packages/${PACKAGE_NAME} | cut -f1)"
echo "   - حجم آرشیو tar.gz: $(du -sh packages/${PACKAGE_NAME}.tar.gz | cut -f1)"
echo ""
echo "🚀 آماده برای توزیع!"
