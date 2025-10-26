<?php

return [
    'categories' => [
        'vegetables' => 'تره بار و محصولات تازه',
        'protein' => 'محصولات پروتئینی',
        'transport' => 'حمل و نقل',
        'repairs' => 'تعمیرات و نگهداری',
        'cleaning' => 'مواد و خدمات نظافتی',
        'utilities' => 'قبوض آب، برق، گاز و خدمات شهری',
        'fuel' => 'سوخت و انرژی',
        'supplies' => 'لوازم مصرفی و اداری',
        'marketing' => 'تبلیغات و بازاریابی',
        'insurance' => 'بیمه و خدمات مالی',
        'rent' => 'اجاره و اجرت',
        'human_resources' => 'منابع انسانی',
        'staff_meals' => 'غذای پرسنلی',
        'dairy' => 'محصولات لبنی',
        'grocery' => 'کالاهای سوپرمارکتی',
        'equipment' => 'تجهیزات و ابزار',
        'furniture' => 'مبلمان و دکوراسیون',
        'electronics' => 'کالاهای الکترونیکی',
        'security' => 'حفاظت و امنیت',
        'waste' => 'دفع ضایعات',
        'other' => 'سایر هزینه‌ها',
    ],

    'backups' => [
        /*
         * When true, the controller will attempt to adjust owner/group of
         * generated backup files and directories. Requires appropriate OS support.
         */
        'adjust_permissions' => (bool) env('PETTY_CASH_BACKUP_ADJUST_PERMISSIONS', false),

        /*
         * Target owner and group used when adjust_permissions is enabled.
         */
        'owner' => env('PETTY_CASH_BACKUP_OWNER', 'www-data'),
        'group' => env('PETTY_CASH_BACKUP_GROUP', 'www-data'),

        /*
         * Additional paths that should be included when exporting the petty cash module.
         * Provide relative paths from the project root. Directories are supported.
         */
        'additional_paths' => array_filter(array_map('trim', explode(',', (string) env('PETTY_CASH_BACKUP_EXTRA_PATHS', '')))),
    ],
];
