# 📦 بسته آسان نصب ZDR

## 📄 فایل‌های ایجاد شده

این بسته شامل تمام فایل‌ها و مستندات لازم برای نصب آسان در محیط‌های مختلف است:

### 📚 مستندات

| فایل | حجم | توضیح |
|------|-----|--------|
| `README.md` | 26K | مستندات اصلی پروژه |
| `INSTALLATION-GUIDE.md` | 26K | راهنمای کامل نصب |
| `QUICK-START.md` | 6.0K | شروع سریع در 10 دقیقه |
| `DEPLOYMENT.md` | 16K | راهنمای استقرار Production |
| `INSTALL.md` | 3.7K | راهنمای اولیه |

### 🛠 اسکریپت‌های نصب

| اسکریپت | حجم | کاربرد |
|---------|-----|---------|
| `scripts/install-enhanced.sh` | 19K | نصب پیشرفته با تمام گزینه‌ها |
| `scripts/install-shared-hosting.sh` | 13K | نصب ویژه هاست اشتراکی |
| `scripts/install.sh` | 3.8K | نصب ساده و سریع |

### 🐳 Docker

| فایل | حجم | توضیح |
|------|-----|--------|
| `docker-compose.yml` | 4.3K | تنظیمات Docker Compose |
| `docker/Dockerfile` | - | تصویر Docker |
| `docker/nginx/default.conf` | - | تنظیمات Nginx |
| `docker/php/php.ini` | - | تنظیمات PHP |

---

## 🚀 راهنمای سریع استفاده

### برای توسعه‌دهندگان

```bash
# کلون پروژه
git clone https://github.com/separkala-ui/zdr.git
cd zdr

# نصب خودکار
./scripts/install-enhanced.sh --seed

# اجرا
php artisan serve
```

### برای هاست اشتراکی

```bash
# آپلود فایل‌ها به هاست
# سپس در Terminal:
cd public_html/zdr
./scripts/install-shared-hosting.sh
```

### با Docker

```bash
# کلون و راه‌اندازی
git clone https://github.com/separkala-ui/zdr.git
cd zdr
docker-compose up -d
docker-compose exec app php artisan migrate --seed
```

---

## 📋 ویژگی‌های بسته نصب

### ✅ پشتیبانی از محیط‌های مختلف

- ✅ **سرورهای Linux** (Ubuntu, Debian, CentOS, Rocky Linux)
- ✅ **macOS** (Intel & Apple Silicon)
- ✅ **Windows** (WSL2)
- ✅ **Docker** (تمام پلتفرم‌ها)
- ✅ **هاست اشتراکی** (cPanel, DirectAdmin, Plesk)

### ✅ نصب هوشمند

- بررسی خودکار پیش‌نیازها
- تشخیص سیستم‌عامل
- نصب وابستگی‌های لازم
- تنظیم خودکار دسترسی‌ها
- گزارش‌دهی دقیق در هر مرحله

### ✅ گزینه‌های پیشرفته

```bash
# نصب با داده‌های نمونه
./scripts/install-enhanced.sh --seed

# نصب بدون فرانت‌اند
./scripts/install-enhanced.sh --no-build

# نصب برای production
./scripts/install-enhanced.sh --production

# نصب بدون تعامل (CI/CD)
./scripts/install-enhanced.sh --non-interactive

# نصب سریع
./scripts/install-enhanced.sh --quick
```

---

## 🔍 بررسی اجزای بسته

### اسکریپت نصب پیشرفته

```bash
scripts/install-enhanced.sh
```

**قابلیت‌ها:**
- ✅ رنگ‌آمیزی خروجی برای خوانایی بهتر
- ✅ بررسی کامل پیش‌نیازها
- ✅ تشخیص نسخه PHP و افزونه‌ها
- ✅ نصب Composer در صورت نبود
- ✅ تنظیم خودکار .env
- ✅ مدیریت خطاها
- ✅ گزارش‌دهی جامع

### اسکریپت هاست اشتراکی

```bash
scripts/install-shared-hosting.sh
```

**مناسب برای:**
- هاست‌های cPanel
- هاست‌های DirectAdmin
- هاست‌های Plesk
- هاست‌های بدون SSH
- محیط‌های با محدودیت

**ویژگی‌ها:**
- نصب Composer در صورت نبود
- راهنمای تنظیم Document Root
- راهنمای Cron Job
- راهنمای Queue Worker
- نکات امنیتی

### Docker Compose

```yaml
docker-compose.yml
```

**سرویس‌ها:**
- **app:** PHP-FPM Application
- **nginx:** Web Server
- **db:** MariaDB Database
- **redis:** Cache & Queue
- **queue:** Background Jobs
- **scheduler:** Cron Jobs
- **node:** Frontend Development (optional)
- **phpmyadmin:** Database Management (optional)
- **mailhog:** Email Testing (optional)

