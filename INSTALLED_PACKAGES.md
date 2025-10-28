# 📦 پکیج‌های نصب شده - راهنمای کامل

## ✅ پکیج‌های خارجی نصب شده

### 1️⃣ **PDF Export - barryvdh/laravel-dompdf** 
- **⭐ GitHub Stars:** 16,900+
- **📝 منبع:** [github.com/barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf)
- **🎯 کاربرد:** تبدیل گزارش‌های فرم به PDF

#### نحوه استفاده:
```php
use Barryvdh\DomPDF\Facade\Pdf;

// در Controller
public function exportPdf(FormReport $report)
{
    $pdf = Pdf::loadView('pdf.form-report', compact('report'));
    return $pdf->download('report-'.$report->id.'.pdf');
}
```

#### مثال View (resources/views/pdf/form-report.blade.php):
```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>گزارش فرم #{{ $report->id }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial; direction: rtl; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body>
    <h1>{{ $report->template->title }}</h1>
    <table>
        @foreach($report->answers as $answer)
        <tr>
            <th>{{ $answer->field->label }}</th>
            <td>{{ $answer->value }}</td>
        </tr>
        @endforeach
    </table>
</body>
</html>
```

---

### 2️⃣ **Excel Export - maatwebsite/excel**
- **⭐ GitHub Stars:** 12,100+
- **📝 منبع:** [github.com/SpartnerNL/Laravel-Excel](https://github.com/SpartnerNL/Laravel-Excel)
- **🎯 کاربرد:** Export داده‌های فرم به Excel

#### نحوه استفاده:
```bash
# ایجاد Export Class
php artisan make:export FormReportsExport --model=FormReport
```

```php
// app/Exports/FormReportsExport.php
namespace App\Exports;

use App\Models\DynamicForms\FormReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FormReportsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return FormReport::with('template', 'reporter')->get();
    }

    public function headings(): array
    {
        return ['شناسه', 'عنوان فرم', 'گزارش‌دهنده', 'تاریخ'];
    }
}
```

```php
// در Controller
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FormReportsExport;

public function export()
{
    return Excel::download(new FormReportsExport, 'reports.xlsx');
}
```

---

### 3️⃣ **Job Queue Dashboard - laravel/horizon**
- **⭐ GitHub Stars:** 3,900+
- **📝 منبع:** [github.com/laravel/horizon](https://github.com/laravel/horizon)
- **🎯 کاربرد:** مدیریت و نظارت بر Queue ها

#### دسترسی به داشبورد:
```
URL: https://yourdomain.com/horizon
```

#### نحوه استفاده:
```php
// ایجاد Job
php artisan make:job ProcessFormDataJob
```

```php
// app/Jobs/ProcessFormDataJob.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessFormDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public $formReport
    ) {}

    public function handle()
    {
        // پردازش سنگین داده‌ها
        // تحلیل آماری، ML، و غیره
    }
}
```

```php
// استفاده در Controller
ProcessFormDataJob::dispatch($report);
```

#### اجرای Worker:
```bash
php artisan horizon
```

---

### 4️⃣ **Backup System - spatie/laravel-backup**
- **⭐ GitHub Stars:** 5,600+
- **📝 منبع:** [github.com/spatie/laravel-backup](https://github.com/spatie/laravel-backup)
- **🎯 کاربرد:** پشتیبان‌گیری خودکار

#### نحوه استفاده:
```bash
# پشتیبان‌گیری دستی
php artisan backup:run

# لیست پشتیبان‌ها
php artisan backup:list

# پاک‌سازی پشتیبان‌های قدیمی
php artisan backup:clean
```

#### تنظیم Cron برای پشتیبان خودکار:
```bash
# در crontab
0 1 * * * cd /var/www/zdr && php artisan backup:run --only-db
0 2 * * 0 cd /var/www/zdr && php artisan backup:run
```

---

### 5️⃣ **Machine Learning - php-ai/php-ml**
- **⭐ GitHub Stars:** 8,200+
- **📝 منبع:** [github.com/php-ai/php-ml](https://github.com/php-ai/php-ml)
- **🎯 کاربرد:** تحلیل داده‌های فرم با ML

#### مثال - تحلیل رگرسیون:
```php
use Phpml\Regression\LeastSquares;

public function analyzeQCData($measurements)
{
    $samples = [];
    $targets = [];
    
    foreach ($measurements as $m) {
        $samples[] = [$m['dimension'], $m['weight']];
        $targets[] = $m['quality_score'];
    }
    
    $regression = new LeastSquares();
    $regression->train($samples, $targets);
    
    // پیش‌بینی کیفیت بر اساس ابعاد و وزن
    $prediction = $regression->predict([100, 50]);
    
    return $prediction;
}
```

---

## 🎨 قابلیت‌های بومی Filament (بدون نیاز به نصب)

### 1️⃣ **Wizard - فرم‌های چند مرحله‌ای**

```php
use Filament\Forms\Components\Wizard;

Wizard::make([
    Wizard\Step::make('مرحله ۱: اطلاعات پایه')
        ->schema([
            TextInput::make('product_code')->required(),
            TextInput::make('product_name')->required(),
        ]),
    
    Wizard\Step::make('مرحله ۲: اندازه‌گیری‌ها')
        ->schema([
            Repeater::make('measurements')
                ->schema([
                    TextInput::make('dimension')->numeric(),
                    TextInput::make('weight')->numeric(),
                ])
        ]),
    
    Wizard\Step::make('مرحله ۳: تأیید نهایی')
        ->schema([
            Select::make('status')
                ->options([
                    'approved' => 'تایید شد',
                    'rejected' => 'رد شد',
                ])
        ]),
])
```

---

### 2️⃣ **Repeater - فیلدهای تکرارشونده**

```php
use Filament\Forms\Components\Repeater;

Repeater::make('measurements')
    ->schema([
        TextInput::make('dimension')
            ->label('اندازه (mm)')
            ->numeric()
            ->required(),
        
        TextInput::make('tolerance')
            ->label('تلرانس')
            ->numeric(),
        
        Select::make('status')
            ->label('وضعیت')
            ->options([
                'pass' => 'قبول',
                'fail' => 'رد',
            ]),
    ])
    ->columns(3)
    ->defaultItems(1)
    ->addActionLabel('➕ افزودن اندازه‌گیری')
    ->collapsible()
```

---

### 3️⃣ **Conditional Fields - فیلدهای شرطی**

```php
Select::make('inspection_type')
    ->options([
        'visual' => 'بازرسی چشمی',
        'destructive' => 'آزمون تخریبی',
    ])
    ->reactive(),

Textarea::make('destruction_reason')
    ->label('علت تخریب')
    ->visible(fn ($get) => $get('inspection_type') === 'destructive')
    ->required(fn ($get) => $get('inspection_type') === 'destructive'),
```

---

### 4️⃣ **Live Calculations - محاسبات لحظه‌ای**

```php
TextInput::make('price')
    ->numeric()
    ->live(),

TextInput::make('quantity')
    ->numeric()
    ->live(),

TextInput::make('total')
    ->numeric()
    ->disabled()
    ->dehydrated(false)
    ->afterStateHydrated(function ($set, $get) {
        $set('total', $get('price') * $get('quantity'));
    })
    ->reactive()
```

---

## 📊 نمونه‌های کاربردی

### مثال ۱: فرم کنترل کیفیت با Wizard و Repeater

```php
// app/Filament/Resources/QCInspectionResource.php
public static function form(Form $form): Form
{
    return $form->schema([
        Wizard::make([
            Wizard\Step::make('اطلاعات محصول')
                ->schema([
                    TextInput::make('product_code')->required(),
                    DatePicker::make('production_date')->required(),
                ]),
            
            Wizard\Step::make('اندازه‌گیری‌ها')
                ->schema([
                    Repeater::make('measurements')
                        ->schema([
                            TextInput::make('dimension')->numeric()->live(),
                            TextInput::make('tolerance_min')->numeric(),
                            TextInput::make('tolerance_max')->numeric(),
                            Toggle::make('is_pass')
                                ->disabled()
                                ->dehydrated(false)
                                ->afterStateUpdated(function ($set, $get) {
                                    $dim = $get('dimension');
                                    $min = $get('tolerance_min');
                                    $max = $get('tolerance_max');
                                    $set('is_pass', $dim >= $min && $dim <= $max);
                                }),
                        ])
                        ->columns(4),
                ]),
            
            Wizard\Step::make('نتیجه نهایی')
                ->schema([
                    Select::make('final_status')
                        ->options([
                            'pass' => '✅ قبول',
                            'fail' => '❌ رد',
                            'conditional' => '⚠️ مشروط',
                        ]),
                ]),
        ]),
    ]);
}
```

---

### مثال ۲: Export با Queue

```php
// در Controller
use App\Jobs\ExportFormReportsJob;

public function requestExport(Request $request)
{
    ExportFormReportsJob::dispatch(
        auth()->id(),
        $request->template_id
    );
    
    return back()->with('success', 'گزارش در حال آماده‌سازی است. از طریق ایمیل ارسال خواهد شد.');
}
```

```php
// app/Jobs/ExportFormReportsJob.php
public function handle()
{
    $export = new FormReportsExport($this->templateId);
    $filePath = 'exports/report-' . now()->format('Y-m-d-H-i-s') . '.xlsx';
    
    Excel::store($export, $filePath, 'public');
    
    // ارسال ایمیل با لینک دانلود
    Mail::to(User::find($this->userId))
        ->send(new ExportReadyMail($filePath));
}
```

---

## 🚀 دستورات مفید

```bash
# Horizon
php artisan horizon                    # اجرای Horizon
php artisan horizon:terminate          # توقف Horizon

# Backup
php artisan backup:run                 # پشتیبان کامل
php artisan backup:run --only-db       # فقط دیتابیس
php artisan backup:clean               # پاک‌سازی قدیمی‌ها

# Queue
php artisan queue:work                 # اجرای worker ساده
php artisan queue:failed               # لیست Job های ناموفق
php artisan queue:retry all            # تلاش مجدد همه

# Cache
php artisan optimize:clear             # پاکسازی همه cache ها
php artisan config:cache               # Cache کردن config
```

---

## 📚 منابع آموزشی

- **Filament Docs:** https://filamentphp.com/docs
- **Laravel Horizon:** https://laravel.com/docs/horizon
- **Spatie Backup:** https://spatie.be/docs/laravel-backup
- **PHP-ML:** https://php-ml.readthedocs.io/

---

✅ **همه پکیج‌ها نصب و آماده استفاده هستند!** 🎉

