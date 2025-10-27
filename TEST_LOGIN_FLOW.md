# 🧪 LOGIN SYSTEM TEST FLOW

## تست 1: Superadmin ورود
```
Email/Username: dr.mostafazade یا dr.mostafazade@gmail.com
Password: (superadmin password)

Expected Result:
- ✅ Login موفق
- ✅ Redirect به /admin
- ✅ Dashboard بارگیری می‌شود
- ✅ لاگ: "✅ User authenticated successfully"
- ✅ لاگ: "→ Redirecting to admin dashboard"
```

---

## تست 2: Admin ورود
```
Email/Username: admin یا admin@example.com
Password: (admin password)

Expected Result:
- ✅ Login موفق
- ✅ Redirect به /admin
- ✅ Dashboard بارگیری می‌شود
- ✅ لاگ‌های مشابه Superadmin
```

---

## تست 3: Regular User (Subscriber) ورود
```
Email/Username: subscriber یا subscriber@example.com
Password: (subscriber password)

Expected Result:
- ✅ Login موفق
- ✅ Redirect به /admin (چون دارای dashboard.view)
- ✅ Dashboard بارگیری می‌شود
```

---

## تست 4: Invalid Credentials
```
Email/Username: any@email.com
Password: wrongpassword

Expected Result:
- ❌ Login ناموفق
- ✅ بازگشت به صفحه login
- ✅ پیام خطا: "Invalid credentials"
```

---

## تست 5: Authenticated User بازدید از /login
```
1. User باید وارد شده باشد
2. بازدید از /login

Expected Result:
- ✅ RedirectIfAuthenticated middleware trigger می‌شود
- ✅ User redirect می‌شود به /admin
- ✅ صفحه login نمایش داده نمی‌شود
```

---

## تست 6: POST /login بدون guest middleware
```
1. صفحه login باز کنید
2. Credentials وارد کنید
3. فرم submit کنید

Expected Result:
- ✅ POST request به /login ارسال می‌شود
- ✅ middleware: [web, recaptcha, throttle] (NO guest)
- ✅ attemptLogin() سعی می‌کند email و username
- ✅ اگر موفق: authenticated() method فراخوانی می‌شود
- ✅ Redirect به /admin
- ✅ **NO infinite loop** ✅
```

---

## 🔍 چک لاگ‌ها

```bash
# Live watching
tail -f storage/logs/laravel.log | grep -E "✅|→|🔄|↪️"

# یا جستجو برای محتوا
grep "User authenticated" storage/logs/laravel.log
grep "Redirecting" storage/logs/laravel.log
grep "Auth check" storage/logs/laravel.log
```

---

## 📋 Checklist

- [ ] تست 1 موفق (Superadmin)
- [ ] تست 2 موفق (Admin)
- [ ] تست 3 موفق (Subscriber)
- [ ] تست 4 موفق (Invalid credentials)
- [ ] تست 5 موفق (Authenticated user /login)
- [ ] تست 6 موفق (NO infinite loop)
- [ ] لاگ‌ها صحیح هستند
- [ ] هیچ خطایی در logs نیست

---

## 🐛 Troubleshooting

### اگر حلقه ورود هنوز وجود دارد:

1. **Cache پاک کنید:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

2. **Routes چک کنید:**
   ```bash
   php artisan route:list | grep login
   ```

3. **Middleware order چک کنید:**
   ```bash
   # Kernel.php را ببینید
   cat app/Http/Kernel.php | grep -A 15 "middlewareGroups"
   ```

4. **RedirectIfAuthenticated middleware:**
   ```bash
   # Middleware را چک کنید
   cat app/Http/Middleware/RedirectIfAuthenticated.php
   ```

5. **لاگ‌ها را دقیق بررسی کنید:**
   ```bash
   tail -100 storage/logs/laravel.log
   ```

---

## ✅ نتایج موفق

اگر تمام تست‌ها موفق بود، سیستم صحیح است! 🎉

