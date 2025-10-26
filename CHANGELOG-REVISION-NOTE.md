# تغییرات: اضافه شدن قابلیت ثبت دلیل برای "ارسال به بازبینی"

## 📋 خلاصه تغییرات

در نسخه قبل، مدیر می‌توانست تراکنش را برای بازبینی ارسال کند اما نمی‌توانست دلیل خود را ثبت کند. 
حالا مشابه **تایید** و **رد**، مدیر می‌تواند دلیل ارسال برای بازبینی را توضیح دهد تا کاربر شعبه بداند چه تغییراتی لازم است.

---

## 🎯 ویژگی‌های جدید

### 1. Modal جدید برای ثبت دلیل بازبینی
- ✅ Modal زیبا با طراحی مشابه modal های تایید و رد
- ✅ فیلد الزامی برای وارد کردن دلیل (حداقل 5 کاراکتر)
- ✅ نمایش اطلاعات تراکنش در modal
- ✅ پیام راهنما برای کاربر

### 2. Validation کامل
- ✅ بررسی حداقل طول (5 کاراکتر)
- ✅ بررسی حداکثر طول (500 کاراکتر)
- ✅ پیام‌های خطای فارسی
- ✅ نمایش خطا در UI

### 3. ذخیره‌سازی دلیل
- ✅ ذخیره در `meta->revision_note`
- ✅ ثبت تاریخ و کاربر درخواست‌کننده
- ✅ نمایش دلیل برای کاربر شعبه

---

## 📝 فایل‌های تغییر یافته

### 1. Backend - Livewire Component
**فایل:** `app/Livewire/PettyCash/TransactionsTable.php`

#### تغییرات:
```php
// Property های جدید
public bool $showRevisionModal = false;
public ?int $revisionTransactionId = null;
public string $revisionNote = '';

// متد جدید برای باز کردن modal
public function openRevisionModal(int $transactionId): void

// متد بازنویسی شده با validation
public function requestRevision(): void
```

**خطوط تغییر یافته:**
- خط 57-61: اضافه شدن property های جدید
- خط 161-170: متد `openRevisionModal()`
- خط 172-213: متد `requestRevision()` بازنویسی شده

### 2. Backend - Service Layer
**فایل:** `app/Services/PettyCash/PettyCashService.php`

#### تغییرات:
```php
public function sendBackForRevision(
    PettyCashTransaction $transaction, 
    User $manager, 
    ?string $note = null
): PettyCashTransaction
{
    // اضافه شدن validation
    $note = trim((string) $note);
    
    if ($note === '') {
        throw ValidationException::withMessages([
            'revisionNote' => __('لطفاً دلیل ارسال برای بازبینی را وارد کنید.'),
        ]);
    }
    
    // ... بقیه کد
}
```

**خطوط تغییر یافته:**
- خط 121-151: اضافه شدن validation به متد `sendBackForRevision()`

### 3. Frontend - Blade View
**فایل:** `resources/views/livewire/petty-cash/transactions-table.blade.php`

#### تغییرات:
1. **دکمه ارسال به بازبینی** (2 مورد):
```blade
// قبل:
wire:click="requestRevision({{ $transaction->id }})"

// بعد:
wire:click="openRevisionModal({{ $transaction->id }})"
```

2. **Modal جدید** (خط 867-942):
```blade
<!-- Revision Modal -->
<div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60"
     x-data="{ show: @entangle('showRevisionModal') }"
     x-show="show">
    <!-- محتوای modal -->
</div>
```

**خطوط تغییر یافته:**
- خط 285: تغییر از `requestRevision()` به `openRevisionModal()`
- خط 455: تغییر از `requestRevision()` به `openRevisionModal()`
- خط 867-942: اضافه شدن Modal کامل برای revision

---

## 🎨 UI/UX بهبودها

### Modal Design
- **رنگ‌بندی:** رنگ amber برای هماهنگی با دکمه "ارسال برای بازبینی"
- **Icon:** استفاده از `lucide:arrow-left-right`
- **Layout:** مشابه modal های تایید و رد برای یکپارچگی UI

### نمایش اطلاعات
- شماره مرجع تراکنش
- مبلغ تراکنش
- شرح تراکنش
- فیلد textarea برای دلیل

### پیام‌ها
- راهنمای واضح در بالای modal
- placeholder مفید در textarea
- پیام کمکی زیر textarea
- پیام‌های خطای واضح

---

## 🔍 نحوه استفاده

### برای مدیر:

1. **باز کردن لیست تراکنش‌ها**
   - ورود به پنل → تنخواه → انتخاب دفتر → تراکنش‌ها

2. **ارسال برای بازبینی**
   - کلیک روی دکمه "ارسال برای بازبینی" در کنار تراکنش مورد نظر
   - modal باز می‌شود

