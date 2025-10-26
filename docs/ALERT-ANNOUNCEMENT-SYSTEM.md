# 🔔 سیستم جامع هشدارها و اطلاعیه‌ها

**تاریخ:** 1404/08/05  
**نسخه:** 2.0.0  
**وضعیت:** Backend کامل ✅ | Frontend نیاز به تکمیل ⚠️

---

## 📋 خلاصه تغییرات

یک سیستم کامل و حرفه‌ای برای مدیریت هشدارها و اطلاعیه‌ها ساخته شد که شامل:

1. ✅ **تنظیمات پویا هشدارها** - سوپر ادمین می‌تواند تنظیمات را تغییر دهد
2. ✅ **سیستم اطلاعیه‌ها** - مدیر می‌تواند اطلاعیه ایجاد کند و کارمندان ببینند
3. ✅ **ارتقای AlertsPanel** - هشدارها از تنظیمات پویا استفاده می‌کنند
4. ✅ **Models & Migrations** - دیتابیس کامل
5. ✅ **Livewire Components** - Logic کامل
6. ⚠️ **Views** - نیاز به تکمیل در مرحله بعد

---

## 🗄️ دیتابیس

### 1️⃣ جدول `alert_settings`

```sql
CREATE TABLE alert_settings (
    id BIGINT PRIMARY KEY,
    key VARCHAR UNIQUE,                  -- کلید تنظیمات
    category VARCHAR,                    -- دسته‌بندی
    type VARCHAR,                        -- نوع (percentage, amount, count, boolean)
    value TEXT,                          -- مقدار
    title_fa VARCHAR,                    -- عنوان فارسی
    description_fa TEXT,                 -- توضیحات فارسی
    title_en VARCHAR,                    -- عنوان انگلیسی
    description_en TEXT,                 -- توضیحات انگلیسی
    is_active BOOLEAN DEFAULT TRUE,      -- فعال/غیرفعال
    is_editable BOOLEAN DEFAULT TRUE,    -- قابل ویرایش
    priority INT DEFAULT 0,              -- اولویت
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**تنظیمات پیش‌فرض (11 مورد):**

| کلید | دسته | نوع | مقدار | توضیح |
|------|------|-----|-------|-------|
| `low_balance_threshold_percentage` | petty_cash | percentage | 20 | درصد هشدار موجودی کم |
| `very_low_balance_threshold_percentage` | petty_cash | percentage | 10 | درصد هشدار موجودی بسیار کم |
| `pending_transactions_alert_count` | petty_cash | count | 5 | تعداد تراکنش‌های معلق برای هشدار |
| `overdue_settlement_days` | petty_cash | count | 30 | روزهای تسویه معوق |
| `high_expense_rate_days` | petty_cash | count | 7 | بازه بررسی نرخ هزینه |
| `high_expense_rate_percentage` | petty_cash | percentage | 50 | درصد نرخ هزینه بالا |
| `large_transaction_threshold` | transaction | amount | 10000000 | آستانه تراکنش بزرگ (ریال) |
| `duplicate_transaction_check_enabled` | transaction | boolean | true | بررسی تراکنش‌های تکراری |
| `alert_auto_dismiss_seconds` | general | count | 300 | زمان بستن خودکار (ثانیه) |
| `enable_email_alerts` | general | boolean | false | ارسال هشدار به ایمیل |
| `enable_sms_alerts` | general | boolean | false | ارسال هشدار به پیامک |

---

### 2️⃣ جدول `system_announcements`

```sql
CREATE TABLE system_announcements (
    id BIGINT PRIMARY KEY,
    title VARCHAR,                       -- عنوان
    content TEXT,                        -- محتوا
    type VARCHAR DEFAULT 'info',         -- نوع (info, success, warning, danger)
    priority VARCHAR DEFAULT 'normal',   -- اولویت (low, normal, high, urgent)
    is_active BOOLEAN DEFAULT TRUE,      -- فعال
    is_pinned BOOLEAN DEFAULT FALSE,     -- سنجاق شده
    starts_at TIMESTAMP NULL,            -- شروع نمایش
    expires_at TIMESTAMP NULL,           -- انقضا
    created_by BIGINT NULL,              -- ایجاد شده توسط
    target_roles JSON NULL,              -- نقش‌های هدف
    target_users JSON NULL,              -- کاربران هدف
    icon VARCHAR NULL,                   -- آیکون
    action_url VARCHAR NULL,             -- لینک عملیات
    action_text VARCHAR NULL,            -- متن دکمه
    view_count INT DEFAULT 0,            -- تعداد بازدید
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

---

## 📦 Models

### 1️⃣ `AlertSetting`

```php
// دریافت مقدار تنظیمات
$value = AlertSetting::getValue('low_balance_threshold_percentage', 20);

// تنظیم مقدار
AlertSetting::setValue('low_balance_threshold_percentage', 15);

// دریافت بر اساس دسته‌بندی
$settings = AlertSetting::getByCategory('petty_cash');
```

**Methods:**
- `getValue(string $key, $default = null)` - دریافت مقدار
- `setValue(string $key, $value)` - تنظیم مقدار
- `getByCategory(string $category)` - فیلتر بر اساس دسته
- `scopeActive($query)` - فقط فعال‌ها
- `scopeEditable($query)` - فقط قابل ویرایش‌ها

---

### 2️⃣ `SystemAnnouncement`

```php
// دریافت اطلاعیه‌های قابل نمایش
$announcements = SystemAnnouncement::visible()
    ->forUser($user)
    ->byPriority()
    ->get();

// ایجاد اطلاعیه جدید
SystemAnnouncement::create([
    'title' => 'اطلاعیه مهم',
    'content' => 'متن اطلاعیه',
    'type' => 'info',
    'priority' => 'normal',
    'is_active' => true,
    'created_by' => auth()->id(),
]);
```

**Scopes:**
- `visible()` - اطلاعیه‌های قابل نمایش (فعال + در بازه زمانی)
- `forUser($user)` - فیلتر بر اساس نقش/کاربر
- `byPriority()` - مرتب‌سازی بر اساس اولویت
- `active()` - فقط فعال‌ها
- `pinned()` - فقط سنجاق شده‌ها

**Methods:**
- `incrementViews()` - افزایش بازدید
- `isExpired()` - بررسی انقضا
- `hasStarted()` - بررسی شروع
- `isVisibleNow()` - بررسی قابل نمایش بودن

---

## 🎨 Livewire Components

### 1️⃣ `AlertSettingsManagement` (مدیریت تنظیمات)

**مسیر:** `/admin/alert-settings`

**ویژگی‌ها:**
- ✅ لیست تنظیمات با صفحه‌بندی
- ✅ جستجو
- ✅ فیلتر بر اساس دسته‌بندی
- ✅ ویرایش مقدار (با validation بر اساس نوع)
- ✅ فعال/غیرفعال کردن
- ⚠️ View نیاز به تکمیل

**Property ها:**
```php
public $search = '';
public $categoryFilter = 'all';
public $editingId = null;
public $editingValue = null;
```

**Methods:**
```php
editSetting($id)           // باز کردن فرم ویرایش
saveSetting()              // ذخیره تنظیمات
cancelEdit()               // انصراف
toggleActive($id)          // فعال/غیرفعال
```

---

### 2️⃣ `AnnouncementsManagement` (مدیریت اطلاعیه‌ها)

**مسیر:** `/admin/announcements`

**ویژگی‌ها:**
- ✅ لیست اطلاعیه‌ها
- ✅ ایجاد اطلاعیه جدید
- ✅ ویرایش اطلاعیه
- ✅ حذف (Soft Delete)
- ✅ فعال/غیرفعال
- ✅ فیلتر بر اساس نوع
- ⚠️ View نیاز به تکمیل

**Property ها:**
```php
public $search = '';
public $typeFilter = 'all';
public $showModal = false;
public $title = '';
public $content = '';
public $type = 'info';
public $priority = 'normal';
// ... و سایر property ها
```

**Methods:**
```php
openCreateModal()          // باز کردن فرم ایجاد
openEditModal($id)         // باز کردن فرم ویرایش
save()                     // ذخیره
delete($id)                // حذف
toggleActive($id)          // فعال/غیرفعال
```

---

### 3️⃣ `AnnouncementsWidget` (نمایش اطلاعیه‌ها)

**مکان:** هر صفحه (معمولاً داشبورد اصلی)

**ویژگی‌ها:**
- ✅ نمایش اطلاعیه‌های فعال
- ✅ فیلتر بر اساس نقش کاربر
- ✅ قابلیت dismiss (بستن)
- ✅ نمایش 3 اطلاعیه (قابل تغییر به همه)
- ✅ آمار بازدید
- ⚠️ View نیاز به تکمیل

**Property ها:**
```php
public $showAll = false;
public $dismissedIds = [];
```

**Methods:**
```php
dismiss($id)               // بستن اطلاعیه
toggleShowAll()            // نمایش همه/محدود
markAsViewed($id)          // ثبت بازدید
```

---

## 🔧 ارتقای AlertsPanel

**تغییرات:**

### قبل:
```php
if ($balancePercentage < 20) {
    // هشدار موجودی کم
}
```

### بعد:
```php
$lowThreshold = (float) AlertSetting::getValue('low_balance_threshold_percentage', 20);

if ($balancePercentage < $lowThreshold) {
    // هشدار موجودی کم
}
```

**تنظیمات قابل تغییر:**
- ✅ درصد موجودی کم
- ✅ درصد موجودی بسیار کم
- ✅ تعداد تراکنش‌های معلق

---

## 📝 مراحل تکمیل (نیاز به انجام)

### 1️⃣ Views (الزامی)

#### `alert-settings-management.blade.php`

```blade
<div class="space-y-4">
    {{-- Search & Filter --}}
    <div class="flex gap-4">
        <input wire:model.live="search" 
               type="text" 
               placeholder="جستجو..."
               class="...">
        
        <select wire:model.live="categoryFilter" class="...">
            <option value="all">همه دسته‌ها</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
        </select>
    </div>

    {{-- Settings Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>دسته</th>
                    <th>نوع</th>
                    <th>مقدار</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($settings as $setting)
                    <tr>
                        <td>{{ $setting->title_fa }}</td>
                        <td>{{ $setting->category }}</td>
                        <td>{{ $setting->type }}</td>
                        <td>
                            @if($editingId === $setting->id)
                                <input wire:model="editingValue" 
                                       type="text" 
                                       class="...">
                            @else
                                {{ $setting->value }}
                            @endif
                        </td>
                        <td>
                            <button wire:click="toggleActive({{ $setting->id }})">
                                {{ $setting->is_active ? 'فعال' : 'غیرفعال' }}
                            </button>
                        </td>
                        <td>
                            @if($editingId === $setting->id)
                                <button wire:click="saveSetting">ذخیره</button>
                                <button wire:click="cancelEdit">انصراف</button>
                            @else
                                <button wire:click="editSetting({{ $setting->id }})">
                                    ویرایش
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $settings->links() }}
</div>
```

---

#### `announcements-management.blade.php`

```blade
<div>
    {{-- Create Button --}}
    <button wire:click="openCreateModal" class="...">
        ایجاد اطلاعیه جدید
    </button>

    {{-- Announcements List --}}
    <div class="space-y-4 mt-4">
        @foreach($announcements as $announcement)
            <div class="border rounded-lg p-4">
                <div class="flex justify-between">
                    <div>
                        <h3>{{ $announcement->title }}</h3>
                        <p>{{ $announcement->content }}</p>
                        <span class="badge">{{ $announcement->type }}</span>
                        <span class="badge">{{ $announcement->priority }}</span>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="openEditModal({{ $announcement->id }})">
                            ویرایش
                        </button>
                        <button wire:click="toggleActive({{ $announcement->id }})">
                            {{ $announcement->is_active ? 'غیرفعال' : 'فعال' }}
                        </button>
                        <button wire:click="delete({{ $announcement->id }})">
                            حذف
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Modal for Create/Edit --}}
    @if($showModal)
        <div class="modal">
            <form wire:submit.prevent="save">
                <input wire:model="title" type="text" placeholder="عنوان">
                <textarea wire:model="content" placeholder="محتوا"></textarea>
                
                <select wire:model="type">
                    <option value="info">اطلاع</option>
                    <option value="success">موفقیت</option>
                    <option value="warning">هشدار</option>
                    <option value="danger">خطر</option>
                </select>

                <select wire:model="priority">
                    <option value="low">کم</option>
                    <option value="normal">عادی</option>
                    <option value="high">بالا</option>
                    <option value="urgent">فوری</option>
                </select>

                <input wire:model="starts_at" type="datetime-local">
                <input wire:model="expires_at" type="datetime-local">

                <label>
                    <input wire:model="is_active" type="checkbox">
                    فعال
                </label>

                <label>
                    <input wire:model="is_pinned" type="checkbox">
                    سنجاق شده
                </label>

                <button type="submit">ذخیره</button>
                <button type="button" @click="$wire.showModal = false">انصراف</button>
            </form>
        </div>
    @endif
</div>
```

---

#### `announcements-widget.blade.php`

```blade
<div class="space-y-3">
    @if($announcements->isEmpty())
        <p class="text-center text-slate-500">اطلاعیه‌ای وجود ندارد</p>
    @else
        @foreach($announcements as $announcement)
            <div class="announcement-card 
                        announcement-{{ $announcement->type }}
                        {{ $announcement->is_pinned ? 'pinned' : '' }}"
                 wire:init="markAsViewed({{ $announcement->id }})">
                
                <div class="flex items-start gap-3">
                    <iconify-icon icon="{{ $announcement->icon ?? $announcement->default_icon }}" 
                                  class="text-2xl"></iconify-icon>
                    
                    <div class="flex-1">
                        <h4>{{ $announcement->title }}</h4>
                        <p>{{ $announcement->content }}</p>
                        
                        @if($announcement->action_url)
                            <a href="{{ $announcement->action_url }}" 
                               class="btn btn-sm">
                                {{ $announcement->action_text ?? 'مشاهده' }}
                            </a>
                        @endif
                    </div>

                    <button wire:click="dismiss({{ $announcement->id }})" 
                            class="btn-close">
                        ×
                    </button>
                </div>
            </div>
        @endforeach
    @endif

    @if($totalCount > 3 && !$showAll)
        <button wire:click="toggleShowAll" class="btn-link">
            مشاهده همه اطلاعیه‌ها ({{ $totalCount }})
        </button>
    @endif
</div>
```

---

### 2️⃣ Routes (الزامی)

```php
// routes/admin.php یا routes/web.php

Route::middleware(['auth', 'role:Superadmin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Alert Settings Management
    Route::get('/alert-settings', function () {
        return view('admin.alert-settings.index');
    })->name('alert-settings.index');

    // Announcements Management
    Route::get('/announcements', function () {
        return view('admin.announcements.index');
    })->name('announcements.index');
});
```

---

### 3️⃣ Integration در Dashboard

```blade
{{-- در resources/views/backend/pages/dashboard/index.blade.php --}}

<div class="grid grid-cols-1 gap-6">
    {{-- نمایش اطلاعیه‌ها --}}
    @livewire('announcements-widget')

    {{-- سایر ویجت‌ها --}}
    ...
</div>
```

---

## 🎨 طراحی پیشنهادی

### رنگ‌بندی اطلاعیه‌ها:

| Type | رنگ | آیکون |
|------|-----|-------|
| info | blue | lucide:info |
| success | emerald | lucide:check-circle |
| warning | amber | lucide:alert-triangle |
| danger | rose | lucide:alert-octagon |

### اولویت:

| Priority | نمایش |
|----------|-------|
| urgent | رنگ قرمز + سنجاق در بالا + متن Bold |
| high | رنگ نارنجی + Border ضخیم |
| normal | رنگ آبی + Border معمولی |
| low | رنگ خاکستری + شفافیت 80% |

---

## 📊 آمار و گزارش

### تنظیمات:
```php
// تعداد تنظیمات فعال
AlertSetting::active()->count();

// تنظیمات هر دسته
AlertSetting::active()->get()->groupBy('category');
```

### اطلاعیه‌ها:
```php
// تعداد اطلاعیه‌های فعال
SystemAnnouncement::visible()->count();

// میانگین بازدید
SystemAnnouncement::avg('view_count');

// اطلاعیه‌های منقضی شده
SystemAnnouncement::whereNotNull('expires_at')
    ->where('expires_at', '<', now())
    ->count();
```

---

## 🧪 تست

### AlertSetting:
```php
// تست دریافت مقدار
$value = AlertSetting::getValue('low_balance_threshold_percentage');
$this->assertEquals(20, $value);

// تست تنظیم مقدار
AlertSetting::setValue('low_balance_threshold_percentage', 15);
$newValue = AlertSetting::getValue('low_balance_threshold_percentage');
$this->assertEquals(15, $newValue);
```

### SystemAnnouncement:
```php
// تست قابل نمایش بودن
$announcement = SystemAnnouncement::factory()->create([
    'is_active' => true,
    'starts_at' => now()->subDay(),
    'expires_at' => now()->addDay(),
]);

$this->assertTrue($announcement->isVisibleNow());
```

---

## 🚀 نصب و راه‌اندازی

```bash
# 1. Migration
php artisan migrate

# 2. Seed تنظیمات پیش‌فرض
php artisan db:seed --class=AlertSettingsSeeder

# 3. Cache Clear
php artisan cache:clear
php artisan view:clear

# 4. تکمیل Views (نیاز به انجام دستی)
# 5. اضافه کردن Routes
# 6. Integration در Dashboard
```

---

## 📚 منابع اضافی

- **Models:** `/app/Models/AlertSetting.php`, `/app/Models/SystemAnnouncement.php`
- **Livewire:** `/app/Livewire/Admin/`, `/app/Livewire/AnnouncementsWidget.php`
- **Migrations:** `/database/migrations/2025_10_26_*.php`
- **Seeder:** `/database/seeders/AlertSettingsSeeder.php`

---

## ✅ چک‌لیست

- [x] ساخت جدول alert_settings
- [x] ساخت جدول system_announcements
- [x] ساخت Model AlertSetting
- [x] ساخت Model SystemAnnouncement
- [x] ساخت Seeder با 11 تنظیم پیش‌فرض
- [x] ساخت AlertSettingsManagement Component
- [x] ساخت AnnouncementsManagement Component
- [x] ساخت AnnouncementsWidget Component
- [x] ارتقای AlertsPanel با تنظیمات پویا
- [ ] تکمیل Views (alert-settings-management)
- [ ] تکمیل Views (announcements-management)
- [ ] تکمیل Views (announcements-widget)
- [ ] اضافه کردن Routes
- [ ] Integration در Dashboard
- [ ] تست

---

**توسعه‌دهنده:** AI Assistant  
**تاریخ:** 1404/08/05  
**وضعیت:** Backend کامل ✅ | Frontend نیاز به تکمیل ⚠️

