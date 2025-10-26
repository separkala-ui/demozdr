# راهنمای نصب افزونه زودری (ZDR)

## 🚀 نصب سریع

### پیش‌نیازها
- PHP 8.3 یا بالاتر
- Composer 2
- Node.js 18+ و npm 9+
- MySQL/MariaDB
- Git

### نصب خودکار
```bash
# کلون کردن مخزن
git clone https://github.com/your-org/zdr.git
cd zdr

# اجرای اسکریپت نصب
chmod +x scripts/install.sh
./scripts/install.sh --seed
```

### گزینه‌های نصب
- `--seed`: اجرای seeder ها برای داده‌های اولیه
- `--no-build`: صرف‌نظر از بیلد فرانت‌اند
- `--help`: نمایش راهنما

## ⚙️ تنظیمات اولیه

### 1. تنظیم دیتابیس
فایل `.env` را ویرایش کنید:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zdr_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 2. تنظیم کلیدهای API
برای استفاده از فاکتور هوشمند:
```env
# Google Gemini (پیشنهادی)
SMART_INVOICE_GEMINI_ENABLED=true
SMART_INVOICE_GEMINI_API_KEY=your_gemini_api_key

# یا OpenAI
SMART_INVOICE_OPENAI_ENABLED=true
SMART_INVOICE_OPENAI_API_KEY=your_openai_api_key
```

### 3. تنظیمات ایمیل (اختیاری)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
```

## 🎯 راه‌اندازی اولیه

### 1. اجرای سرور
```bash
php artisan serve
```

### 2. ورود به پنل مدیریت
- آدرس: `http://localhost:8000/admin`
- کاربر پیش‌فرض: `admin@example.com`
- رمز عبور: `password`

### 3. تنظیمات اولیه
1. **ایجاد کاربران**: بخش مدیریت کاربران
2. **تنظیم شعب**: بخش تنخواه شعب
3. **تنظیمات AI**: بخش تنظیمات > فاکتور هوشمند

## 📋 ویژگی‌های اصلی

### سیستم تنخواه
- ✅ مدیریت چند شعبه
- ✅ ثبت تراکنش‌های شارژ و هزینه
- ✅ سیستم تایید چندمرحله‌ای
- ✅ آرشیو دوره‌ای
- ✅ گزارش‌گیری کامل

### فاکتور هوشمند
- ✅ پردازش خودکار با AI
- ✅ استخراج اطلاعات از تصاویر
- ✅ تایید محاسباتی
- ✅ دسته‌بندی خودکار

### امنیت
- ✅ سیستم مجوزها و نقش‌ها
- ✅ اعتبارسنجی کامل
- ✅ لاگ‌گیری عملیات
- ✅ بک‌آپ خودکار

## 🔧 تنظیمات پیشرفته

### تنظیمات تنخواه
فایل `config/petty-cash.php`:
```php
'categories' => [
    'vegetables' => 'تره بار و محصولات تازه',
    'protein' => 'محصولات پروتئینی',
    // ...
],
```

### تنظیمات فاکتور هوشمند
فایل `config/smart-invoice.php`:
```php
'gemini' => [
    'enabled' => true,
    'model' => 'gemini-2.5-flash',
    'timeout' => 45,
],
```

## 🐛 عیب‌یابی

### مشکلات رایج

#### خطای دسترسی فایل
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### خطای دیتابیس
```bash
php artisan migrate:fresh --seed
```

#### مشکل کش
```bash
php artisan optimize:clear
php artisan optimize
```

#### مشکل فرانت‌اند
```bash
npm install
npm run build
```

## 📞 پشتیبانی

- 📧 ایمیل: support@zdr.com
- 📱 تلگرام: @zdr_support
- 🌐 وب‌سایت: https://zdr.com

## 📄 مجوز

این پروژه تحت مجوز MIT منتشر شده است.

---

**نسخه**: 2.0.0  
**تاریخ**: 2025-01-27  
**وضعیت**: آماده تولید ✅
