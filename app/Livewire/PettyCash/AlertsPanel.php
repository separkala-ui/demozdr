<?php

declare(strict_types=1);

namespace App\Livewire\PettyCash;

use App\Models\PettyCashLedger;
use App\Models\PettyCashTransaction;
use Carbon\Carbon;
use Livewire\Component;

class AlertsPanel extends Component
{
    public PettyCashLedger $ledger;

    public array $alerts = [];

    protected $listeners = [
        'petty-cash-transaction-saved' => 'refreshAlerts',
        'petty-cash-transaction-deleted' => 'refreshAlerts',
        'petty-cash-transactions-refresh' => 'refreshAlerts',
    ];

    public function mount(PettyCashLedger $ledger): void
    {
        $this->ledger = $ledger;
        $this->loadAlerts();
    }

    public function refreshAlerts(): void
    {
        $this->loadAlerts();
    }

    protected function loadAlerts(): void
    {
        $this->alerts = [];

        // Alert 1: Low Balance Warning
        $this->checkLowBalance();

        // Alert 2: Many Pending Transactions
        $this->checkPendingTransactions();

        // Alert 3: Settlement Due
        $this->checkSettlementDue();

        // Alert 4: Charge Requests Waiting
        $this->checkChargeRequests();

        // Alert 5: High Expense Rate
        $this->checkHighExpenseRate();
    }

    protected function checkLowBalance(): void
    {
        $currentBalance = (float) $this->ledger->current_balance;
        $limitAmount = (float) $this->ledger->limit_amount;
        $balancePercentage = ($currentBalance / max($limitAmount, 1)) * 100;

        if ($balancePercentage < 20) {
            $this->alerts[] = [
                'type' => 'danger',
                'icon' => 'lucide:alert-triangle',
                'title' => __('هشدار: موجودی بسیار کم'),
                'message' => __('موجودی تنخواه به زیر :percent% سقف مجاز رسیده است. لطفاً در اسرع وقت شارژ کنید.', ['percent' => number_format($balancePercentage, 1)]),
                'action' => [
                    'label' => __('درخواست شارژ'),
                    'route' => route('admin.petty-cash.charge-request', $this->ledger),
                ],
                'priority' => 1,
            ];
        } elseif ($balancePercentage < 30) {
            $this->alerts[] = [
                'type' => 'warning',
                'icon' => 'lucide:alert-circle',
                'title' => __('توجه: موجودی کم'),
                'message' => __('موجودی تنخواه به :percent% سقف مجاز رسیده است.', ['percent' => number_format($balancePercentage, 1)]),
                'action' => [
                    'label' => __('درخواست شارژ'),
                    'route' => route('admin.petty-cash.charge-request', $this->ledger),
                ],
                'priority' => 2,
            ];
        }
    }

    protected function checkPendingTransactions(): void
    {
        $pendingCount = PettyCashTransaction::where('ledger_id', $this->ledger->id)
            ->where('status', PettyCashTransaction::STATUS_SUBMITTED)
            ->count();

        if ($pendingCount > 10) {
            $this->alerts[] = [
                'type' => 'warning',
                'icon' => 'lucide:clock',
                'title' => __('تراکنش‌های معلق زیاد'),
                'message' => __(':count تراکنش در انتظار بررسی و تایید شما هستند.', ['count' => $pendingCount]),
                'action' => [
                    'label' => __('بررسی تراکنش‌ها'),
                    'route' => route('admin.petty-cash.transactions', ['ledger' => $this->ledger, 'status' => 'submitted']),
                ],
                'priority' => 3,
            ];
        } elseif ($pendingCount > 5) {
            $this->alerts[] = [
                'type' => 'info',
                'icon' => 'lucide:info',
                'title' => __('تراکنش‌های در انتظار'),
                'message' => __(':count تراکنش منتظر بررسی شما هستند.', ['count' => $pendingCount]),
                'action' => [
                    'label' => __('مشاهده'),
                    'route' => route('admin.petty-cash.transactions', ['ledger' => $this->ledger, 'status' => 'submitted']),
                ],
                'priority' => 4,
            ];
        }
    }

