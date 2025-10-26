# 🎯 Toast Notification System

سیستم اعلان‌های Toast برای نمایش پیام‌های سریع و غیرمزاحم در تمام بخش‌های اپلیکیشن.

---

## ✨ ویژگی‌ها

- ✅ 4 نوع اعلان: Success, Error, Warning, Info
- ✅ طراحی زیبا و RTL
- ✅ Auto-dismiss با Progress Bar
- ✅ Close Button
- ✅ انیمیشن نرم
- ✅ پشتیبانی از Session Flash
- ✅ Livewire Integration
- ✅ JavaScript API
- ✅ Position: بالا راست صفحه
- ✅ Stacking: چند toast همزمان

---

## 📖 نحوه استفاده

### 1️⃣ **از PHP (Backend)**

#### استفاده از Helper Functions:
```php
// Success
toast_success('عملیات با موفقیت انجام شد');

// Error  
toast_error('خطا در انجام عملیات');

// Warning
toast_warning('هشدار: لطفاً دقت کنید');

// Info
toast_info('اطلاعات جدید موجود است');

// با تنظیمات سفارشی
toast('پیام شما', 'success', 10000); // 10 ثانیه
```

#### استفاده از Session Flash (در Controllers):
```php
// در Controller
public function store()
{
    // انجام عملیات...
    
    toast_success('محصول با موفقیت اضافه شد');
    return redirect()->route('products.index');
}

// یا استفاده مستقیم از session
session()->flash('success', 'عملیات موفق');
session()->flash('error', 'خطایی رخ داد');
```

### 2️⃣ **از Livewire**

```php
// در Livewire Component
public function save()
{
    // انجام عملیات...
    
    // ارسال event
    $this->dispatch('showToast', [
        'message' => 'ذخیره شد',
        'type' => 'success',
        'duration' => 5000
    ]);
}
```

### 3️⃣ **از JavaScript**

```javascript
// Success
window.toast.success('عملیات موفق');

// Error
window.toast.error('خطا رخ داد');

// Warning
window.toast.warning('توجه داشته باشید');

// Info
window.toast.info('اطلاعات جدید');

// با مدت زمان سفارشی
window.toast.success('پیام', 10000); // 10 ثانیه
```

### 4️⃣ **از Blade Templates**

```html
<!-- بعد از فرم submit -->
<form wire:submit.prevent="save">
    <!-- فیلدها -->
    <button type="submit">ذخیره</button>
</form>

<script>
// بعد از انجام عملیات
toast.success('فرم با موفقیت ارسال شد');
</script>
```

---

## 🎨 انواع Toast

### ✅ Success (سبز)
```php
toast_success('عملیات با موفقیت انجام شد');
```
- رنگ: سبز (Emerald)
- Icon: ✓ Check Circle
- کاربرد: عملیات موفق، ذخیره، حذف موفق

### ❌ Error (قرمز)
```php
toast_error('خطا در انجام عملیات');
```
- رنگ: قرمز (Rose)
- Icon: ✗ X Circle
- کاربرد: خطاها، مشکلات، validation errors

### ⚠️ Warning (نارنجی)
```php
toast_warning('هشدار: این عملیات قابل بازگشت نیست');
```
- رنگ: نارنجی (Amber)
- Icon: ⚠ Alert Triangle
- کاربرد: هشدارها، توجه‌ها

### ℹ️ Info (آبی)
```php
toast_info('اطلاعات جدید دریافت شد');
```
- رنگ: آبی (Indigo)
- Icon: ℹ Info
- کاربرد: اطلاعات عمومی، راهنماها

---

## ⚙️ تنظیمات

### مدت زمان نمایش (Duration)
```php
// 3 ثانیه
toast_success('پیام', 3000);

// 10 ثانیه
toast_error('پیام مهم', 10000);

// پیش‌فرض: 5000 (5 ثانیه)
```

### موقعیت
- **فعلی:** بالا راست صفحه
- برای تغییر: ویرایش کلاس‌های CSS در `livewire/toast-notification.blade.php`

---

## 🔧 فایل‌های مربوطه

| فایل | توضیح |
|------|-------|
| `app/Livewire/ToastNotification.php` | Component اصلی Livewire |
| `resources/views/livewire/toast-notification.blade.php` | View و UI |
| `app/Helpers/toast_helper.php` | Helper Functions |
| `resources/views/backend/layouts/app.blade.php` | Integration در layout |
| `composer.json` | Autoload helper |

---

## 📝 مثال‌های کاربردی

### مثال 1: ثبت تراکنش
```php
// در TransactionForm Livewire
public function save()
{
    $this->validate();
    
    try {
        // ذخیره تراکنش
        $transaction = $this->createTransaction();
        
        toast_success('تراکنش با موفقیت ثبت شد');
        $this->reset();
        
    } catch (\Exception $e) {
        toast_error('خطا در ثبت تراکنش: ' . $e->getMessage());
    }
}
```

