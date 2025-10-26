<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class WelcomeWidget extends Component
{
    public User $user;

    public array $stats = [];

    public string $greeting = '';

    public string $motivationalQuote = '';

    public function mount(): void
    {
        $this->user = auth()->user();
        $this->setGreeting();
        $this->loadStats();
        $this->setMotivationalQuote();
    }

    protected function setGreeting(): void
    {
        $hour = (int) now()->format('H');

        if ($hour >= 5 && $hour < 12) {
            $this->greeting = __('صبح بخیر');
        } elseif ($hour >= 12 && $hour < 17) {
            $this->greeting = __('ظهر بخیر');
        } elseif ($hour >= 17 && $hour < 20) {
            $this->greeting = __('عصر بخیر');
        } else {
            $this->greeting = __('شب بخیر');
        }
    }

    protected function loadStats(): void
    {
        // Today's stats
        $todayStart = now()->startOfDay();

        $this->stats = [
            'today_users' => User::where('created_at', '>=', $todayStart)->count(),
            'total_users' => User::count(),
            'online_users' => User::where('last_login_at', '>=', now()->subMinutes(15))->count(),
            'your_last_login' => $this->user->last_login_at 
                ? verta($this->user->last_login_at)->formatDifference() 
                : __('اولین ورود'),
        ];

        // Check if user has petty cash access
        if ($this->user->can('petty-cash-view') || $this->user->hasRole(['Admin', 'Superadmin'])) {
            try {
                $pendingTransactions = DB::table('petty_cash_transactions')
                    ->where('status', 'submitted')
                    ->count();
                
                $this->stats['pending_transactions'] = $pendingTransactions;
            } catch (\Exception $e) {
                $this->stats['pending_transactions'] = 0;
            }
        }
    }

    protected function setMotivationalQuote(): void
    {
        $quotes = [
            __('امروز روز خوبی برای موفقیت است! 🚀'),
            __('تلاش شما تفاوت ایجاد می‌کند! 💪'),
            __('با انگیزه و امید به آینده نگاه کنید! ✨'),
            __('هر روز فرصتی جدید برای پیشرفت است! 🌟'),
            __('موفقیت حاصل تلاش‌های مستمر است! 🎯'),
            __('باور داشته باشید، می‌توانید! 💫'),
            __('امروز بهترین روز برای شروع است! 🌅'),
            __('کوچکترین قدم‌ها به بزرگترین موفقیت‌ها منجر می‌شوند! 👣'),
        ];

        // Select quote based on day of week (consistent for each day)
        $dayOfWeek = (int) now()->dayOfWeek;
        $this->motivationalQuote = $quotes[$dayOfWeek % count($quotes)];
    }

    public function render()
    {
        return view('livewire.dashboard.welcome-widget');
    }
}

