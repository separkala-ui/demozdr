# 📅 Persian Datepicker - راهنمای کامل

**تاریخ:** 1404/08/05  
**نسخه:** 2.0.0  
**وضعیت:** ✅ Production Ready

---

## 🎯 خلاصه تغییرات

تقویم شمسی **واقعی** با استفاده از:
- ✅ `PersianDate` - تبدیل دقیق تاریخ
- ✅ `Flatpickr` - UI/UX حرفه‌ای
- ✅ `jalaali-js` - محاسبات شمسی
- ✅ تزئین کامل (ماه‌ها، روزها، اعداد فارسی)

---

## 📦 نصب و تنظیمات

### 1. Package های نصب شده

```bash
npm install persian-date --save
```

### 2. Import ها در `app.js`

```javascript
import PersianDate from "persian-date";
```

---

## 🔧 نحوه استفاده

### در Blade Template

```blade
<input
    type="text"
    class="form-control"
    placeholder="1404/08/04 14:30"
    x-data
    x-init="window.initPersianDatepicker($el, { enableTime: true })"
    dir="rtl"
/>
```

### تنظیمات

| پارامتر | توضیح | مقدار پیش‌فرض |
|---------|-------|---------------|
| `enableTime` | فعال‌سازی انتخابگر زمان | `false` |

---

## ⚙️ توابع JavaScript

### 1. `initPersianDatepicker(element, options)`

**توضیح:** تابع اصلی برای اضافه کردن date picker شمسی

**مثال:**
```javascript
window.initPersianDatepicker(document.querySelector('#date'), {
    enableTime: true
});
```

### 2. `formatPersianDate(date, includeTime)`

**توضیح:** فرمت کردن تاریخ میلادی به شمسی

**مثال:**
```javascript
formatPersianDate(new Date(), true);
// خروجی: "1404/08/04 14:30"
```

### 3. `parsePersianDateToGregorian(value, includeTime)`

**توضیح:** تبدیل تاریخ شمسی به میلادی

**مثال:**
```javascript
parsePersianDateToGregorian("1404/08/04 14:30", true);
// خروجی: Date object (2025-10-26 14:30)
```

---

## 🎨 ویژگی‌های UI/UX

### ✅ تزئین کامل تقویم

1. **ماه‌های فارسی:**
   - فروردین، اردیبهشت، خرداد، ...

2. **روزهای فارسی:**
   - ش، ی، د، س، چ، پ، ج

3. **اعداد فارسی:**
   - ۱۴۰۴/۰۸/۰۴

4. **شروع هفته:**
   - شنبه (مطابق تقویم ایرانی)

---

## 📋 فرمت‌های قابل قبول

### ورودی کاربر (Input)

✅ **فرمت صحیح:**
```
1404/08/04
1404-08-04
1404/08/04 14:30
1404-08-04 14:30
۱۴۰۴/۰۸/۰۴
```

❌ **فرمت غلط:**
```
2025-10-26
October 26, 2025
26/10/2025
```

### خروجی (Output)

- **بدون زمان:** `1404/08/04`
- **با زمان:** `1404/08/04 14:30`
- **ذخیره در دیتابیس:** `YYYY-MM-DD HH:mm` (میلادی)

---

## 🔄 نحوه کار (Flow)

```
1. کاربر تاریخ شمسی وارد می‌کند
   ↓
2. parsePersianDateToGregorian() آن را به میلادی تبدیل می‌کند
   ↓
3. Flatpickr تاریخ میلادی را مدیریت می‌کند
   ↓
4. decorateCalendar() تقویم را شمسی می‌کند
   ↓
5. formatPersianDate() خروجی را شمسی نمایش می‌دهد
   ↓
6. Backend تاریخ میلادی را دریافت می‌کند
```

---

## 🐛 رفع مشکلات رایج

### 1. تاریخ شمسی نمایش نمی‌دهد

**راه حل:**
```bash
npm run build
php artisan optimize
```

### 2. خطای "PersianDate is not defined"

**راه حل:** بررسی import:
```javascript
import PersianDate from "persian-date";
```

### 3. تقویم میلادی است

**راه حل:** استفاده از `initPersianDatepicker` به جای `initJalaliDatepicker`:
```blade
x-init="window.initPersianDatepicker($el, { enableTime: true })"
```

### 4. فرمت تاریخ اشتباه است

**راه حل:** اضافه کردن `dir="rtl"` و placeholder مناسب:
```blade
<input dir="rtl" placeholder="1404/08/04 14:30" />
```

---

## 🧪 تست

### تست دستی

1. **باز کردن صفحه:** `/admin/petty-cash/12/transactions`
2. **کلیک روی فیلد تاریخ**
3. **بررسی:**
   - ✅ ماه‌ها فارسی هستند؟
   - ✅ روزها از شنبه شروع می‌شوند؟
   - ✅ اعداد فارسی هستند؟
   - ✅ تاریخ شمسی ذخیره می‌شود؟

### تست خودکار

```javascript
// Test formatPersianDate
const date = new Date(2025, 9, 26, 14, 30); // October 26, 2025 14:30
const formatted = formatPersianDate(date, true);
console.assert(formatted === '1404/08/04 14:30', 'Format failed');

// Test parsePersianDateToGregorian
const parsed = parsePersianDateToGregorian('1404/08/04 14:30', true);
console.assert(parsed.getFullYear() === 2025, 'Parse failed');
console.assert(parsed.getMonth() === 9, 'Parse month failed');
console.assert(parsed.getDate() === 26, 'Parse day failed');
```

---

## 📊 مقایسه: قبل vs بعد

| ویژگی | قبل | بعد |
|-------|-----|-----|
| **تقویم** | میلادی | ✅ شمسی |
| **ماه‌ها** | English | ✅ فارسی |
| **اعداد** | 123 | ✅ ۱۲۳ |
| **شروع هفته** | یکشنبه | ✅ شنبه |
| **Input** | دستی فقط | ✅ Picker شمسی |
| **Format** | 2025-10-26 | ✅ 1404/08/04 |

---

## 🚀 ارتقاءهای آینده

- [ ] Theme سفارشی برای تقویم
- [ ] Shortcuts (امروز، دیروز، ...)
- [ ] Range Picker (از - تا)
- [ ] افزودن تقویم قمری
- [ ] Disable کردن روزهای تعطیل

---

## 📚 منابع

1. **PersianDate:** https://github.com/babakhani/PersianDate
2. **Flatpickr:** https://flatpickr.js.org/
3. **jalaali-js:** https://github.com/jalaali/jalaali-js

---

## ✅ Checklist نهایی

- [x] نصب `persian-date`
- [x] import در `app.js`
- [x] تابع `formatPersianDate()`
- [x] تابع `parsePersianDateToGregorian()`
- [x] تابع `initPersianDatepicker()`
- [x] تزئین تقویم با `decorateCalendar()`
- [x] اعمال در `transaction-form.blade.php`
- [x] آیکون و راهنما
- [x] Build و Optimize
- [x] تست نهایی

---

## 🎉 نتیجه

**Persian Datepicker V2.0** آماده استفاده است!

🔹 تقویم **واقعا** شمسی  
🔹 UI/UX **حرفه‌ای**  
🔹 فرمت **صحیح**  
🔹 کاربرد **آسان**

---

**توسعه‌دهنده:** AI Assistant  
**تاریخ تکمیل:** 1404/08/05  
**وضعیت:** ✅ Production Ready

