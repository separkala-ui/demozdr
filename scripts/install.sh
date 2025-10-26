#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )"
cd "$ROOT_DIR"

SEED_DATABASE=false
SKIP_FRONTEND=false

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
        --help|-h)
            cat <<'USAGE'
استفاده:
  ./scripts/install.sh [گزینه‌ها]

گزینه‌ها:
  --seed       اجرای php artisan db:seed پس از مهاجرت دیتابیس
  --no-build   صرف‌نظر از نصب و بیلد فرانت‌اند (npm install / npm run build)
  --help       نمایش همین توضیحات

USAGE
            exit 0
            ;;
        *)
            echo "گزینه ناشناخته: $arg"
            exit 1
            ;;
    esac
done

function ensure_command() {
    if ! command -v "$1" >/dev/null 2>&1; then
        echo "✗ ابزار '$1' روی سیستم پیدا نشد. لطفاً ابتدا آن را نصب کنید." >&2
        exit 1
    fi
    echo "✓ ابزار '$1' موجود است."
}

echo "🔍 بررسی پیش‌نیازها..."
ensure_command php
ensure_command composer

if [ "$SKIP_FRONTEND" = false ]; then
    ensure_command npm
else
    echo "⚠️  به‌درخواست شما نصب/بیلد فرانت‌اند انجام نمی‌شود."
fi

if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo "🆕 فایل .env ایجاد شد (بر اساس .env.example). در صورت نیاز مقادیر را اصلاح کنید."
    else
        echo "❗ فایل .env.example یافت نشد. لطفاً فایل پیکربندی را به‌صورت دستی بسازید." >&2
        exit 1
    fi
else
    echo "ℹ️  فایل .env از قبل وجود دارد."
fi

echo "📦 اجرای composer install ..."
composer install --no-interaction --prefer-dist

echo "🔐 تولید کلید برنامه ..."
php artisan key:generate --ansi --force

echo "🧹 پاکسازی و بهینه‌سازی اولیه ..."
php artisan optimize:clear

echo "🗄  اجرای مهاجرت‌های پایگاه داده ..."
php artisan migrate --force

if [ "$SEED_DATABASE" = true ]; then
    echo "🌱 اجرای seeder ها ..."
    php artisan db:seed --force
fi

echo "🔗 ساخت symbolic link برای storage ..."
php artisan storage:link >/dev/null 2>&1 || true

if [ "$SKIP_FRONTEND" = false ]; then
    echo "📦 نصب وابستگی‌های فرانت‌اند ..."
    npm install

    echo "🏗  بیلد دارایی‌های فرانت‌اند ..."
    npm run build
fi

echo "✅ نصب و پیکربندی اولیه با موفقیت پایان یافت."
echo ""
echo "گام بعدی:"
echo "  - در صورت نیاز فایل .env را ویرایش و مقادیر سرویس‌ها را تنظیم کنید."
echo "  - وب‌سرور یا php artisan serve را اجرا کنید."