### مثال 2: حذف کاربر
```php
// در UserController
public function destroy(User $user)
{
    if ($user->id === auth()->id()) {
        toast_warning('شما نمی‌توانید خودتان را حذف کنید');
        return back();
    }
    
    $user->delete();
    toast_success('کاربر با موفقیت حذف شد');
    
    return redirect()->route('admin.users.index');
}
```

### مثال 3: چند Toast همزمان
```php
// اطلاع‌رسانی چندگانه
toast_info('در حال پردازش...');

// بعد از 2 ثانیه
sleep(2);
toast_warning('لطفاً صبر کنید...');

// بعد از تمام شدن
toast_success('پردازش با موفقیت انجام شد');
```

### مثال 4: Validation Errors
```php
// در Form Request یا Controller
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|min:3',
        'email' => 'required|email',
    ]);
    
    if ($validator->fails()) {
        // نمایش اولین خطا
        toast_error($validator->errors()->first());
        return back()->withInput();
    }
    
    // ذخیره...
    toast_success('اطلاعات با موفقیت ذخیره شد');
}
```

### مثال 5: AJAX Success
```javascript
// در فایل JavaScript
fetch('/api/data', {
    method: 'POST',
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(data => {
    toast.success('داده‌ها با موفقیت ارسال شدند');
})
.catch(error => {
    toast.error('خطا در ارسال داده‌ها');
});
```

---

## 🎭 Customization

### تغییر رنگ‌ها
ویرایش کلاس‌های Tailwind در `toast-notification.blade.php`:

```html
<!-- Success -->
<div class="bg-emerald-50"> <!-- تغییر به رنگ دلخواه -->
    <iconify-icon class="text-emerald-600"></iconify-icon>
</div>
```

### تغییر موقعیت
```html
<!-- بالا راست (فعلی) -->
<div class="fixed left-4 top-4 sm:right-4">

<!-- پایین راست -->
<div class="fixed bottom-4 left-4 sm:right-4">

<!-- بالا وسط -->
<div class="fixed left-1/2 top-4 transform -translate-x-1/2">
```

### تغییر انیمیشن
```html
<!-- در x-transition -->
x-transition:enter-start="translate-x-full opacity-0"  <!-- از راست -->
x-transition:enter-start="translate-y-full opacity-0"  <!-- از پایین -->
x-transition:enter-start="scale-95 opacity-0"          <!-- zoom -->
```

---

## 🧪 Testing

### تست دستی:
```php
// در routes/web.php (فقط برای تست)
Route::get('/test-toast', function() {
    toast_success('این یک پیام تست است');
    toast_error('این یک خطای تست است');
    toast_warning('این یک هشدار تست است');
    toast_info('این یک اطلاعیه تست است');
    
    return view('backend.pages.dashboard.index');
});
```

### تست در Console:
```javascript
// در DevTools Console
toast.success('تست موفق');
toast.error('تست خطا');
toast.warning('تست هشدار');
toast.info('تست اطلاعات');
```

---

## 🐛 Troubleshooting

### Toast نمایش داده نمی‌شود:
1. ✅ مطمئن شوید `@livewire('toast-notification')` در layout اضافه شده
2. ✅ Cache را پاک کنید: `php artisan optimize:clear`
3. ✅ Composer autoload را rebuild کنید: `composer dump-autoload`
4. ✅ Browser console را چک کنید برای JavaScript errors

### چند Toast روی هم می‌افتند:
- این رفتار عادی است، Toast ها به صورت Stack نمایش داده می‌شوند
- برای تک Toast: قبل از ارسال، clear کنید

### انیمیشن کار نمی‌کند:
- مطمئن شوید Alpine.js بارگذاری شده
- چک کنید که `x-cloak` styles در CSS موجود باشد

---

## 📊 Performance

- **حجم:** ~5KB (HTML + CSS + JS)
- **Dependencies:** Alpine.js, Livewire
- **سرعت:** < 100ms برای نمایش
- **توصیه:** حداکثر 3-4 Toast همزمان

---

## 🔐 Security

- ✅ پیام‌ها از XSS محافظت می‌شوند (Blade Escaping)
- ✅ Session Flash Messages امن هستند
- ⚠️ توجه: پیام‌های کاربران را Sanitize کنید

---

## 🚀 Performance Tips

1. از Toast برای پیام‌های کوتاه استفاده کنید
2. پیام‌های طولانی را در Modal نمایش دهید
3. برای اعلان‌های مهم از Warning یا Error استفاده کنید
4. Duration را بر اساس اهمیت پیام تنظیم کنید

---

**نسخه:** 1.0.0  
**تاریخ:** 26 آبان 1404  
**وضعیت:** ✅ آماده استفاده

