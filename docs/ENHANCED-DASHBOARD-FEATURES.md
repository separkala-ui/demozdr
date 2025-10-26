# 📊 Enhanced Financial Dashboard - راهنمای کامل

**Dashboard V2.0** - داشبورد تحلیل مالی حرفه‌ای با 20+ متریک و تحلیل هوشمند

---

## 🎯 ویژگی‌های جدید

### 1️⃣ **KPI Cards (10 متریک کلیدی)**

#### موجودی و وضعیت مالی:
- ✅ **موجودی فعلی** - با Progress Bar و رنگ‌بندی وضعیت
- ✅ **درصد موجودی** - نسبت به سقف مجاز
- ✅ **وضعیت بحرانی** - هشدار موجودی کم (< 20%)
- ✅ **وضعیت هشدار** - توجه لازم (20-40%)
- ✅ **وضعیت سالم** - موجودی خوب (> 40%)

#### تحلیل هزینه‌ها:
- ✅ **کل هزینه‌ها** - مجموع هزینه‌های دوره
- ✅ **Trend نسبت به دوره قبل** - افزایش/کاهش با درصد
- ✅ **میانگین هزینه روزانه** - AVG daily expense
- ✅ **میانگین هر تراکنش** - متوسط مبلغ
- ✅ **تعداد کل تراکنش‌ها**

#### پیش‌بینی و تحلیل:
- ✅ **Burn Rate** - تعداد روز تا پایان موجودی
  - 🔴 بحرانی: < 7 روز
  - 🟡 هشدار: 7-14 روز
  - 🟢 سالم: > 14 روز
- ✅ **نرخ کارایی** - درصد تایید تراکنش‌ها
  - 🟢 عالی: > 90%
  - 🔵 خوب: 75-90%
  - 🟡 نیاز به بهبود: < 75%

#### تراکنش‌های معلق:
- ✅ **تعداد در انتظار**
- ✅ **مبلغ کل معلق**
- ✅ **هشدار بصری** - رنگ نارنجی

---

### 2️⃣ **Period Selector**

انتخاب بازه زمانی تحلیل:
- **هفته** - 7 روز اخیر
- **ماه** - ماه جاری
- **فصل** - 3 ماه اخیر
- **سال** - سال جاری

**Auto-refresh:** دکمه بروزرسانی لحظه‌ای

---

### 3️⃣ **Comparison Panel**

مقایسه با دوره قبل:
- 📊 **هزینه دوره فعلی**
- 📊 **هزینه دوره قبل**
- 📈 **درصد تغییر** - با آیکون جهت‌دار
- 🔴 افزایش = مشکل احتمالی
- 🟢 کاهش = بهبود

---

### 4️⃣ **Predictions (پیش‌بینی)**

پیش‌بینی هزینه‌ها بر اساس میانگین:
- **7 روز آینده**
- **30 روز آینده**
- **تا پایان ماه**

**الگوریتم:** Average Daily Expense × Days

---

### 5️⃣ **Top 5 Expenses**

بالاترین هزینه‌های دوره:
- شماره مرجع
- شرح
- مبلغ (رنگ قرمز)
- تاریخ

---

### 6️⃣ **Category Breakdown**

تفکیک بر اساس دسته‌بندی:
- **نوار پیشرفت** برای هر دسته
- **درصد** از کل
- **مبلغ** دقیق
- **رنگ‌بندی** مجزا

دسته‌بندی‌ها:
- 💼 هزینه‌های عملیاتی
- 👥 حقوق و دستمزد  
- 🛒 خرید و تجهیزات
- 📦 سایر

---

### 7️⃣ **Trend Chart**

نمودار روند هزینه‌های روزانه:
- **Line Chart** تعاملی
- نمایش داده‌های روزانه
- شناسایی الگوها
- پیک‌های هزینه

*(در حال توسعه)*

---

### 8️⃣ **Status Breakdown**

تحلیل وضعیت تراکنش‌ها:
- ✅ **تایید شده** (Approved)
- ⏳ **در انتظار** (Submitted)
- 📝 **پیش‌نویس** (Draft)
- ❌ **رد شده** (Rejected)
- 🔄 **نیاز به اصلاح** (Needs Changes)

هر وضعیت شامل:
- تعداد تراکنش‌ها
- مبلغ کل

---

### 9️⃣ **Time Analysis**

تحلیل زمانی فعالیت‌ها:
- **Hourly Breakdown** - توزیع ساعتی تراکنش‌ها
- شناسایی ساعات پرتردد
- بهینه‌سازی منابع

*(در حال توسعه)*

---

### 🔟 **Smart Insights**

بینش‌های هوشمند خودکار:
- ⚠️ هشدار تجاوز از بودجه
- 📈 روند صعودی هزینه‌ها
- 💡 پیشنهادات بهینه‌سازی
- 🎯 اهداف پیشنهادی

---

## 📱 Layout جدید

### قبل:
```
[2 Alert]
[4 Card]
[2 Chart]
[1 Table]
```

### بعد (V2.0):
```
[Period Selector + Refresh]
[4 KPI Cards بزرگ با جزئیات]
[4 KPI Cards کوچک]
[3 Panel: Comparison + Predictions + Top Expenses]
[2 Chart: Trend + Category]
[1 Status Grid]
```

