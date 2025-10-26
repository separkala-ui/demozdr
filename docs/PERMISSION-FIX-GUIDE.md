# 🔧 راهنمای رفع مشکلات Permission

**تاریخ:** 1404/08/05  
**مشکل:** `Permission denied` در `storage/framework/views`

---

## 🐛 علائم مشکل:

```
file_put_contents(/var/www/zdr/storage/framework/views/xxxxx.php): 
Failed to open stream: Permission denied
```

---

## 🎯 راه حل سریع (Quick Fix)

### دستور یکجا:

```bash
cd /var/www/zdr

# تغییر مالکیت
sudo chown -R www-data:www-data storage bootstrap/cache

# تغییر دسترسی
sudo chmod -R 775 storage bootstrap/cache

# پاک کردن کش‌ها
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Optimize مجدد
php artisan optimize
```

---

## 📋 مراحل تفصیلی:

### 1. تغییر مالکیت (Ownership)

```bash
sudo chown -R www-data:www-data /var/www/zdr/storage
sudo chown -R www-data:www-data /var/www/zdr/bootstrap/cache
```

**توضیح:**
- `www-data` = کاربر وب سرور (Nginx/Apache)
- `-R` = Recursive (همه زیرپوشه‌ها)

---

### 2. تغییر دسترسی (Permissions)

```bash
sudo chmod -R 775 /var/www/zdr/storage
sudo chmod -R 775 /var/www/zdr/bootstrap/cache
```

**توضیح:**
- `7` (Owner) = Read + Write + Execute
- `7` (Group) = Read + Write + Execute
- `5` (Others) = Read + Execute

---

### 3. پاک کردن کش‌ها

```bash
php artisan view:clear      # کش‌های View
php artisan cache:clear     # کش‌های Application
php artisan config:clear    # کش‌های Config
php artisan route:clear     # کش‌های Route
```

---

### 4. Optimize مجدد

```bash
php artisan optimize
```

---

## 🔍 بررسی دسترسی‌ها:

```bash
# بررسی storage/framework/views
ls -la storage/framework/views/ | head -10

# باید ببینید:
drwxrwxr-x 2 www-data www-data ...
-rw-r--r-- 1 www-data www-data ...
```

**اگر `root` دیدید:**
```bash
sudo chown -R www-data:www-data storage/framework/views/
```

---

## 🚫 اشتباهات رایج:

### ❌ اشتباه 1: اجرای optimize با sudo

```bash
# اشتباه:
sudo php artisan optimize

# درست:
php artisan optimize
```

**چرا؟** چون با sudo، فایل‌های cache با owner `root` ساخته می‌شوند!

---

### ❌ اشتباه 2: Permission 777

```bash
# خطرناک و غیرضروری:
sudo chmod -R 777 storage
```

**استفاده کنید:**
```bash
# ایمن و کافی:
sudo chmod -R 775 storage
```

---

### ❌ اشتباه 3: فراموش کردن bootstrap/cache

```bash
# ناقص:
sudo chown -R www-data:www-data storage

# کامل:
sudo chown -R www-data:www-data storage bootstrap/cache
```

---

## 🔄 اتوماتیک کردن (Deployment Script)

### ساخت اسکریپت:

```bash
nano /var/www/zdr/scripts/fix-permissions.sh
```

### محتوای اسکریپت:

```bash
#!/bin/bash

echo "🔧 Fixing Laravel permissions..."

# Change to project directory
cd /var/www/zdr

# Fix ownership
echo "📁 Setting ownership to www-data..."
sudo chown -R www-data:www-data storage bootstrap/cache

# Fix permissions
echo "🔐 Setting permissions..."
sudo chmod -R 775 storage bootstrap/cache

# Clear caches
echo "🗑️  Clearing caches..."
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Optimize
echo "⚡ Optimizing..."
php artisan optimize

echo "✅ Done!"
```

### اجرا:

```bash
chmod +x /var/www/zdr/scripts/fix-permissions.sh
/var/www/zdr/scripts/fix-permissions.sh
```

---

## 🐳 برای Docker:

اگر از Docker استفاده می‌کنید:

```dockerfile
# در Dockerfile:
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

---

## 🎯 چک‌لیست نهایی:

- [ ] `storage/` مالکیت: `www-data:www-data` ✓
- [ ] `bootstrap/cache/` مالکیت: `www-data:www-data` ✓
- [ ] `storage/` دسترسی: `775` ✓
- [ ] `bootstrap/cache/` دسترسی: `775` ✓
- [ ] کش‌ها پاک شده ✓
- [ ] Optimize اجرا شده ✓
- [ ] سایت بدون خطا کار می‌کند ✓

---

## 🆘 اگر باز مشکل دارید:

### 1. بررسی User وب سرور:

```bash
# برای Nginx:
ps aux | grep nginx

# برای Apache:
ps aux | grep apache
```

اگر user متفاوت است (مثلا `nginx` به جای `www-data`):

```bash
sudo chown -R nginx:nginx storage bootstrap/cache
```

---

### 2. بررسی SELinux (CentOS/RHEL):

```bash
# غیرفعال کردن موقت:
sudo setenforce 0

# یا تنظیم context:
sudo chcon -R -t httpd_sys_rw_content_t storage bootstrap/cache
```

---

### 3. بررسی Disk Space:

```bash
df -h
```

اگر دیسک پر است، فضا آزاد کنید!

---

## 📚 منابع:

1. [Laravel Deployment](https://laravel.com/docs/deployment)
2. [File Permissions](https://www.digitalocean.com/community/tutorials/linux-permissions-basics-and-how-to-use-umask-on-a-vps)

---

## ✅ خلاصه:

```bash
# یک خط کافی است:
sudo chown -R www-data:www-data storage bootstrap/cache && \
sudo chmod -R 775 storage bootstrap/cache && \
php artisan optimize
```

---

**توسعه‌دهنده:** AI Assistant  
**تاریخ:** 1404/08/05  
**وضعیت:** ✅ تست شده و کار می‌کند

