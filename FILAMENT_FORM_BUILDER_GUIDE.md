# 📋 Filament Form Builder - راهنمای استفاده

## ✅ مشکل حل شد!

**Filament Admin Panel** اکنون **کامل‌اً کار می‌کند** و **FormTemplate Resources** دیده می‌شود.

---

## 🎯 نحوه دسترسی:

### **گزینه 1: از داخل Admin Panel (توصیه شده)**
1. ورود: `https://yourdomain.com/admin`
2. منوی سایدبار → **"عملیات و کنترل کیفیت"**
3. زیرمنو → **"الگوهای فرم"** یا **"نتایج فرم‌ها"**

### **گزینه 2: دسترسی مستقیم**
- الگوهای فرم: `https://yourdomain.com/filament/form-templates`
- نتایج فرم‌ها: `https://yourdomain.com/filament/form-reports`

---

## 🎨 Filament Form Builder - Features

### **1. ایجاد فرم جدید**
```
مسیر: /filament/form-templates → "Create"

مراحل:
1. عنوان: "فرم کنترل کیفیت"
2. توضیحات: "بررسی کیفیت محصولات"
3. دسته‌بندی: "🔍 کنترل کیفیت"
4. فعال: ✓ فعال کنید
```

### **2. افزودن فیلدها**
```
سه نوع روش برای افزودن فیلدها:

① نوع Text:
   - برای ورودی متن عادی
   - مثال: "نام محصول"

② نوع Number:
   - برای ورودی عددی
   - مثال: "اندازه‌گیری (میلی‌متر)"
   - قاعده: numeric|between:50,100

③ نوع Select:
   - برای فهرست انتخاب
   - مثال: "وضعیت" → تایید شد / رد شد
```

### **3. ذخیره فرم**
```
روی دکمه "Save" کلیک کنید
فرم ایجاد شد! ✅
```

---

## 🧪 فرم نمونه (Dummy Data)

یک فرم نمونه **از قبل ایجاد شده**:

```
📋 فرم کنترل کیفیت (ID: 1)
├── کد محصول (text)
├── اندازه‌گیری (number, 50-100 میلی‌متر)
└── وضعیت (select: تایید/رد/تعمیر)
```

**آن را می‌توانید ویرایش کنید یا حذف کنید!**

---

## 📊 ساختار دیتابیس

```sql
form_templates
├── id
├── title (عنوان)
├── description (توضیحات)
├── category (qc / inspection / production / other)
├── is_active (فعال/غیرفعال)
├── created_by (کاربر سازنده)
└── timestamps

form_template_fields
├── id
├── template_id
├── name (نام فیلد)
├── label (برچسب)
├── type (text/number/date/select/checkbox/textarea/file)
├── order (ترتیب)
├── required (الزامی)
├── options (گزینه‌ها برای select)
└── validation (قوانین اعتبارسنجی)

form_reports
├── id
├── template_id
├── reporter_id (کاربری که تکمیل کرد)
├── status (draft/submitted/reviewed/approved/rejected)
├── overall_score
├── notes
└── completed_at

form_report_answers
├── id
├── report_id
├── field_id
└── value (جواب کاربر)
```

---

## 🔐 Permissions

```
form.view        - مشاهده فرم‌ها
form.create      - ایجاد فرم‌های جدید
form.edit        - ویرایش فرم‌ها
form.delete      - حذف فرم‌ها
```

---

## 📱 نمایش سازی در Frontend

### مسیر: `/admin/forms/{id}/fill`
```blade
<form action="{{ route('forms.submit', $template) }}" method="POST">
    @foreach($fields as $field)
        <!-- Dynamic field rendering -->
    @endforeach
    <button type="submit">ثبت فرم</button>
</form>
```

---

## 🚀 نکات مهم

✅ **Filament Admin Panel** کامل‌اً تنظیم شد
✅ **Resources discovery** فعال است
✅ **Menu items** اضافه شدند
✅ **Sample data** ایجاد شد
✅ **Permissions** کنترل می‌شود

---

## 🔧 Troubleshooting

### مشکل: فرم نمایش نمی‌شود
**حل**: 
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### مشکل: خطای "Permission denied"
**حل**: کاربر باید دسترسی `form.view` داشته باشد

### مشکل: Filament dashboard نیست
**حل**: مطمئن شوید از `/filament` استفاده می‌کنید (نه `/admin`)

---

## 📞 مشکلات رایج

| مشکل | حل |
|-----|-----|
| صفحه خالی است | Cache پاک کنید |
| فرم ذخیره نمی‌شود | مطمئن شوید required fields پر شده‌اند |
| منو دیده نمی‌شود | دسترسی `form.view` را چک کنید |
| Filament 404 | مطمئن شوید از `/filament` استفاده می‌کنید |

---

## 🎉 نتیجه

**Filament Form Builder اکنون آماده است!**

```
صفحه: /filament/form-templates
عملیات: Create, Read, Update, Delete
دسترسی: Through admin panel menu
```

