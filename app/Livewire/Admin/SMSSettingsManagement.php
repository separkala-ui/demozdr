<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\File;

class SMSSettingsManagement extends Component
{
    use WithPagination;

    // Current Settings
    public $enabled;
    public $logOnly;
    public $apiKey;
    public $originator;

    // Test SMS
    public $testMobile = '';
    public $testMessage = '';
    public $testResult = null;

    // SMS Logs
    public $logs = [];
    public $showLogs = false;

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $this->enabled = config('services.ippanel.enabled', false);
        $this->logOnly = config('services.ippanel.log_only', true);
        $this->apiKey = config('services.ippanel.api_key', '');
        $this->originator = config('services.ippanel.originator', '');
    }

    public function saveSettings()
    {
        try {
            // Update .env file
            $this->updateEnvFile([
                'IPPANEL_ENABLED' => $this->enabled ? 'true' : 'false',
                'IPPANEL_LOG_ONLY' => $this->logOnly ? 'true' : 'false',
                'IPPANEL_API_KEY' => $this->apiKey,
                'IPPANEL_ORIGINATOR' => $this->originator,
            ]);

            // Clear config cache
            \Artisan::call('config:clear');

            toast_success('تنظیمات با موفقیت ذخیره شد');
        } catch (\Exception $e) {
            toast_error('خطا در ذخیره تنظیمات: ' . $e->getMessage());
        }
    }

    public function sendTestSMS()
    {
        $this->validate([
            'testMobile' => 'required|string|max:15',
            'testMessage' => 'required|string|max:500',
        ], [
            'testMobile.required' => 'شماره موبایل الزامی است',
            'testMessage.required' => 'متن پیام الزامی است',
        ]);

        try {
            $result = send_sms($this->testMobile, $this->testMessage);

            $this->testResult = $result;

            if ($result['success']) {
                toast_success('پیامک تستی با موفقیت ارسال شد (یا لاگ شد)');
            } else {
                toast_error('خطا در ارسال: ' . ($result['error'] ?? 'نامشخص'));
            }
        } catch (\Exception $e) {
            toast_error('خطا: ' . $e->getMessage());
        }
    }

    public function getCredit()
    {
        try {
            $result = sms()->getCredit();

            if ($result['success']) {
                toast_success('اعتبار: ' . number_format($result['credit']) . ' ریال');
            } else {
                toast_error('خطا: ' . ($result['error'] ?? 'نامشخص'));
            }
        } catch (\Exception $e) {
            toast_error('خطا: ' . $e->getMessage());
        }
    }

    public function loadLogs()
    {
        $this->showLogs = true;
        $logFile = storage_path('logs/laravel.log');

        if (File::exists($logFile)) {
            $content = File::get($logFile);
            $lines = explode("\n", $content);

            // فیلتر فقط خطوط مربوط به SMS
            $smsLines = array_filter($lines, function ($line) {
                return str_contains($line, '[SMS') || str_contains($line, 'SMS');
            });

            $this->logs = array_slice(array_reverse(array_values($smsLines)), 0, 50);
        } else {
            $this->logs = ['فایل لاگ یافت نشد'];
        }
    }

    public function clearTestResult()
    {
        $this->testResult = null;
    }

    /**
     * Update .env file
     */
    private function updateEnvFile(array $data)
    {
        $envFile = base_path('.env');
        $envContent = File::get($envFile);

        foreach ($data as $key => $value) {
            // اگر کلید وجود دارد، update کن
            if (preg_match("/^{$key}=/m", $envContent)) {
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                // اگر وجود ندارد، اضافه کن
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envFile, $envContent);
    }

    public function render()
    {
        return view('livewire.admin.sms-settings-management');
    }
}
