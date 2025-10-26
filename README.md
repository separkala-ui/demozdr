<div align="center">

# 🌟 ZDR - سیستم مدیریت تنخواه و فاکتور هوشمند

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE.txt)
[![Version](https://img.shields.io/badge/Version-2.3.0-orange.svg)](https://github.com/separkala-ui/zdr)

**سیستم جامع مدیریت تنخواه با قابلیت استخراج خودکار فاکتورها توسط هوش مصنوعی**

[🚀 شروع سریع](#نصب-سریع) •
[📚 مستندات کامل](#مستندات) •
[🎯 ویژگی‌ها](#ویژگیها) •
[🖼 تصاویر](#تصاویر) •
[💬 پشتیبانی](#پشتیبانی)

</div>

---

## 📋 فهرست مطالب

- [معرفی](#معرفی)
- [ویژگی‌های کلیدی](#ویژگیهای-کلیدی)
- [پیش‌نیازها](#پیشنیازها)
- [نصب سریع](#نصب-سریع)
- [نصب روی محیط‌های مختلف](#نصب-روی-محیطهای-مختلف)
- [تنظیمات](#تنظیمات)
- [استفاده](#استفاده)
- [مستندات](#مستندات)
- [مشارکت](#مشارکت)
- [لایسنس](#لایسنس)

---

## 📖 معرفی

**ZDR** یک سیستم مدیریت تنخواه (Petty Cash Management) پیشرفته و مدرن است که با فناوری Laravel 12 ساخته شده و قابلیت‌های هوش مصنوعی را برای استخراج خودکار اطلاعات فاکتورها فراهم می‌کند.

### چرا ZDR؟

✅ **استخراج خودکار فاکتور** - با استفاده از Google Gemini AI  
✅ **مدیریت چند شعبه** - قابلیت مدیریت تنخواه شعب مختلف  
✅ **سیستم تایید چندمرحله‌ای** - گردش کار قابل تنظیم  
✅ **گزارش‌گیری جامع** - تحلیل و آمار کامل  
✅ **رابط کاربری مدرن** - طراحی زیبا با Tailwind CSS  
✅ **امنیت بالا** - مدیریت نقش‌ها و دسترسی‌ها  
✅ **نصب آسان** - راه‌اندازی در کمتر از 10 دقیقه  

---

## 🌟 ویژگی‌های کلیدی

### 🤖 فاکتور هوشمند (Smart Invoice)
- **استخراج خودکار با AI** - استفاده از Google Gemini 2.5 Flash
- **پردازش تصویر** - پشتیبانی از فرمت‌های JPG, PNG, PDF
- **تشخیص خودکار** - شناسایی مبلغ، تاریخ، فروشنده، اقلام
- **اعتبارسنجی هوشمند** - بررسی صحت محاسبات
- **پشتیبانی تاریخ شمسی** - تبدیل خودکار تاریخ جلالی

### 💰 مدیریت تنخواه
- **دفاتر متعدد** - مدیریت تنخواه شعب و بخش‌های مختلف
- **انواع تراکنش** - شارژ، هزینه، تعدیل
- **وضعیت‌های مختلف** - پیش‌نویس، ارسال شده، تایید، رد، نیاز به بازبینی
- **محاسبه خودکار موجودی** - بروزرسانی real-time
- **آرشیو دوره‌ای** - ذخیره و مدیریت دوره‌های مالی
- **محدودیت مبلغ** - تنظیم حد مجاز برای هر دفتر

### 📊 گزارش‌گیری و تحلیل
- **داشبورد تحلیلی** - نمایش آمار و نمودارها
- **گزارش تراکنش‌ها** - فیلتر و جستجوی پیشرفته
- **خروجی PDF** - چاپ و ذخیره گزارشات
- **Timeline تراکنش‌ها** - مشاهده تاریخچه کامل
- **گزارش دسته‌بندی** - تحلیل هزینه‌ها بر اساس دسته

### 👥 مدیریت کاربران
- **نقش‌ها و مجوزها** - سیستم دسترسی چندسطحی
- **تخصیص دفتر** - اختصاص هر دفتر به یک کاربر
- **لاگ عملیات** - ثبت تمام تغییرات
- **احراز هویت امن** - Laravel Sanctum

### 🎨 رابط کاربری
- **طراحی مدرن** - UI/UX بهینه با Tailwind CSS 4
- **پشتیبانی RTL** - کاملاً فارسی
- **Responsive** - سازگار با موبایل و تبلت
- **Dark Mode** - حالت شب (در دست توسعه)
- **Livewire** - بدون نیاز به reload صفحه

### 🔒 امنیت
- **مدیریت نقش** - Spatie Permission
- **لاگ دقیق** - Action Logs
- **Validation کامل** - در تمام سطوح
- **CSRF Protection** - محافظت از حملات
- **SQL Injection Prevention** - استفاده از Eloquent ORM

---

## 🔧 پیش‌نیازها

### نرم‌افزار

| پیش‌نیاز | نسخه حداقل | نسخه توصیه‌شده | نحوه بررسی |
|----------|------------|-----------------|------------|
| **PHP** | 8.3 | 8.4 | `php -v` |
| **Composer** | 2.0 | 2.7+ | `composer -V` |
| **MySQL/MariaDB** | 8.0 / 10.6 | 8.0+ / 10.11+ | `mysql -V` |
| **Node.js** | 18.0 | 20 LTS | `node -v` |
| **npm** | 9.0 | 10.0+ | `npm -v` |

### افزونه‌های PHP

```bash
pdo_mysql mbstring xml gd curl openssl intl fileinfo tokenizer bcmath
```

### سخت‌افزار (توصیه‌شده)

- **CPU:** 2+ Core
- **RAM:** 2+ GB
- **Storage:** 5+ GB
- **Bandwidth:** 100+ Mbps

---

## ⚡ نصب سریع

### گزینه 1: نصب خودکار (توصیه می‌شود)

```bash
# دانلود پروژه
git clone https://github.com/separkala-ui/zdr.git
cd zdr

# نصب خودکار
chmod +x scripts/install-enhanced.sh
./scripts/install-enhanced.sh --seed

# راه‌اندازی
php artisan serve
```

🎉 **تمام!** به `http://localhost:8000/admin` بروید

**اطلاعات ورود:**
- Email: `admin@example.com`
- Password: `password`

### گزینه 2: نصب دستی

```bash
# 1. دانلود
git clone https://github.com/separkala-ui/zdr.git
cd zdr

# 2. تنظیمات اولیه
cp .env.example .env
nano .env  # ویرایش تنظیمات دیتابیس

# 3. نصب وابستگی‌ها
composer install --optimize-autoloader
npm install && npm run build

# 4. راه‌اندازی دیتابیس
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# 5. بهینه‌سازی
php artisan optimize

# 6. اجرا
php artisan serve
```

📚 **راهنمای کامل:** [INSTALLATION-GUIDE.md](INSTALLATION-GUIDE.md)

---

## 🏗 نصب روی محیط‌های مختلف

### 🐳 Docker

```bash
git clone https://github.com/separkala-ui/zdr.git
cd zdr
cp .env.example .env

docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
```

مشاهده در: http://localhost

### 🏠 هاست اشتراکی (cPanel)

```bash
# در Terminal cPanel
cd public_html
git clone https://github.com/separkala-ui/zdr.git
cd zdr
./scripts/install-shared-hosting.sh
```

**تنظیمات لازم:**
- Document Root: `/public_html/zdr/public`
- Cron Job: `* * * * * cd /path/to/zdr && php artisan schedule:run`

📖 **راهنمای جامع:** [INSTALLATION-GUIDE.md#نصب-روی-هاست-اشتراکی](INSTALLATION-GUIDE.md#نصب-روی-هاست-اشتراکی)

### 🖥 سرور اختصاصی (Ubuntu/Debian)

```bash
# نصب پیش‌نیازها
sudo apt update && sudo apt upgrade -y
sudo apt install -y php8.4 php8.4-fpm composer nodejs npm mysql-server nginx

# نصب پروژه
cd /var/www
sudo git clone https://github.com/separkala-ui/zdr.git
cd zdr
sudo ./scripts/install-enhanced.sh --production

# تنظیم Nginx + SSL
# راهنمای کامل: DEPLOYMENT.md
```

📖 **راهنمای Production:** [DEPLOYMENT.md](DEPLOYMENT.md)

---

## ⚙️ تنظیمات

### تنظیمات پایه (`.env`)

```env
# برنامه
APP_NAME="ZDR"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# دیتابیس
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=zdr_database
DB_USERNAME=zdr_user
DB_PASSWORD=your_password

# فاکتور هوشمند
SMART_INVOICE_GEMINI_ENABLED=true
SMART_INVOICE_GEMINI_API_KEY=your_api_key
```

### دریافت Gemini API Key

1. برو به: [Google AI Studio](https://makersuite.google.com/app/apikey)
2. "Create API Key" کلیک کن
3. کلید را در `.env` قرار بده

### تنظیمات پیشرفته

- **تنخواه:** `config/petty-cash.php`
- **فاکتور هوشمند:** `config/smart-invoice.php`
- **دسترسی‌ها:** از پنل Admin → Roles & Permissions

---

## 🚀 استفاده

### مدیریت دفاتر تنخواه

1. ورود به پنل → **تنخواه** → **دفاتر**
2. **افزودن دفتر جدید**
3. تعیین **موجودی اولیه** و **محدودیت‌ها**
4. **تخصیص** به کاربر مربوطه

### ثبت تراکنش

1. انتخاب دفتر → **ثبت تراکنش**
2. انتخاب **نوع** (شارژ/هزینه)
3. **آپلود فاکتور** (اختیاری)
4. کلیک روی **تکمیل هوشمند** (اگر فاکتور دارید)
5. **ذخیره**

### تایید/رد تراکنش

1. تراکنش‌ها → **در انتظار تایید**
2. مشاهده جزئیات
3. **تایید** یا **رد** با ثبت توضیح

### گزارش‌گیری

1. تنخواه → **گزارشات**
2. انتخاب **بازه زمانی** و **فیلترها**
3. **مشاهده** یا **دانلود PDF**

---

## 📚 مستندات

| مستند | توضیح |
|-------|--------|
| [📖 راهنمای نصب کامل](INSTALLATION-GUIDE.md) | نصب روی محیط‌های مختلف |
| [🚀 شروع سریع](QUICK-START.md) | نصب در 10 دقیقه |
| [🏗 استقرار Production](DEPLOYMENT.md) | راهنمای deployment |
| [🤖 معماری فاکتور هوشمند](docs/smart-invoice-architecture.md) | جزئیات فنی AI |
| [🎨 راهنمای UI](docs/ui-guidelines.md) | استانداردهای طراحی |

---

## 🎯 Roadmap

### نسخه 2.4 (در دست توسعه)
- [ ] پشتیبانی از چند زبان
- [ ] API کامل RESTful
- [ ] اپلیکیشن موبایل (PWA)
- [ ] یکپارچه‌سازی با سیستم‌های حسابداری
- [ ] گزارشات پیشرفته‌تر

### نسخه 3.0 (آینده)
- [ ] Microservices Architecture
- [ ] Machine Learning برای پیش‌بینی هزینه‌ها
- [ ] Blockchain برای Audit Trail
- [ ] Real-time Collaboration

---

## 🤝 مشارکت

مشارکت شما را خوش‌آمد می‌گوییم! 

### نحوه مشارکت

1. **Fork** کنید
2. **Branch** جدید بسازید (`git checkout -b feature/amazing-feature`)
3. تغییرات را **Commit** کنید (`git commit -m 'Add amazing feature'`)
4. **Push** کنید (`git push origin feature/amazing-feature`)
5. **Pull Request** باز کنید

### قوانین

- کد را format کنید: `composer format`
- تست‌ها را اجرا کنید: `composer test`
- از [Conventional Commits](COMMIT_CONVENTION.md) استفاده کنید
- مستندات را به‌روز کنید

📖 **راهنمای مشارکت:** [CONTRIBUTING.md](CONTRIBUTING.md)

---

## 🐛 گزارش مشکلات

مشکلی پیدا کردید؟ در [GitHub Issues](https://github.com/separkala-ui/zdr/issues) گزارش دهید.

**قبل از گزارش:**
- جستجو کنید که قبلاً گزارش نشده باشد
- اطلاعات کامل بدهید (نسخه PHP, Laravel, خطا, ...)
- مراحل بازتولید مشکل را بنویسید

---

## 📞 پشتیبانی

- 📧 **Email:** support@zdr.ir
- 💬 **Telegram:** [@zdr_support](https://t.me/zdr_support)
- 🌐 **Website:** [zdr.ir](https://zdr.ir)
- 📝 **Docs:** [GitHub Wiki](https://github.com/separkala-ui/zdr/wiki)
- 🐛 **Issues:** [GitHub Issues](https://github.com/separkala-ui/zdr/issues)

---

## 📄 لایسنس

این پروژه تحت لایسنس [MIT](LICENSE.txt) منتشر شده است.

---

## 🙏 تشکر از

- [Laravel](https://laravel.com) - فریمورک PHP
- [Livewire](https://laravel-livewire.com) - AJAX بدون JavaScript
- [Tailwind CSS](https://tailwindcss.com) - CSS Framework
- [Spatie](https://spatie.be) - Laravel Packages
- [Google Gemini](https://ai.google.dev) - AI Platform
- و تمام [مشارکت‌کنندگان](https://github.com/separkala-ui/zdr/graphs/contributors)

---

## ⭐ Star History

اگر این پروژه برای شما مفید بود، لطفاً یک ستاره ⭐ بدهید!

[![Star History Chart](https://api.star-history.com/svg?repos=separkala-ui/zdr&type=Date)](https://star-history.com/#separkala-ui/zdr&Date)

---

<div align="center">

**ساخته شده با ❤️ توسط تیم ZDR**

[⬆ بازگشت به بالا](#-zdr---سیستم-مدیریت-تنخواه-و-فاکتور-هوشمند)

</div>