---

## 📖 مستندات تکمیلی

### INSTALLATION-GUIDE.md (26K)

**شامل:**
- ✅ پیش‌نیازهای دقیق
- ✅ نصب گام به گام
- ✅ نصب روی هاست اشتراکی (جزئیات کامل)
- ✅ نصب با Docker (توضیحات کامل)
- ✅ نصب روی Ubuntu/Debian/CentOS
- ✅ تنظیمات Nginx/Apache
- ✅ تنظیمات PHP-FPM
- ✅ عیب‌یابی (50+ مشکل رایج)
- ✅ چک‌لیست نصب

### QUICK-START.md (6K)

**برای:**
- نصب سریع در 10 دقیقه
- توسعه‌دهندگان مبتدی
- تست سریع پروژه

### DEPLOYMENT.md (16K)

**شامل:**
- ✅ آماده‌سازی سرور
- ✅ تنظیمات Nginx پیشرفته
- ✅ SSL با Let's Encrypt
- ✅ Firewall و امنیت
- ✅ بهینه‌سازی PHP-FPM
- ✅ تنظیم Redis
- ✅ Queue Worker با Supervisor
- ✅ Monitoring
- ✅ Backup خودکار
- ✅ CI/CD با GitHub Actions

---

## 💡 نکات مهم

### قبل از نصب

1. **بررسی پیش‌نیازها:**
   ```bash
   php -v  # باید 8.3+
   composer -V  # باید 2.0+
   node -v  # باید 18+ (اختیاری)
   ```

2. **آماده‌سازی دیتابیس:**
   ```sql
   CREATE DATABASE zdr_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'zdr_user'@'localhost' IDENTIFIED BY 'password';
   GRANT ALL ON zdr_database.* TO 'zdr_user'@'localhost';
   ```

3. **دریافت Gemini API Key:**
   - https://makersuite.google.com/app/apikey

### حین نصب

- دستورات را با دقت دنبال کنید
- در صورت خطا، لاگ‌ها را بررسی کنید
- از اتصال اینترنت پایدار استفاده کنید

### بعد از نصب

1. **امنیت:**
   - رمز admin را تغییر دهید
   - فایل .env را محافظت کنید (chmod 600)
   - APP_DEBUG را false کنید (production)

2. **بهینه‌سازی:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Backup:**
   - تنظیم backup خودکار
   - تست بازیابی

---

## 🆘 پشتیبانی

### مشکل در نصب؟

1. **بررسی مستندات:**
   - INSTALLATION-GUIDE.md → بخش عیب‌یابی
   - QUICK-START.md → مشکلات رایج

2. **بررسی لاگ‌ها:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **اجرای تست:**
   ```bash
   php artisan about
   php artisan migrate:status
   ```

4. **درخواست کمک:**
   - GitHub Issues: https://github.com/separkala-ui/zdr/issues
   - Email: support@zdr.ir
   - Telegram: @zdr_support

---

## ✅ چک‌لیست نصب موفق

- [ ] پیش‌نیازها نصب شده‌اند
- [ ] اسکریپت نصب بدون خطا اجرا شد
- [ ] دیتابیس متصل است
- [ ] صفحه اصلی لود می‌شود
- [ ] ورود به admin موفق است
- [ ] آپلود فایل کار می‌کند
- [ ] فاکتور هوشمند تست شد
- [ ] Queue Worker فعال است
- [ ] Cron Job تنظیم شده

---

## 📊 آمار بسته

- **تعداد فایل‌های مستندات:** 5+
- **تعداد اسکریپت‌های نصب:** 3
- **تعداد راهنمای محیط:** 4+
- **تعداد مثال‌های کد:** 100+
- **تعداد دستورات:** 200+
- **خطوط کد مستندات:** 2600+

---

## 🎯 نتیجه‌گیری

این بسته آسان نصب طراحی شده تا:

✅ **نصب را ساده کند** - با 1 دستور  
✅ **خطاها را کاهش دهد** - با بررسی خودکار  
✅ **زمان را صرفه‌جویی کند** - نصب در کمتر از 10 دقیقه  
✅ **از محیط‌های مختلف پشتیبانی کند** - Linux, Mac, Windows, Docker, Shared Hosting  
✅ **مستندات جامع ارائه دهد** - 2600+ خط مستندات فارسی  

---

**نسخه بسته:** 2.3.0  
**تاریخ انتشار:** 26 آبان 1404  
**سازنده:** تیم ZDR  
**لایسنس:** MIT  

**موفق باشید! 🎉**

