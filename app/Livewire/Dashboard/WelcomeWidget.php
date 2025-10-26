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

    public function mount(): void
    {
        $this->user = auth()->user();
        $this->setGreeting();
        $this->loadStats();
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

        // Calculate online users from sessions table (active in last 15 minutes)
        $onlineUsers = 0;
        try {
            $fifteenMinutesAgo = now()->subMinutes(15)->timestamp;
            $onlineUsers = DB::table('sessions')
                ->where('user_id', '!=', null)
                ->where('last_activity', '>=', $fifteenMinutesAgo)
                ->distinct('user_id')
                ->count('user_id');
        } catch (\Exception $e) {
            // If sessions table doesn't exist or error, default to 0
            $onlineUsers = 0;
        }

        $this->stats = [
            'today_users' => User::where('created_at', '>=', $todayStart)->count(),
            'total_users' => User::count(),
            'online_users' => $onlineUsers,
            'your_last_login' => __('امروز'), // Simplified since we don't have last_login_at column
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


    public function render()
    {
        return view('livewire.dashboard.welcome-widget');
    }
}

