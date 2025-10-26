# 🚀 راهنمای شروع سریع ZDR

> این راهنما برای کسانی است که می‌خواهند در کمتر از 10 دقیقه پروژه را راه‌اندازی کنند.

---

## 📦 پیش‌نیازها

✅ PHP 8.3+  
✅ Composer  
✅ MySQL/MariaDB  
✅ Node.js 18+ (اختیاری برای فرانت‌اند)

---

## ⚡ نصب در 3 مرحله

### 1️⃣ دریافت پروژه

```bash
git clone https://github.com/separkala-ui/zdr.git
cd zdr
```

### 2️⃣ نصب خودکار

```bash
chmod +x scripts/install-enhanced.sh
./scripts/install-enhanced.sh --seed
```

### 3️⃣ راه‌اندازی

```bash
php artisan serve
```

🎉 **تمام!** به `http://localhost:8000/admin` بروید

---

## 🔐 اطلاعات ورود پیش‌فرض

```
Email: admin@example.com
Password: password
```

⚠️ **حتماً پس از ورود اول رمز را تغییر دهید!**

---

## 🏠 نصب روی هاست اشتراکی

### روش 1: با SSH

```bash
ssh username@your-domain.com
cd public_html
git clone https://github.com/separkala-ui/zdr.git
cd zdr
./scripts/install-shared-hosting.sh
```

### روش 2: بدون SSH

1. دانلود پروژه به صورت ZIP
2. آپلود به هاست از طریق cPanel File Manager
3. Extract کردن
4. اجرای Terminal در cPanel:
```bash
cd public_html/zdr
bash scripts/install-shared-hosting.sh
```

### تنظیمات لازم در cPanel

**Document Root:**
```
public_html/zdr/public
```

**Cron Job:**
```bash
* * * * * cd /home/username/public_html/zdr && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🐳 نصب با Docker

```bash
git clone https://github.com/separkala-ui/zdr.git
cd zdr

# کپی .env
cp .env.example .env

# بیلد و اجرا
docker-compose up -d

# نصب
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed

# مشاهده سایت
open http://localhost
```

---

## ⚙️ تنظیم Gemini AI (فاکتور هوشمند)

### گام 1: دریافت API Key

1. برو به: https://makersuite.google.com/app/apikey
2. "Create API Key" کلیک کن
3. کلید را کپی کن

### گام 2: تنظیم در .env

```env
SMART_INVOICE_GEMINI_ENABLED=true
SMART_INVOICE_GEMINI_API_KEY=your_key_here
```

### گام 3: پاکسازی کش

```bash
php artisan config:clear
php artisan optimize
```

✅ **آماده است!** حالا می‌توانید فاکتورها را هوشمند پردازش کنید.

---

## 🔧 دستورات مفید

### پاکسازی کش
```bash
php artisan optimize:clear
```

### بازسازی کش (Production)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### مشاهده لاگ‌ها
```bash
tail -f storage/logs/laravel.log
```

### اجرای Queue Worker
```bash
php artisan queue:work
```

### اجرای تست‌ها
```bash
php artisan test
```

---

## 🆘 مشکلات رایج

### خطای Database Connection

**راه‌حل:**
1. دیتابیس را در MySQL ایجاد کنید:
```sql
CREATE DATABASE zdr_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. اطلاعات `.env` را بررسی کنید
3. کش config را پاک کنید:
```bash
php artisan config:clear
```

### خطای Permission Denied

**راه‌حل:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### خطای 500 Internal Server

**راه‌حل:**
```bash
# مشاهده لاگ
tail -100 storage/logs/laravel.log

# پاکسازی کش
php artisan optimize:clear
```

### نمایش داده نمی‌شود (صفحه خالی)

**راه‌حل:**
```bash
# بیلد مجدد فرانت‌اند
npm install
npm run build
```

---

## 📊 بررسی سلامت سیستم

```bash
php artisan about
```

این دستور اطلاعات کامل سیستم را نمایش می‌دهد.

---

## 📚 مستندات کامل

| مستند | توضیح |
|-------|--------|
| [INSTALLATION-GUIDE.md](INSTALLATION-GUIDE.md) | راهنمای کامل نصب |
| [docs/smart-invoice-architecture.md](docs/smart-invoice-architecture.md) | معماری فاکتور هوشمند |
| [config/petty-cash.php](config/petty-cash.php) | تنظیمات تنخواه |

---

## 🎯 مراحل بعدی

پس از نصب موفق:

1. ✅ **امنیت**: رمز عبور admin را تغییر دهید
2. ✅ **تنظیمات**: به Settings → General بروید و اطلاعات سازمان را وارد کنید
3. ✅ **کاربران**: کاربران جدید ایجاد کنید و نقش‌ها را تنظیم کنید
4. ✅ **دفتر تنخواه**: اولین دفتر تنخواه را ایجاد کنید
5. ✅ **فاکتور هوشمند**: Gemini AI را تنظیم و تست کنید
6. ✅ **Backup**: سیستم backup خودکار راه‌اندازی کنید

---

## 💡 نکات Pro

### افزایش سرعت

```bash
# استفاده از Redis
composer require predis/predis
```

در `.env`:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### بهینه‌سازی تصاویر

```bash
# نصب image optimizer
sudo apt install jpegoptim optipng pngquant gifsicle
```

### مانیتورینگ

- **Telescope**: `http://localhost:8000/telescope`
- **Pulse**: `http://localhost:8000/pulse`
- **Logs**: `storage/logs/laravel.log`

---

## 📞 کمک نیاز دارید؟

- 📧 **Email**: support@zdr.ir
- 💬 **Issues**: [GitHub Issues](https://github.com/separkala-ui/zdr/issues)
- 📚 **Docs**: [مستندات کامل](https://github.com/separkala-ui/zdr/wiki)

---

## 🌟 پروژه را دوست داشتید؟

⭐ به پروژه در GitHub ستاره بدهید  
🐛 باگ پیدا کردید؟ گزارش دهید  
💡 ایده دارید؟ به اشتراک بگذارید  
🤝 می‌خواهید مشارکت کنید؟ PR بفرستید

---

**موفق باشید! 🎉**