    protected function checkSettlementDue(): void
    {
        $lastSettlement = $this->ledger->cycles()
            ->where('status', 'closed')
            ->latest('closed_at')
            ->first();

        if ($lastSettlement) {
            $daysSinceSettlement = Carbon::parse($lastSettlement->closed_at)->diffInDays(now());
        } else {
            // If no settlement exists, check from ledger creation
            $daysSinceSettlement = Carbon::parse($this->ledger->created_at)->diffInDays(now());
        }

        if ($daysSinceSettlement > 90) {
            $this->alerts[] = [
                'type' => 'danger',
                'icon' => 'lucide:file-x',
                'title' => __('تسویه معوقه'),
                'message' => __(':days روز از آخرین تسویه گذشته است. باید فوراً تسویه انجام شود.', ['days' => $daysSinceSettlement]),
                'action' => [
                    'label' => __('شروع تسویه'),
                    'route' => route('admin.petty-cash.settlement', $this->ledger),
                ],
                'priority' => 1,
            ];
        } elseif ($daysSinceSettlement > 30) {
            $this->alerts[] = [
                'type' => 'info',
                'icon' => 'lucide:calendar-clock',
                'title' => __('یادآوری: زمان تسویه'),
                'message' => __(':days روز از آخرین تسویه گذشته است. توصیه می‌شود تسویه ماهانه انجام شود.', ['days' => $daysSinceSettlement]),
                'action' => [
                    'label' => __('تسویه'),
                    'route' => route('admin.petty-cash.settlement', $this->ledger),
                ],
                'priority' => 5,
            ];
        }
    }

    protected function checkChargeRequests(): void
    {
        $chargeRequests = PettyCashTransaction::where('ledger_id', $this->ledger->id)
            ->where('type', PettyCashTransaction::TYPE_CHARGE)
            ->where('status', PettyCashTransaction::STATUS_SUBMITTED)
            ->count();

        if ($chargeRequests > 0) {
            $this->alerts[] = [
                'type' => 'info',
                'icon' => 'lucide:trending-up',
                'title' => __('درخواست‌های شارژ'),
                'message' => __(':count درخواست شارژ منتظر تایید شما است.', ['count' => $chargeRequests]),
                'action' => [
                    'label' => __('بررسی درخواست‌ها'),
                    'route' => route('admin.petty-cash.transactions', ['ledger' => $this->ledger, 'type' => 'charge', 'status' => 'submitted']),
                ],
                'priority' => 3,
            ];
        }
    }

    protected function checkHighExpenseRate(): void
    {
        // Check last 7 days expenses
        $recentExpenses = (float) PettyCashTransaction::where('ledger_id', $this->ledger->id)
            ->where('type', PettyCashTransaction::TYPE_EXPENSE)
            ->where('status', PettyCashTransaction::STATUS_APPROVED)
            ->where('created_at', '>=', now()->subDays(7))
            ->sum('amount');

        $weeklyBudget = ((float) $this->ledger->limit_amount / 30) * 7; // 7 days worth of monthly limit

        if ($recentExpenses > $weeklyBudget * 1.5) {
            $this->alerts[] = [
                'type' => 'warning',
                'icon' => 'lucide:trending-up',
                'title' => __('هزینه‌های بالا'),
                'message' => __('هزینه‌های ۷ روز گذشته (:amount ریال) بیشتر از حد معمول است.', [
                    'amount' => number_format((int) $recentExpenses),
                ]),
                'action' => null,
                'priority' => 4,
            ];
        }
    }

    public function render()
    {
        // Sort alerts by priority
        usort($this->alerts, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        return view('livewire.petty-cash.alerts-panel', [
            'alerts' => $this->alerts,
        ]);
    }
}

