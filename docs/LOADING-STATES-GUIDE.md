# ⏳ Loading States & Skeleton Loaders - راهنما

Components برای نمایش حالت بارگذاری و بهبود UX.

---

## 📦 Components موجود

### 1️⃣ `<x-loading.skeleton-card>`
Skeleton برای Card ها

### 2️⃣ `<x-loading.skeleton-table>`
Skeleton برای جداول

### 3️⃣ `<x-loading.skeleton-list>`  
Skeleton برای لیست‌ها

### 4️⃣ `<x-loading.spinner>`
Spinner چرخان

### 5️⃣ `<x-loading.overlay>`
Overlay تمام صفحه با Spinner

---

## 🚀 نحوه استفاده

### 1. Skeleton Card

```blade
{{-- Loading State --}}
@if($loading)
    <x-loading.skeleton-card />
@else
    {{-- Real Content --}}
    <div class="rounded-lg bg-white p-4">
        <h3>{{ $title }}</h3>
        <p>{{ $value }}</p>
    </div>
@endif
```

### 2. Skeleton Table

```blade
<div wire:loading.remove>
    {{-- Real Table --}}
    <table>...</table>
</div>

<div wire:loading>
    <x-loading.skeleton-table :rows="10" />
</div>
```

### 3. Skeleton List

```blade
@if($activities->isEmpty() && $loading)
    <x-loading.skeleton-list :items="5" />
@else
    @foreach($activities as $activity)
        {{-- Activity item --}}
    @endforeach
@endif
```

### 4. Spinner

```blade
{{-- Sizes: sm, md, lg, xl --}}
<x-loading.spinner size="md" />

{{-- Colors: indigo, blue, emerald, amber, rose, slate --}}
<x-loading.spinner color="emerald" />

{{-- در دکمه --}}
<button wire:loading.attr="disabled">
    <span wire:loading.remove>ذخیره</span>
    <span wire:loading>
        <x-loading.spinner size="sm" color="white" class="inline-block" />
        در حال ذخیره...
    </span>
</button>
```

### 5. Loading Overlay

```blade
<div wire:loading wire:target="save">
    <x-loading.overlay message="در حال ذخیره..." />
</div>
```

---

## 💡 با Livewire

### Method 1: wire:loading

```blade
<div>
    {{-- محتوای اصلی --}}
    <div wire:loading.remove>
        @livewire('transactions-table')
    </div>

    {{-- Skeleton --}}
    <div wire:loading>
        <x-loading.skeleton-table :rows="10" />
    </div>
</div>
```

### Method 2: Target Specific Action

```blade
<button wire:click="loadMore">بارگذاری بیشتر</button>

<div wire:loading wire:target="loadMore">
    <x-loading.spinner />
</div>
```

### Method 3: Property Binding

```blade
{{-- در Component --}}
public bool $loading = true;

public function mount()
{
    $this->loadData();
    $this->loading = false;
}
```

```blade
{{-- در View --}}
@if($loading)
    <x-loading.skeleton-list :items="10" />
@else
    {{-- محتوا --}}
@endif
```

---

## 🎨 مثال‌های کاربردی

### 1. Dashboard Cards

```blade
<div class="grid grid-cols-1 gap-4 md:grid-cols-4">
    @if($loading)
        @for($i = 0; $i < 4; $i++)
            <x-loading.skeleton-card />
        @endfor
    @else
        @foreach($stats as $stat)
            <div class="rounded-lg bg-white p-4">
                <p class="text-sm text-slate-500">{{ $stat['label'] }}</p>
                <p class="text-2xl font-bold">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    @endif
</div>
```

### 2. Form Submit Button

```blade
<button 
    type="submit" 
    wire:loading.attr="disabled"
    class="rounded-lg bg-indigo-600 px-4 py-2 text-white"
>
    <span wire:loading.remove>ثبت اطلاعات</span>
    <span wire:loading class="flex items-center gap-2">
        <x-loading.spinner size="sm" color="white" />
        در حال ثبت...
    </span>
</button>
```

### 3. Infinite Scroll

```blade
<div>
    @foreach($items as $item)
        {{-- Item --}}
    @endforeach

    {{-- Load More --}}
    <div x-intersect="$wire.loadMore()" class="py-4">
        <div wire:loading wire:target="loadMore">
            <x-loading.spinner />
            <p class="mt-2 text-center text-sm text-slate-500">در حال بارگذاری...</p>
        </div>
    </div>
</div>
```

### 4. Search with Debounce

