# 📘 راهنمای سیستم بک‌آپ و بازگردانی دیتابیس

## 📋 فهرست مطالب
1. [معرفی](#معرفی)
2. [استفاده از CLI](#استفاده-از-cli)
3. [استفاده از پنل وب](#استفاده-از-پنل-وب)
4. [بک‌آپ خودکار](#بک-آپ-خودکار)
5. [توصیه‌های امنیتی](#توصیه-های-امنیتی)

---

## 🎯 معرفی

سیستم بک‌آپ دیتابیس Tankha/ZDR امکانات کاملی برای ایجاد، مدیریت و بازگردانی نسخه‌های پشتیبان دیتابیس را فراهم می‌کند.

### ویژگی‌ها:
- ✅ ایجاد بک‌آپ از طریق CLI و پنل وب
- ✅ بازگردانی بک‌آپ با هشدار امنیتی
- ✅ دانلود فایل‌های بک‌آپ
- ✅ آپلود فایل‌های بک‌آپ خارجی
- ✅ حذف خودکار بک‌آپ‌های قدیمی (30+ روز)
- ✅ نمایش حجم و تاریخ شمسی
- ✅ رابط کاربری مدرن و فارسی
- ✅ دسترسی محدود به Superadmin

---

## 💻 استفاده از CLI (Command Line)

### 1️⃣ ایجاد بک‌آپ جدید

#### بک‌آپ با نام خودکار (تاریخ‌دار):
```bash
php artisan db:backup
```

**خروجی نمونه:**
```
🔄 Starting database backup...
✅ Backup created successfully!
+----------+------------------------------------------------------------------+
| Property | Value                                                            |
+----------+------------------------------------------------------------------+
| File     | backup_2025-10-29_10-52-37.sql                                   |
| Size     | 78.17 KB                                                         |
| Path     | /var/www/zdr/storage/app/backups/database/backup_2025-10-29_...  |
| Database | laradashboard                                                    |
| Time     | 2025-10-29 10:52:37                                              |
+----------+------------------------------------------------------------------+
```

#### بک‌آپ با نام سفارشی:
```bash
php artisan db:backup --name=before_migration
```

**استفاده‌های پیشنهادی:**
- قبل از migration: `--name=before_migration_v2.0`
- قبل از آپدیت: `--name=before_update_$(date +%Y%m%d)`
- بک‌آپ دوره‌ای: `--name=daily_backup`

---

### 2️⃣ بازگردانی بک‌آپ

#### بازگردانی تعاملی (Interactive):
```bash
php artisan db:restore
```

**خروجی:**
```
📋 Available backups:
+---+----------------------------------+----------+---------------------+
| # | File                             | Size     | Date                |
+---+----------------------------------+----------+---------------------+
| 1 | backup_2025-10-29_10-52-37.sql   | 78.17 KB | 2025-10-29 10:52:37 |
| 2 | before_migration.sql             | 75.23 KB | 2025-10-28 14:30:15 |
+---+----------------------------------+----------+---------------------+

Enter backup number to restore (or filename): 1

⚠️  WARNING: This will replace all current database data!
Are you sure you want to restore this backup? (yes/no) [no]:
> yes

🔄 Restoring database...
✅ Database restored successfully!
🔄 Clearing application cache...
```

#### بازگردانی مستقیم (با نام فایل):
```bash
php artisan db:restore backup_2025-10-29_10-52-37.sql
```

⚠️ **هشدار:** بازگردانی بک‌آپ تمام داده‌های فعلی را پاک می‌کند!

---

## 🌐 استفاده از پنل وب

### دسترسی به پنل:
```
https://yourdomain.com/admin/database-backup
```

**شرط دسترسی:** فقط کاربران با نقش **Superadmin**

---

### امکانات پنل وب:

#### 1️⃣ ایجاد بک‌آپ جدید
1. کلیک روی دکمه **"ایجاد بک‌آپ جدید"** (سبز رنگ)
2. بک‌آپ به صورت خودکار ایجاد می‌شود
3. فایل جدید در لیست نمایش داده می‌شود

#### 2️⃣ دانلود بک‌آپ
1. در لیست بک‌آپ‌ها، روی دکمه **"دانلود"** (آبی) کلیک کنید
2. فایل `.sql` دانلود می‌شود
3. فایل را در مکان امن ذخیره کنید

#### 3️⃣ بازگردانی بک‌آپ
1. روی دکمه **"بازگردانی"** (سبز) کلیک کنید
2. پیام هشدار را مطالعه کنید:
   ```
   ⚠️ هشدار: این عملیات تمام داده‌های فعلی دیتابیس را جایگزین می‌کند!
   آیا مطمئن هستید؟
   ```
3. روی **OK** کلیک کنید
4. صفحه به صورت خودکار reload می‌شود

#### 4️⃣ آپلود فایل بک‌آپ
1. کلیک روی دکمه **"آپلود بک‌آپ"** (آبی)
2. فایل `.sql` خود را انتخاب کنید
3. روی **آپلود** کلیک کنید
4. فایل در لیست اضافه می‌شود

**محدودیت‌ها:**
- فرمت: فقط `.sql`
- حداکثر حجم: 500 MB

#### 5️⃣ حذف بک‌آپ
1. روی دکمه **"حذف"** (قرمز) کلیک کنید
2. تایید کنید
3. فایل به صورت دائمی حذف می‌شود

---

## ⏰ بک‌آپ خودکار

### 1️⃣ بک‌آپ روزانه با Cron

#### نصب Cron Job:
```bash
# ویرایش crontab
crontab -e
```

#### افزودن بک‌آپ روزانه (ساعت 2 بامداد):
```bash
0 2 * * * cd /var/www/zdr && php artisan db:backup --name=daily_$(date +\%Y\%m\%d) >> /var/www/zdr/storage/logs/backup.log 2>&1
```

#### افزودن بک‌آپ هفتگی (یکشنبه ساعت 3 بامداد):
```bash
0 3 * * 0 cd /var/www/zdr && php artisan db:backup --name=weekly_$(date +\%Y\%m\%d) >> /var/www/zdr/storage/logs/backup.log 2>&1
```

#### حذف بک‌آپ‌های قدیمی‌تر از 30 روز:
```bash
0 4 * * * find /var/www/zdr/storage/app/backups/database -name "*.sql" -mtime +30 -delete
```

#### نمونه crontab کامل:
```bash
# بک‌آپ روزانه
0 2 * * * cd /var/www/zdr && php artisan db:backup --name=daily_$(date +\%Y\%m\%d) >> /var/www/zdr/storage/logs/backup.log 2>&1

# بک‌آپ هفتگی
0 3 * * 0 cd /var/www/zdr && php artisan db:backup --name=weekly_$(date +\%Y\%m\%d) >> /var/www/zdr/storage/logs/backup.log 2>&1

# پاک کردن بک‌آپ‌های قدیمی
0 4 * * * find /var/www/zdr/storage/app/backups/database -name "*.sql" -mtime +30 -delete

# لاگ cron
0 5 * * * echo "Backup cron executed at $(date)" >> /var/www/zdr/storage/logs/backup.log
```

---

### 2️⃣ بک‌آپ خودکار با Laravel Scheduler

#### ویرایش `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    // بک‌آپ روزانه ساعت 2 بامداد
    $schedule->command('db:backup', ['--name' => 'daily_' . date('Ymd')])
             ->dailyAt('02:00')
             ->timezone('Asia/Tehran');

    // بک‌آپ هفتگی
    $schedule->command('db:backup', ['--name' => 'weekly_' . date('Ymd')])
             ->weekly()
             ->sundays()
             ->at('03:00')
             ->timezone('Asia/Tehran');
}
```

**نکته:** مطمئن شوید که Cron برای Laravel Scheduler تنظیم شده است:
```bash
* * * * * cd /var/www/zdr && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔐 توصیه‌های امنیتی

### 1️⃣ ذخیره‌سازی خارج از سرور

بک‌آپ‌ها را در مکان‌های امن خارج از سرور ذخیره کنید:

#### استفاده از rsync:
```bash
# همگام‌سازی با سرور بک‌آپ
rsync -avz /var/www/zdr/storage/app/backups/database/ \
    user@backup-server:/backups/tankha/database/
```

#### آپلود به Cloud Storage:
```bash
# AWS S3
aws s3 sync /var/www/zdr/storage/app/backups/database/ \
    s3://your-bucket/backups/database/

# Google Cloud Storage
gsutil rsync -r /var/www/zdr/storage/app/backups/database/ \
    gs://your-bucket/backups/database/
```

---

### 2️⃣ رمزگذاری فایل‌های بک‌آپ

#### رمزگذاری با GPG:
```bash
# رمزگذاری
gpg --symmetric --cipher-algo AES256 backup_2025-10-29.sql

# رمزگشایی
gpg --decrypt backup_2025-10-29.sql.gpg > backup_2025-10-29.sql
```

#### رمزگذاری خودکار در Cron:
```bash
0 2 * * * cd /var/www/zdr && \
    php artisan db:backup --name=daily_$(date +\%Y\%m\%d) && \
    gpg --batch --yes --passphrase "YourStrongPassword" --symmetric --cipher-algo AES256 \
    storage/app/backups/database/daily_$(date +\%Y\%m\%d).sql
```

---

### 3️⃣ محدود کردن دسترسی به فایل‌ها

```bash
# محدود کردن دسترسی به پوشه بک‌آپ
chmod 700 /var/www/zdr/storage/app/backups/database
chown www-data:www-data /var/www/zdr/storage/app/backups/database

# محدود کردن دسترسی به فایل‌های بک‌آپ
chmod 600 /var/www/zdr/storage/app/backups/database/*.sql
```

---

### 4️⃣ تست بازگردانی

حتماً بک‌آپ‌ها را به صورت دوره‌ای تست کنید:

```bash
# 1. ایجاد دیتابیس تست
mysql -u root -p -e "CREATE DATABASE tankha_test;"

# 2. بازگردانی بک‌آپ در دیتابیس تست
mysql -u root -p tankha_test < storage/app/backups/database/backup_2025-10-29.sql

# 3. بررسی جداول
mysql -u root -p tankha_test -e "SHOW TABLES;"

# 4. حذف دیتابیس تست
mysql -u root -p -e "DROP DATABASE tankha_test;"
```

---

### 5️⃣ نظارت و Alert

ایجاد script برای نظارت بر بک‌آپ:

```bash
#!/bin/bash
# /usr/local/bin/check-backup.sh

BACKUP_DIR="/var/www/zdr/storage/app/backups/database"
LAST_BACKUP=$(ls -t $BACKUP_DIR/*.sql | head -1)
LAST_BACKUP_TIME=$(stat -c %Y "$LAST_BACKUP")
CURRENT_TIME=$(date +%s)
DIFF=$((($CURRENT_TIME - $LAST_BACKUP_TIME) / 3600))

if [ $DIFF -gt 48 ]; then
    echo "⚠️ Warning: Last backup is $DIFF hours old!" | mail -s "Backup Alert" admin@yourdomain.com
fi
```

اضافه کردن به Cron:
```bash
0 6 * * * /usr/local/bin/check-backup.sh
```

---

## 📊 مدیریت فضای دیسک

### بررسی حجم بک‌آپ‌ها:
```bash
# حجم کل پوشه بک‌آپ
du -sh /var/www/zdr/storage/app/backups/database

# حجم هر فایل
du -h /var/www/zdr/storage/app/backups/database/*.sql | sort -h

# تعداد فایل‌ها
ls -1 /var/www/zdr/storage/app/backups/database/*.sql | wc -l
```

### فشرده‌سازی بک‌آپ‌ها:
```bash
# فشرده‌سازی یک فایل
gzip backup_2025-10-29.sql

# فشرده‌سازی تمام فایل‌ها
gzip /var/www/zdr/storage/app/backups/database/*.sql

# رمزگشایی
gunzip backup_2025-10-29.sql.gz
```

---

## 🆘 عیب‌یابی مشکلات

### خطا: Permission Denied
```bash
# تنظیم مجوزها
sudo chown -R www-data:www-data /var/www/zdr/storage/app/backups
sudo chmod -R 775 /var/www/zdr/storage/app/backups
```

### خطا: mysqldump not found
```bash
# نصب MySQL client
sudo apt install mysql-client
```

### خطا: Access denied for user
```bash
# بررسی اطلاعات دیتابیس در .env
cat /var/www/zdr/.env | grep DB_

# تست اتصال
mysql -u DB_USERNAME -p -e "SHOW DATABASES;"
```

### خطا: Table doesn't exist after restore
```bash
# بازگردانی با force
mysql -u DB_USERNAME -p --force DB_DATABASE < backup.sql
```

---

## 📝 چک‌لیست بک‌آپ

- [ ] بک‌آپ روزانه خودکار تنظیم شده
- [ ] بک‌آپ‌ها در مکان خارج از سرور ذخیره می‌شوند
- [ ] فایل‌های بک‌آپ رمزگذاری شده‌اند
- [ ] بازگردانی بک‌آپ حداقل ماهی یکبار تست می‌شود
- [ ] Alert برای شکست بک‌آپ فعال است
- [ ] بک‌آپ‌های قدیمی به صورت خودکار پاک می‌شوند
- [ ] مجوزهای فایل‌ها محدود است (700/600)
- [ ] فضای دیسک به صورت منظم بررسی می‌شود

---

## 📞 پشتیبانی

در صورت بروز مشکل:

1. **بررسی لاگ‌ها:**
   ```bash
   tail -100 /var/www/zdr/storage/logs/laravel.log
   ```

2. **GitHub Issues:**
   https://github.com/separkala-ui/tankha/issues

3. **تست دستی:**
   ```bash
   # تست mysqldump
   mysqldump --version
   
   # تست دسترسی دیتابیس
   php artisan tinker
   DB::connection()->getPdo();
   ```

---

**نسخه: 1.0**  
**تاریخ: اکتبر 2025**  
**مخصوص: Tankha/ZDR Project**

