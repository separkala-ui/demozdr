<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AlertSetting;

class AlertSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // تنظیمات تنخواه
            [
                'key' => 'low_balance_threshold_percentage',
                'category' => 'petty_cash',
                'type' => 'percentage',
                'value' => '20',
                'title_fa' => 'درصد هشدار موجودی کم',
                'description_fa' => 'وقتی موجودی تنخواه کمتر از این درصد سقف شود، هشدار نمایش داده می‌شود',
                'title_en' => 'Low Balance Threshold Percentage',
                'description_en' => 'Alert will be shown when petty cash balance is below this percentage of limit',
                'is_active' => true,
                'is_editable' => true,
                'priority' => 10,
            ],
            [
                'key' => 'very_low_balance_threshold_percentage',
                'category' => 'petty_cash',
                'type' => 'percentage',
                'value' => '10',
                'title_fa' => 'درصد هشدار موجودی بسیار کم',
                'description_fa' => 'وقتی موجودی تنخواه کمتر از این درصد سقف شود، هشدار قرمز نمایش داده می‌شود',
                'title_en' => 'Very Low Balance Threshold Percentage',
                'description_en' => 'Critical alert will be shown when balance is below this percentage',
                'is_active' => true,
                'is_editable' => true,
                'priority' => 11,
            ],
            [
                'key' => 'pending_transactions_alert_count',
                'category' => 'petty_cash',
                'type' => 'count',
                'value' => '5',
                'title_fa' => 'تعداد تراکنش‌های در انتظار برای هشدار',
                'description_fa' => 'وقتی تعداد تراکنش‌های در انتظار تایید بیشتر از این مقدار شود، هشدار نمایش داده می‌شود',
                'title_en' => 'Pending Transactions Alert Count',
                'description_en' => 'Alert will be shown when pending transactions exceed this count',
                'is_active' => true,
                'is_editable' => true,
                'priority' => 9,
            ],
            [
                'key' => 'overdue_settlement_days',
                'category' => 'petty_cash',
                'type' => 'count',
                'value' => '30',
                'title_fa' => 'روزهای تسویه معوق',
                'description_fa' => 'اگر تسویه‌ای بیش از این تعداد روز معوق باشد، هشدار نمایش داده می‌شود',
                'title_en' => 'Overdue Settlement Days',
                'description_en' => 'Alert for settlements overdue by this many days',
                'is_active' => true,
                'is_editable' => true,
                'priority' => 8,
            ],
            [
                'key' => 'high_expense_rate_days',
                'category' => 'petty_cash',
                'type' => 'count',
                'value' => '7',
                'title_fa' => 'بازه زمانی بررسی نرخ هزینه',
                'description_fa' => 'نرخ هزینه در این تعداد روز اخیر بررسی می‌شود',
                'title_en' => 'High Expense Rate Days',
                'description_en' => 'Period for checking expense rate',
                'is_active' => true,
                'is_editable' => true,
                'priority' => 7,
            ],
            [
                'key' => 'high_expense_rate_percentage',
                'category' => 'petty_cash',
                'type' => 'percentage',
                'value' => '50',
                'title_fa' => 'درصد نرخ هزینه بالا',
                'description_fa' => 'اگر در بازه زمانی مشخص، بیش از این درصد موجودی خرج شود، هشدار نمایش داده می‌شود',
                'title_en' => 'High Expense Rate Percentage',
                'description_en' => 'Alert when expenses exceed this percentage of balance in the period',
                'is_active' => true,
                'is_editable' => true,
                'priority' => 6,
            ],

            // تنظیمات تراکنش
            [
                'key' => 'large_transaction_threshold',
                'category' => 'transaction',
                'type' => 'amount',
                'value' => '10000000',
                'title_fa' => 'آستانه تراکنش بزرگ (ریال)',
                'description_fa' => 'تراکنش‌هایی که مبلغ آن‌ها بیش از این مقدار باشد به عنوان تراکنش بزرگ شناخته می‌شوند',
                'title_en' => 'Large Transaction Threshold (Rials)',
                'description_en' => 'Transactions above this amount are considered large',
                'is_active' => true,
                'is_editable' => true,
                'priority' => 5,
            ],
            [
                'key' => 'duplicate_transaction_check_enabled',
                'category' => 'transaction',
                'type' => 'boolean',
                'value' => 'true',
                'title_fa' => 'بررسی تراکنش‌های تکراری',
                'description_fa' => 'هشدار برای تراکنش‌های مشابه در یک بازه زمانی کوتاه',
                'title_en' => 'Duplicate Transaction Check',
                'description_en' => 'Alert for similar transactions in a short period',
                'is_active' => true,
                'is_editable' => true,
                'priority' => 4,
            ],

            // تنظیمات عمومی
            [
                'key' => 'alert_auto_dismiss_seconds',
                'category' => 'general',
                'type' => 'count',
                'value' => '300',
                'title_fa' => 'زمان بستن خودکار هشدارها (ثانیه)',
                'description_fa' => 'هشدارهای غیرمهم بعد از این مدت خودکار بسته می‌شوند (0 = بدون بستن خودکار)',
                'title_en' => 'Alert Auto Dismiss Seconds',
                'description_en' => 'Non-critical alerts auto-dismiss after this duration (0 = no auto-dismiss)',
                'is_active' => true,
                'is_editable' => true,
                'priority' => 3,
            ],
            [
                'key' => 'enable_email_alerts',
                'category' => 'general',
                'type' => 'boolean',
                'value' => 'false',
                'title_fa' => 'ارسال هشدارها به ایمیل',
                'description_fa' => 'هشدارهای مهم به ایمیل کاربران ارسال شود',
                'title_en' => 'Enable Email Alerts',
                'description_en' => 'Send important alerts to users via email',
                'is_active' => true,
                'is_editable' => true,
                'priority' => 2,
            ],
            [
                'key' => 'enable_sms_alerts',
                'category' => 'general',
                'type' => 'boolean',
                'value' => 'false',
                'title_fa' => 'ارسال هشدارها به پیامک',
                'description_fa' => 'هشدارهای فوری به پیامک کاربران ارسال شود',
                'title_en' => 'Enable SMS Alerts',
                'description_en' => 'Send urgent alerts to users via SMS',
                'is_active' => true,
                'is_editable' => true,
                'priority' => 1,
            ],
        ];

        foreach ($settings as $setting) {
            AlertSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('✅ Alert settings seeded successfully!');
    }
}