```blade
<input 
    type="text" 
    wire:model.live.debounce.500ms="search"
    placeholder="جستجو..."
/>

<div class="mt-4">
    <div wire:loading wire:target="search">
        <x-loading.skeleton-list :items="5" />
    </div>

    <div wire:loading.remove>
        @foreach($results as $result)
            {{-- Result item --}}
        @endforeach
    </div>
</div>
```

### 5. Modal Loading

```blade
<div x-show="showModal" class="fixed inset-0 z-50">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-lg rounded-lg bg-white p-6">
            @if($loadingModal)
                <div class="space-y-4">
                    <div class="h-6 w-48 animate-pulse rounded bg-slate-200"></div>
                    <div class="h-4 w-full animate-pulse rounded bg-slate-100"></div>
                    <div class="h-4 w-3/4 animate-pulse rounded bg-slate-100"></div>
                </div>
            @else
                <h2>{{ $modalTitle }}</h2>
                <p>{{ $modalContent }}</p>
            @endif
        </div>
    </div>
</div>
```

---

## 🎯 Best Practices

### 1. Skeleton Shape Matching
```blade
{{-- Skeleton باید شکل محتوای واقعی را داشته باشد --}}

{{-- ❌ Bad --}}
<x-loading.skeleton-card />  {{-- برای لیست --}}

{{-- ✅ Good --}}
<x-loading.skeleton-list />  {{-- برای لیست --}}
```

### 2. Consistent Duration
```blade
{{-- همیشه از wire:loading.delay استفاده کنید --}}
<div wire:loading.delay>  {{-- 200ms delay --}}
    <x-loading.spinner />
</div>
```

### 3. Feedback Messages
```blade
{{-- پیام مناسب بدهید --}}
<x-loading.overlay message="در حال بارگذاری داده‌ها..." />
<x-loading.overlay message="در حال ذخیره تغییرات..." />
<x-loading.overlay message="لطفاً صبر کنید..." />
```

### 4. Button States
```blade
<button 
    wire:click="save"
    wire:loading.attr="disabled"
    wire:loading.class="opacity-50 cursor-not-allowed"
>
    <span wire:loading.remove>ذخیره</span>
    <span wire:loading>در حال ذخیره...</span>
</button>
```

---

## 🔧 سفارشی‌سازی

### Custom Skeleton

```blade
<div class="animate-pulse">
    <div class="mb-4 h-8 w-64 rounded bg-slate-200"></div>
    <div class="space-y-2">
        <div class="h-4 w-full rounded bg-slate-100"></div>
        <div class="h-4 w-5/6 rounded bg-slate-100"></div>
        <div class="h-4 w-4/6 rounded bg-slate-100"></div>
    </div>
</div>
```

### Custom Spinner Colors

```blade
{{-- در spinner.blade.php می‌توانید رنگ‌های بیشتر اضافه کنید --}}
$colorClasses = [
    'purple' => 'border-purple-600',
    'pink' => 'border-pink-600',
    // ...
];
```

---

## 📱 Responsive Skeletons

```blade
<div class="grid gap-4">
    {{-- Mobile: 1 col, Desktop: 3 cols --}}
    <div class="grid-cols-1 md:grid-cols-3">
        @if($loading)
            @for($i = 0; $i < 3; $i++)
                <x-loading.skeleton-card />
            @endfor
        @endif
    </div>
</div>
```

---

## ⚡ Performance Tips

1. **Delay Loading Indicators** - از `wire:loading.delay` استفاده کنید
2. **Skeleton Count** - تعداد skeleton را معقول نگه دارید (5-10)
3. **Lazy Loading** - برای محتوای سنگین از lazy load استفاده کنید
4. **Optimize Images** - در skeleton ها از placeholder استفاده کنید

---

## 🧪 مثال کامل: Transaction Table

```blade
<div>
    {{-- Header --}}
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold">تراکنش‌ها</h2>
        <button wire:click="refresh" wire:loading.attr="disabled">
            <iconify-icon 
                icon="lucide:refresh-cw" 
                wire:loading.class="animate-spin"
            ></iconify-icon>
        </button>
    </div>

    {{-- Content --}}
    <div wire:loading.remove>
        <table class="w-full">
            @foreach($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->reference }}</td>
                    <td>{{ $transaction->amount }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    {{-- Loading --}}
    <div wire:loading>
        <x-loading.skeleton-table :rows="10" />
    </div>
</div>
```

---

**نسخه:** 1.0.0  
**تاریخ:** 26 آبان 1404  
**وضعیت:** ✅ آماده استفاده