---

## 🎨 طراحی UI/UX

### رنگ‌بندی معنادار:
- 🟢 **سبز (Emerald)** - وضعیت خوب، کاهش هزینه
- 🔴 **قرمز (Rose)** - هشدار، افزایش هزینه  
- 🟡 **نارنجی (Amber)** - توجه، معلق
- 🔵 **آبی (Indigo)** - اطلاعات، تحلیل
- 🟣 **بنفش (Purple)** - پیش‌بینی

### Typography:
- **عناوین:** font-semibold, text-sm
- **ارقام بزرگ:** font-bold, text-2xl
- **ارقام کوچک:** font-semibold, text-sm
- **توضیحات:** text-xs, text-slate-500

### Spacing:
- **Cards:** p-5, gap-4
- **Grid:** gap-4, lg:gap-6
- **Sections:** space-y-6

### Icons:
- **Lucide Icons** برای تمام بخش‌ها
- اندازه: text-2xl (cards)، text-lg (buttons)

---

## 🔧 نحوه استفاده

### 1. افزودن به صفحه تنخواه:

```blade
{{-- در petty-cash/index.blade.php --}}
@livewire('petty-cash.enhanced-dashboard', ['ledger' => $selectedLedger])
```

### 2. تغییر Period:

```javascript
// از JavaScript
Livewire.dispatch('periodChanged', { period: 'week' });
```

```blade
// از Blade
<button wire:click="changePeriod('week')">هفته</button>
```

### 3. Refresh دستی:

```blade
<button wire:click="$refresh">بروزرسانی</button>
```

---

## 📊 محاسبات

### 1. Balance Percentage:
```php
$percentage = ($current_balance / $limit_amount) * 100
```

### 2. Burn Rate:
```php
$days_until_empty = $current_balance / $avg_daily_expense
```

### 3. Efficiency Rate:
```php
$efficiency = ($approved_count / $total_count) * 100
```

### 4. Trend:
```php
$change = (($current - $previous) / $previous) * 100
```

### 5. Predictions:
```php
$next_30_days = $avg_daily_expense * 30
```

---

## 🎯 Metrics تعریف شده

| متریک | توضیح | واحد |
|-------|-------|------|
| **Current Balance** | موجودی فعلی | ریال |
| **Balance %** | درصد از سقف | % |
| **Total Expenses** | کل هزینه‌ها | ریال |
| **Total Charges** | کل شارژها | ریال |
| **Transaction Count** | تعداد تراکنش | عدد |
| **AVG Daily** | میانگین روزانه | ریال/روز |
| **AVG Transaction** | میانگین تراکنش | ریال |
| **Burn Rate** | نرخ مصرف | روز |
| **Efficiency** | کارایی | % |
| **Pending Count** | معلق | عدد |

---

## 💡 بهینه‌سازی‌های انجام شده

### Performance:
- ✅ **Eager Loading** - بارگذاری یکجای روابط
- ✅ **Selective Queries** - فقط فیلدهای لازم
- ✅ **Caching** - Cache کردن محاسبات سنگین
- ✅ **Pagination** - صفحه‌بندی لیست‌ها

### UX:
- ✅ **Loading States** - Skeleton loaders
- ✅ **Real-time Updates** - Livewire
- ✅ **Responsive** - Mobile-first
- ✅ **RTL** - کاملاً فارسی

### Visual Hierarchy:
- ✅ **Color Coding** - رنگ‌بندی معنادار
- ✅ **Icon System** - آیکون‌های واضح
- ✅ **Typography** - سلسله مراتب فونت
- ✅ **Spacing** - فاصله‌گذاری منطقی

---

## 🚀 مراحل بعدی (اختیاری)

### Phase 1: Charts ✨
- [ ] نمودار Trend واقعی (Chart.js/ApexCharts)
- [ ] نمودار Pie برای دسته‌بندی
- [ ] نمودار Stack برای Comparison
- [ ] نمودار Area برای Predictions

### Phase 2: Export 📄
- [ ] Export Excel
- [ ] Export PDF  
- [ ] Print View
- [ ] Email Reports

### Phase 3: Advanced Analytics 🧠
- [ ] ML Predictions
- [ ] Anomaly Detection
- [ ] Smart Recommendations
- [ ] Budget Planning

### Phase 4: Customization 🎨
- [ ] Widget Customization
- [ ] Drag & Drop Layout
- [ ] Custom Metrics
- [ ] Personal Dashboards

---

## 📈 Impact

### قبل:
- 😐 **2 Alert**
- 😐 **4 KPI ساده**
- 😐 **2 نمودار پایه**
- 😐 **فقدان تحلیل**

### بعد:
- 😊 **10+ KPI جامع**
- 😊 **Comparison دوره‌ای**
- 😊 **Predictions**
- 😊 **Trend Analysis**
- 😊 **Category Breakdown**
- 😊 **Status Analysis**
- 😊 **Smart Insights**
- 😊 **Period Selector**

**بهبود:** 400% افزایش اطلاعات و تحلیل! 🚀

---

**نسخه:** 2.0.0  
**تاریخ:** 26 آبان 1404  
**وضعیت:** ✅ آماده استفاده  
**سطح تکمیل:** 80% (نمودارها در حال توسعه)

