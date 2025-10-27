# 📱 سیستم ارسال پیامک IPPanel (نسخه ۲)

راهنمای جامع سیستم ارسال پیامک با استفاده از IPPanel، شامل مدیریت پترن و اطلاع‌رسانی درخواست شارژ.

---

## 📋 فهرست

1. [معرفی](#معرفی)
2. [نصب و راه‌اندازی](#نصب-و-راه‌اندازی)
3. [تنظیمات](#تنظیمات)
4. [پنل مدیریت](#پنل-مدیریت)
5. [پیامک‌های پیش‌فرض](#پیامک‌های-پیش‌فرض)
6. [چند کاربر به هر شعبه](#چند-کاربر-به-هر-شعبه)

---

## 🎯 معرفی

این سیستم امکان ارسال پیامک از طریق **IPPanel** را فراهم می‌کند.

**قابلیت‌های جدید (نسخه ۲):**
- ✅ **مدیریت پترن‌ها:** پنل گرافیکی برای مدیریت کدهای پترن.
- ✅ **اطلاع‌رسانی درخواست شارژ:** ارسال SMS به مدیر مالی.

---

## 🔧 نصب و راه‌اندازی

### 1. Migrations

```bash
php artisan migrate
```

**Migrations شامل:**
- ✅ فیلد `mobile` به جدول `users`
- ✅ فیلد `manager_mobile` به جدول `petty_cash_ledgers`
- ✅ جدول `branch_users` (چند کاربر به هر شعبه)

### 2. Models

- ✅ `IPPanelService` (Service Layer)
- ✅ `BranchUser` (Model)
- ✅ Relations به `User` و `PettyCashLedger`

### 3. Helper Functions

Helper functions به صورت خودکار بارگذاری می‌شوند:

```php
app/Helpers/sms_helper.php
```

---

## ⚙️ تنظیمات

### ۱. فایل `.env`

```env
# IPPanel SMS Configuration
IPPANEL_ENABLED=true
IPPANEL_LOG_ONLY=false
IPPANEL_API_KEY=your_api_key_here
IPPANEL_ORIGINATOR=+98...
FINANCE_MANAGER_MOBILE=0912...

# Pattern Codes (managed from admin panel)
IPPANEL_PATTERN_WELCOME=...
IPPANEL_PATTERN_BRANCH_CREATED=...
IPPANEL_PATTERN_CHARGE_REQUEST=...
IPPANEL_PATTERN_TRANSACTION_APPROVED=...
IPPANEL_PATTERN_TRANSACTION_REJECTED=...
IPPANEL_PATTERN_TRANSACTION_REVISION=...
```

### 2. حالت‌های مختلف

#### حالت توسعه (Development):
```env
IPPANEL_ENABLED=false
IPPANEL_LOG_ONLY=true
```
**نتیجه:** فقط لاگ می‌شود، ارسال نمی‌شود.

#### حالت تست (Testing):
```env
IPPANEL_ENABLED=true
IPPANEL_LOG_ONLY=true
```
**نتیجه:** فقط لاگ می‌شود، ارسال نمی‌شود.

#### حالت تولید (Production):
```env
IPPANEL_ENABLED=true
IPPANEL_LOG_ONLY=false
```
**نتیجه:** واقعاً ارسال می‌شود! 🚀

---

## 🎨 استفاده از Service

### 1. ارسال پیامک ساده

```php
use App\Services\SMS\IPPanelService;

$sms = app(IPPanelService::class);

$result = $sms->send('09123456789', 'سلام! این یک پیام تستی است.');

if ($result['success']) {
    echo "پیام ارسال شد! Message ID: " . $result['message_id'];
} else {
    echo "خطا: " . $result['error'];
}
```

### 2. ارسال پیامک پترن

```php
$result = $sms->sendPattern('09123456789', 'your-pattern-code', [
    'name' => 'علی',
    'company' => 'ZDR',
]);
```

### 3. دریافت اعتبار

```php
$result = $sms->getCredit();

if ($result['success']) {
    echo "اعتبار: " . $result['credit'];
}
```

### 4. دریافت وضعیت پیام

```php
$result = $sms->getMessageStatus($messageId);

if ($result['success']) {
    echo "وضعیت: " . $result['status'];
}
```

---

## 🛠️ Helper Functions

### 1. `sms()`

دریافت instance از service:

```php
$smsService = sms();
```

### 2. `send_sms($recipients, $message)`

ارسال پیامک ساده:

```php
// یک گیرنده
send_sms('09123456789', 'سلام دنیا!');

// چند گیرنده
send_sms(['09123456789', '09987654321'], 'سلام دنیا!');
```

### 3. `send_pattern_sms($mobile, $patternCode, $variables)`

ارسال پیامک پترن:

```php
send_pattern_sms('09123456789', 'welcome-pattern', [
    'name' => 'محمد',
    'code' => '1234',
]);
```

---

## 📬 پیامک‌های پیش‌فرض

### 1. خوش‌آمدگویی هنگام ثبت‌نام

```php
use App\Services\SMS\IPPanelService;

$sms = app(IPPanelService::class);
$sms->sendWelcomeSMS('09123456789', 'علی');
```

### 2. ساخت شعبه جدید

```php
$sms->sendBranchCreatedSMS('09123456789', 'شعبه مرکزی', 'علی');
```

### 3. اطلاع‌رسانی عمومی

```php
$sms->sendAnnouncementSMS('09123456789', 'عنوان اطلاعیه', 'متن اطلاعیه');
```

### 4. تایید تراکنش

```php
$sms->sendTransactionApprovedSMS('09123456789', '1,000,000', 'TX-001');
```

### 5. رد تراکنش

```php
$sms->sendTransactionRejectedSMS('09123456789', '1,000,000', 'TX-001', 'مدارک ناقص');
```

### 6. درخواست بازبینی تراکنش

```php
$sms->sendTransactionRevisionSMS('09123456789', '1,000,000', 'TX-001', 'نیاز به مدرک تکمیلی');
```

### 7. ارسال پیامک درخواست شارژ (جدید!)

```php
// app/Livewire/PettyCash/ChargeRequestForm.php

// ... پس از موفقیت‌آمیز بودن درخواست
sms()->sendChargeRequestSMS(
    $user->full_name,          // نام درخواست‌دهنده
    $this->ledger->branch_name,  // نام شعبه
    (string) $validated['amount'], // مبلغ
    verta()->now()->format('Y/n/j H:i') // تاریخ شمسی
);
```

**متغیرهای پترن `charge_request`:**
- `{manager_name}`
- `{branch_name}`
- `{amount}`
- `{date}`

---

## 🎯 پترن‌ها (Patterns)

برای استفاده از پترن‌ها، ابتدا باید در پنل IPPanel پترن ایجاد کنید.

### مثال پترن خوش‌آمدگویی:

```
سلام {name} عزیز،

به سیستم ZDR خوش آمدید!

اطلاعات ورود به حساب شما ایجاد شده است.
```

**متغیرها:**
- `{name}`: نام کاربر

**کد پترن در `.env`:**
```env
IPPANEL_PATTERN_WELCOME=your-pattern-code-here
```

### مثال پترن ساخت شعبه:

```
سلام {manager_name} عزیز،

شعبه {branch_name} با موفقیت ایجاد شد و شما به عنوان مسئول آن منصوب شدید.

ZDR System
```

**متغیرها:**
- `{manager_name}`: نام مدیر
- `{branch_name}`: نام شعبه

---

## 👥 چند کاربر به هر شعبه

امکان اضافه کردن چند کاربر با دسترسی‌های مختلف به یک شعبه.

### انواع دسترسی:

| نوع | توضیح |
|-----|-------|
| `petty_cash` | تنخواه |
| `inspection` | بازرسی |
| `quality_control` | کنترل کیفیت |
| `production_engineering` | مهندسی تولید |

### استفاده در Code:

#### اضافه کردن کاربر به شعبه:

```php
use App\Models\BranchUser;

BranchUser::create([
    'ledger_id' => 1,
    'user_id' => 5,
    'access_type' => 'petty_cash',
    'is_active' => true,
]);
```

#### بررسی دسترسی کاربر:

```php
$user = User::find(5);

// آیا کاربر به شعبه 1 دسترسی دارد؟
if ($user->hasAccessToBranch(1)) {
    echo "دسترسی دارد";
}

// آیا کاربر به شعبه 1 با نوع دسترسی petty_cash دسترسی دارد؟
if ($user->hasAccessToBranch(1, 'petty_cash')) {
    echo "دسترسی به تنخواه دارد";
}
```

#### دریافت همه شعبه‌های کاربر:

```php
$user = User::find(5);
$branches = $user->branches; // Collection of PettyCashLedger
```

#### دریافت همه کاربران یک شعبه:

```php
$ledger = PettyCashLedger::find(1);
$users = $ledger->accessUsers; // Collection of User
```

#### فیلتر بر اساس نوع دسترسی:

```php
use App\Models\BranchUser;

// همه کاربران با دسترسی petty_cash
$users = BranchUser::where('access_type', 'petty_cash')
    ->where('is_active', true)
    ->with('user', 'ledger')
    ->get();
```

---

## 🔒 Permissions

مدل `BranchUser` امکان تعریف دسترسی‌های سفارشی را دارد:

```php
$branchUser = BranchUser::find(1);

// بررسی دسترسی
if ($branchUser->hasPermission('approve_transactions')) {
    // ...
}

// اضافه کردن دسترسی
$branchUser->addPermission('approve_transactions');

// حذف دسترسی
$branchUser->removePermission('approve_transactions');
```

---

## 📊 Database Schema

### جدول `users`:

```
id
first_name
last_name
email
mobile (جدید!) 📱
password
...
```

### جدول `petty_cash_ledgers`:

```
id
branch_name
manager_mobile (جدید!) 📱
...
```

### جدول `branch_users`:

```
id
ledger_id (FK → petty_cash_ledgers)
user_id (FK → users)
access_type
is_active
permissions (JSON)
created_at
updated_at
```

---

## 🧪 تست

### 1. تست ارسال پیامک (Log-Only):

```bash
php artisan tinker
```

```php
send_sms('09123456789', 'این یک تست است');
```

**نتیجه:** لاگ در `storage/logs/laravel.log`:

```
[SMS LOG-ONLY] SMS would have been sent
{
    "recipients": ["09123456789"],
    "message": "این یک تست است"
}
```

### 2. تست اعتبار:

```php
$result = sms()->getCredit();
dd($result);
```

---

## 🚀 Production Checklist (آپدیت)

قبل از فعال کردن در Production:

- [ ] شماره موبایل مدیر مالی را در پنل تنظیمات وارد کنید.
- [ ] پترن `charge_request` را در IPPanel بسازید.
- [ ] کد پترن `charge_request` را در پنل تنظیمات وارد کنید.

---

## 📞 پشتیبانی

برای مشکلات مربوط به IPPanel:
- 📖 [مستندات IPPanel](https://docs.ippanel.com/)
- 💬 پشتیبانی IPPanel

---

**ساخته شده با ❤️ برای پروژه ZDR**


