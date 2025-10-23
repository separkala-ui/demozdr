# Tankha (Petty Cash) Module

این ریپوزیتوری نسخه‌ی استخراج‌شده‌ی ماژول «تنخواه» از پروژه‌ی ZDR است تا بتوانید آن را به صورت مستقل نگه‌داری یا داخل پروژه‌های لاراول دیگر ادغام کنید.

## محتویات
- `app/Console/Commands` : دستور آرشیو‌گیری تنخواه.
- `app/Http/Controllers/Backend` : کنترلرهای تنخواه و تنظیمات هوش‌مصنوعی فاکتور هوشمند.
- `app/Livewire/PettyCash` : کامپوننت‌های Livewire مربوط به فرم‌ها و جدول تراکنش‌ها.
- `app/Models` : مدل‌های دیتابیس تنخواه.
- `app/Services/PettyCash` : سرویس‌های دامنه‌ای (شارژ، تسویه، پردازش فاکتور، Gemini و ...) به همراه سرویس‌های جانبی مورد نیاز.
- `config/smart-invoice.php` و `resources/lang/fa/smart_invoice.php` : تنظیمات و ترجمه‌های مرتبط با فاکتورهای هوشمند.
- `database/migrations` : مایگریشن‌های جدول‌های تنخواه.
- `resources/views/backend/pages/petty-cash` و `resources/views/livewire/petty-cash` : صفحات Blade و ویوهای Livewire.
- `routes/petty-cash.php` : مجموعه‌ی مسیرهای پیشنهادی که باید داخل گروه ادمین پروژه‌تان اضافه کنید.
- `docs/smart-invoice-architecture.md` : توضیح معماری سرویس‌های OCR و خط لوله‌ی پردازش.

## نحوه‌ی ادغام در پروژه‌ی اصلی
1. **کپی فایل‌ها**: پوشه‌ها و فایل‌های این ریپو را به مسیر متناظرشان در اپلیکیشن لاراول مقصد منتقل کنید.
2. **مسیرها**: محتوای `routes/petty-cash.php` را داخل گروه روتر ادمین خود `Route::prefix('admin')->middleware('auth')` اضافه کنید.
3. **کامند شل**: کلاس `App\Console\Commands\PettyCashArchive` را در `app/Console/Kernel.php` به آرایه‌ی `$commands` اضافه کنید و در صورت نیاز زمان‌بندی (`schedule`) تنظیم کنید.
4. **دسترسی‌ها**: با استفاده از `RolesService` و `PermissionService` سطح دسترسی‌های `petty_cash.*` را به نقش‌های مناسب اضافه کنید.
5. **ترجمه و تنظیمات**: فایل‌های ترجمه و `config/smart-invoice.php` را publish یا merge کنید، سپس مقادیر .env مثل کلیدهای Gemini/OCR را تکمیل کنید.
6. **فرانت‌اند**: اگر از تاریخ‌شمار جلالی یا اسکریپت‌های تکمیلی استفاده می‌کنید، ایمپورت‌های لازم (مثل `modules/jalaali-js-master`) را به باندل Vite/webpack اضافه نمایید.
7. **تست‌ها یا سیلودا**: پس از ادغام، `php artisan migrate` و سناریوهای اصلی (ثبت/ویرایش دفتر، ثبت تراکنش، تسویه، تولید گزارش) را تست کنید.

## وضعیت انتشار
این ماژول به صورت بسته‌ی Composer آماده نشده است و ساختار آن یک «overlay» است تا بتوانید سریعاً در پروژه‌ی موجودتان merge کنید. در صورت نیاز می‌توانید با تغییر namespaceها به `Separkala\Tankha\...` و تنظیم `composer.json` آن را به یک پکیج مستقل تبدیل کنید.

در صورت بروز مشکل یا نیاز به مستندسازی بیشتر، Issue باز کنید.