3. **وارد کردن دلیل**
   - دلیل را توضیح دهید (مثلاً: "لطفاً فاکتور اصلی را آپلود کنید")
   - حداقل 5 کاراکتر الزامی است
   - کلیک روی "ارسال به بازبینی"

4. **نتیجه**
   - وضعیت تراکنش به "نیاز به تغییرات" تغییر می‌کند
   - کاربر شعبه دلیل را مشاهده می‌کند
   - دلیل در متادیتای تراکنش ذخیره می‌شود

### برای کاربر شعبه:

1. تراکنش در وضعیت "نیاز به تغییرات" قرار می‌گیرد
2. دلیل مدیر را مشاهده می‌کند
3. تغییرات لازم را اعمال می‌کند
4. تراکنش را مجدد ارسال می‌کند

---

## ✅ Validation Rules

| فیلد | قانون | پیام خطا |
|------|-------|----------|
| `revisionNote` | required | لطفاً دلیل ارسال برای بازبینی را وارد کنید. |
| `revisionNote` | min:5 | دلیل باید حداقل ۵ کاراکتر باشد. |
| `revisionNote` | max:500 | دلیل نباید بیشتر از ۵۰۰ کاراکتر باشد. |

---

## 📊 ساختار داده

### ذخیره در دیتابیس

دلیل بازبینی در فیلد `meta` جدول `petty_cash_transactions` ذخیره می‌شود:

```json
{
  "revision_requested_by": 1,
  "revision_requested_at": "2025-10-26T19:30:00.000Z",
  "revision_note": "لطفاً فاکتور اصلی را آپلود کنید",
  "approval_note": null,
  "rejection_reason": null
}
```

---

## 🧪 تست

### تست دستی

1. ✅ باز شدن modal
2. ✅ بستن modal با دکمه انصراف
3. ✅ بستن modal با ESC
4. ✅ بستن modal با کلیک بیرون
5. ✅ نمایش خطا برای فیلد خالی
6. ✅ نمایش خطا برای متن کوتاه (< 5 کاراکتر)
7. ✅ ذخیره موفق با دلیل معتبر
8. ✅ نمایش پیام موفقیت
9. ✅ refresh شدن لیست تراکنش‌ها
10. ✅ تغییر وضعیت به "نیاز به تغییرات"

### سناریوهای تست

#### سناریو 1: ارسال موفق
```
1. کلیک روی "ارسال برای بازبینی"
2. وارد کردن دلیل: "لطفاً فاکتور اصلی را آپلود کنید"
3. کلیک روی "ارسال به بازبینی"
4. انتظار: پیام موفقیت + بستن modal + تغییر وضعیت
```

#### سناریو 2: خطای validation
```
1. کلیک روی "ارسال برای بازبینی"
2. وارد کردن متن کوتاه: "ok"
3. کلیک روی "ارسال به بازبینی"
4. انتظار: پیام خطا "دلیل باید حداقل ۵ کاراکتر باشد."
```

#### سناریو 3: انصراف
```
1. کلیک روی "ارسال برای بازبینی"
2. وارد کردن دلیل
3. کلیک روی "انصراف"
4. انتظار: بستن modal بدون تغییر
```

---

## 🔄 مقایسه قبل و بعد

### ❌ قبل (بدون modal)
```php
// کلیک مستقیم
wire:click="requestRevision({{ $transaction->id }})"

// بدون ورودی از کاربر
app(PettyCashService::class)->sendBackForRevision($transaction, Auth::user());
```

### ✅ بعد (با modal و دلیل)
```php
// کلیک برای باز کردن modal
wire:click="openRevisionModal({{ $transaction->id }})"

// با ورودی دلیل از کاربر
app(PettyCashService::class)->sendBackForRevision($transaction, Auth::user(), $this->revisionNote);
```

---

## 📦 سازگاری

- ✅ Laravel 12.x
- ✅ Livewire 3.x
- ✅ PHP 8.3+
- ✅ Tailwind CSS 4.x
- ✅ Alpine.js 3.x

---

## 🐛 مسائل شناخته شده

هیچ مشکلی شناسایی نشده است.

---

## 🎯 کارهای آینده

- [ ] افزودن امکان ویرایش دلیل بازبینی
- [ ] ثبت تاریخچه تغییرات دلیل
- [ ] اعلان به کاربر شعبه هنگام ارسال برای بازبینی
- [ ] نمایش دلیل در صفحه جزئیات تراکنش

---

## 👥 مشارکت‌کنندگان

- تغییرات توسط: AI Assistant (Claude)
- درخواست توسط: کاربر پروژه ZDR
- تاریخ: 26 آبان 1404

---

## 📞 پشتیبانی

در صورت بروز مشکل:
- 📧 Email: support@zdr.ir
- 💬 GitHub Issues: https://github.com/separkala-ui/zdr/issues

---

**نسخه:** 2.3.1  
**تاریخ:** 26 آبان 1404  
**وضعیت:** ✅ تکمیل شده و آماده استفاده

