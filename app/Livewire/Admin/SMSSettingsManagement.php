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
    public $financeManagerMobile;

    // Patterns
    public $patterns = [];
    public $allPatterns = [];

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
        $this->allPatterns = config('services.ippanel.patterns', []);
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $this->enabled = config('services.ippanel.enabled', false);
        $this->logOnly = config('services.ippanel.log_only', true);
        $this->apiKey = config('services.ippanel.api_key', '');
        $this->originator = config('services.ippanel.originator', '');
        $this->financeManagerMobile = config('services.ippanel.finance_manager_mobile', '');

        foreach ($this->allPatterns as $key => $pattern) {
            $this->patterns[$key] = $pattern['code'];
        }
    }

    public function saveSettings()
    {
        try {
            $envData = [
                'IPPANEL_ENABLED' => $this->enabled ? 'true' : 'false',
                'IPPANEL_LOG_ONLY' => $this->logOnly ? 'true' : 'false',
                'IPPANEL_API_KEY' => "'{$this->apiKey}'",
                'IPPANEL_ORIGINATOR' => $this->originator,
                'FINANCE_MANAGER_MOBILE' => $this->financeManagerMobile,
            ];

            foreach ($this->patterns as $key => $code) {
                $envKey = 'IPPANEL_PATTERN_' . strtoupper($key);
                $envData[$envKey] = $code;
            }

            // Update .env file
            $this->updateEnvFile($envData);

            // Clear config cache
            \Artisan::call('config:clear');

            session()->flash('success', 'تنظیمات با موفقیت ذخیره شد.');
            $this->dispatch('notify', type: 'success', message: 'تنظیمات با موفقیت ذخیره شد.');
            
            // Reload settings to reflect changes immediately
            $this->loadSettings();

        } catch (\Exception $e) {
            session()->flash('error', 'خطا در ذخیره تنظیمات: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'خطا در ذخیره تنظیمات: ' . $e->getMessage());
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
                session()->flash('success', 'پیامک تستی با موفقیت ارسال شد (یا لاگ شد).');
                $this->dispatch('notify', type: 'success', message: 'پیامک تستی با موفقیت ارسال شد (یا لاگ شد).');
            } else {
                session()->flash('error', 'خطا در ارسال: ' . ($result['error'] ?? 'نامشخص'));
                $this->dispatch('notify', type: 'error', message: 'خطا در ارسال: ' . ($result['error'] ?? 'نامشخص'));
            }
        } catch (\Exception $e) {
            session()->flash('error', 'خطا: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'خطا: ' . $e->getMessage());
        }
    }

    public function getCredit()
    {
        try {
            $result = sms()->getCredit();

            if ($result['success']) {
                $creditMessage = 'اعتبار: ' . number_format((float)($result['credit'] ?? 0)) . ' ریال';
                session()->flash('success', $creditMessage);
                $this->dispatch('notify', type: 'success', message: $creditMessage);
            } else {
                session()->flash('error', 'خطا در دریافت اعتبار: ' . ($result['error'] ?? 'نامشخص'));
                $this->dispatch('notify', type: 'error', message: 'خطا در دریافت اعتبار: ' . ($result['error'] ?? 'نامشخص'));
            }
        } catch (\Exception $e) {
            session()->flash('error', 'خطا: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'خطا: ' . $e->getMessage());
        }
    }

    public function loadLogs()
    {
        $this->showLogs = true;
        $logFile = storage_path('logs/laravel.log');

        if (File::exists($logFile)) {
            $content = File::get($logFile);
            $lines = explode("\n", $content);

            $smsLines = array_filter($lines, function ($line) {
                return str_contains($line, '[SMS') || str_contains($line, 'SMS');
            });

            $this->logs = array_slice(array_reverse(array_values($smsLines)), 0, 50);
        } else {
            $this->logs = ['فایل لاگ یافت نشد.'];
        }
    }

    public function clearTestResult()
    {
        $this->testResult = null;
    }

    private function updateEnvFile(array $data)
    {
        $envFile = base_path('.env');
        $envContent = File::get($envFile);

        foreach ($data as $key => $value) {
            $value = is_string($value) && str_contains($value, ' ') ? '"' . $value . '"' : $value;
            $key = strtoupper($key);

            if (preg_match("/^{$key}=/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
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
