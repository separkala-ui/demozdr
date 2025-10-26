# 📑 Tab System - راهنمای استفاده

سیستم Tab برای سازماندهی محتوا در داشبوردها و صفحات پیچیده.

---

## 📦 Components

### 1️⃣ `<x-tab-system>`
Component اصلی که Navigation و Container را مدیریت می‌کند.

### 2️⃣ `<x-tab-panel>`
محتوای هر Tab که داخل `<x-tab-system>` قرار می‌گیرد.

---

## 🚀 نحوه استفاده

### مثال ساده:

```blade
<x-tab-system :tabs="[
    ['id' => 'overview', 'label' => 'نمای کلی', 'icon' => 'lucide:layout-dashboard'],
    ['id' => 'transactions', 'label' => 'تراکنش‌ها', 'icon' => 'lucide:list', 'badge' => 5],
    ['id' => 'reports', 'label' => 'گزارشات', 'icon' => 'lucide:file-text'],
]">
    <x-tab-panel id="overview">
        <h2>نمای کلی</h2>
        <p>محتوای Tab اول</p>
    </x-tab-panel>

    <x-tab-panel id="transactions">
        <h2>تراکنش‌ها</h2>
        <p>لیست تراکنش‌ها</p>
    </x-tab-panel>

    <x-tab-panel id="reports">
        <h2>گزارشات</h2>
        <p>گزارشات سیستم</p>
    </x-tab-panel>
</x-tab-system>
```

---

## ⚙️ تنظیمات Tabs

### ساختار هر Tab:

```php
[
    'id' => 'unique-id',           // الزامی - شناسه یکتا
    'label' => 'عنوان Tab',       // الزامی - متن نمایشی
    'icon' => 'lucide:icon-name',  // اختیاری - آیکون
    'badge' => 10,                 // اختیاری - Badge با عدد
]
```

### مثال کامل:

```php
$tabs = [
    [
        'id' => 'dashboard',
        'label' => __('داشبورد'),
        'icon' => 'lucide:layout-dashboard',
    ],
    [
        'id' => 'pending',
        'label' => __('در انتظار'),
        'icon' => 'lucide:clock',
        'badge' => $pendingCount,  // نمایش تعداد
    ],
    [
        'id' => 'approved',
        'label' => __('تایید شده'),
        'icon' => 'lucide:check-circle',
        'badge' => $approvedCount,
    ],
    [
        'id' => 'settings',
        'label' => __('تنظیمات'),
        'icon' => 'lucide:settings',
    ],
];
```

---

## 🎨 استفاده در Dashboard تنخواه

### Controller:

```php
public function index(Request $request)
{
    $selectedTab = $request->get('tab', 'overview');
    
    // محاسبه تعداد برای badge ها
    $pendingTransactions = Transaction::where('status', 'pending')->count();
    $needsReview = Transaction::where('status', 'needs_changes')->count();
    
    $tabs = [
        ['id' => 'overview', 'label' => __('نمای کلی'), 'icon' => 'lucide:layout-dashboard'],
        ['id' => 'transactions', 'label' => __('تراکنش‌ها'), 'icon' => 'lucide:list'],
        ['id' => 'pending', 'label' => __('در انتظار'), 'icon' => 'lucide:clock', 'badge' => $pendingTransactions],
        ['id' => 'review', 'label' => __('نیاز به بازبینی'), 'icon' => 'lucide:alert-circle', 'badge' => $needsReview],
        ['id' => 'analytics', 'label' => __('تحلیل‌ها'), 'icon' => 'lucide:bar-chart'],
        ['id' => 'settings', 'label' => __('تنظیمات'), 'icon' => 'lucide:settings'],
    ];
    
    return view('backend.pages.petty-cash.index', compact('tabs', 'selectedTab'));
}
```

### View:

```blade
<x-tab-system :tabs="$tabs" :activeTab="$selectedTab">
    
    {{-- Tab 1: نمای کلی --}}
    <x-tab-panel id="overview">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            {{-- آمار و نمودارها --}}
        </div>
    </x-tab-panel>

    {{-- Tab 2: تراکنش‌ها --}}
    <x-tab-panel id="transactions">
        @livewire('petty-cash.transactions-table', ['ledger' => $selectedLedger])
    </x-tab-panel>

    {{-- Tab 3: در انتظار تایید --}}
    <x-tab-panel id="pending">
        @livewire('petty-cash.transactions-table', [
            'ledger' => $selectedLedger,
            'status' => 'pending'
        ])
    </x-tab-panel>

    {{-- Tab 4: نیاز به بازبینی --}}
    <x-tab-panel id="review">
        @livewire('petty-cash.transactions-table', [
            'ledger' => $selectedLedger,
            'status' => 'needs_changes'
        ])
    </x-tab-panel>

    {{-- Tab 5: تحلیل‌ها --}}
    <x-tab-panel id="analytics">
        {{-- Charts and Analytics --}}
    </x-tab-panel>

    {{-- Tab 6: تنظیمات --}}
    <x-tab-panel id="settings">
        {{-- Settings Form --}}
    </x-tab-panel>

</x-tab-system>
```

---

## ✨ ویژگی‌ها

- ✅ **Alpine.js Integration** - تغییر Tab بدون reload
- ✅ **Badge Support** - نمایش تعداد/اعلان
- ✅ **Icon Support** - آیکون برای هر Tab
- ✅ **Smooth Animation** - انیمیشن نرم با Transition
- ✅ **RTL Support** - کاملاً RTL
- ✅ **Responsive** - برای موبایل بهینه شده
- ✅ **Active State** - Highlight خودکار Tab فعال
- ✅ **Accessibility** - با استاندارد ARIA

---

## 🎨 سفارشی‌سازی

### تغییر رنگ Tab فعال:

```blade
{{-- در tab-system.blade.php --}}
<button
    :class="activeTab === '{{ $tab['id'] }}' 
        ? 'border-purple-500 bg-purple-50 text-purple-700'  {{-- رنگ دلخواه --}}
        : 'border-transparent text-slate-600'"
    ...
>
```

### اضافه کردن Tooltip:

```blade
<button
    @click="activeTab = '{{ $tab['id'] }}'"
    title="{{ $tab['description'] ?? '' }}"  {{-- Tooltip --}}
    ...
>
```

### Custom Class ها:

```blade
<x-tab-panel id="special" class="bg-gradient-to-br from-indigo-50 to-purple-50">
    {{-- محتوا با Background سفارشی --}}
</x-tab-panel>
```

---

## 🔄 با Livewire

### Option 1: Query String

```php
// در Livewire Component
public string $activeTab = 'overview';

protected $queryString = ['activeTab'];

public function updatedActiveTab()
{
    // اعمال فیلتر بر اساس Tab
}
```

```blade
<x-tab-system :tabs="$tabs" :activeTab="$activeTab">
    <x-tab-panel id="overview" wire:key="tab-overview">
        {{-- محتوا --}}
    </x-tab-panel>
</x-tab-system>
```

### Option 2: Livewire Events

```blade
<button
    @click="activeTab = 'transactions'; $wire.call('loadTransactions')"
    ...
>
```

---

## 📱 Responsive

Tab ها به صورت خودکار در موبایل به حالت Wrap می‌روند:

```blade
<div class="flex flex-wrap gap-1">  {{-- flex-wrap --}}
    {{-- Tab buttons --}}
</div>
```

---

## 🧪 مثال‌های بیشتر

### 1. Tab با محتوای Livewire:

```blade
<x-tab-system :tabs="$tabs">
    <x-tab-panel id="users">
        @livewire('admin.users-table')
    </x-tab-panel>
</x-tab-system>
```

### 2. Tab با Form:

```blade
<x-tab-system :tabs="$tabs">
    <x-tab-panel id="edit">
        <form wire:submit.prevent="save">
            {{-- Form fields --}}
        </form>
    </x-tab-panel>
</x-tab-system>
```

### 3. Tab با Grid Layout:

```blade
<x-tab-system :tabs="$tabs">
    <x-tab-panel id="gallery">
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            {{-- Images --}}
        </div>
    </x-tab-panel>
</x-tab-system>
```

---

## 💡 توصیه‌ها

1. **حداکثر 6-7 Tab** - بیشتر از این کاربر را گیج می‌کند
2. **Badge را به روز نگه دارید** - با Livewire یا Polling
3. **Loading State** - برای محتوای سنگین از Skeleton استفاده کنید
4. **Default Tab** - همیشه اولین Tab را active کنید
5. **Icon ها** - از iconify-icon یا مشابه استفاده کنید

---

**نسخه:** 1.0.0  
**تاریخ:** 26 آبان 1404  
**وضعیت:** ✅ آماده استفاده

