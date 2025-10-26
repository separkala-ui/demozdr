<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PettyCashLedger;
use App\Models\PettyCashCycle;
use App\Models\PettyCashTransaction;
use App\Models\User;
use App\Services\PettyCash\PettyCashService;
use App\Services\PettyCash\PettyCashArchiveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Hekmatinasser\Verta\Verta;
use ZipArchive;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PettyCashController extends Controller
{
    public function __construct(private readonly PettyCashService $pettyCashService)
    {
        $this->middleware(function ($request, $next) {
            // Only Superadmin and Admin can create ledgers
            if ($request->route()->getName() === 'admin.petty-cash.store') {
                $user = Auth::user();
                if (!$user || (!$user->hasRole('Superadmin') && !$user->hasRole('Admin'))) {
                    abort(403, __('شما اجازه ایجاد دفتر تنخواه ندارید.'));
                }
            }
            return $next($request);
        });
    }

    public function index(Request $request, ?PettyCashLedger $ledger = null)
    {
        $this->authorize('petty_cash.ledger.view');

        $this->setBreadcrumbTitle(__('تنخواه گردان'))
            ->addBreadcrumbItem(__('مدیریت مالی'), null)
            ->addBreadcrumbItem(__('تنخواه شعب'), null);

        $user = Auth::user();

        $showAllCards = $request->boolean('show_all');

        // If user is Superadmin or Admin, show all ledgers
        if ($user && $user->hasRole(['Superadmin', 'Admin'])) {
            $ledgers = PettyCashLedger::with(['assignedUser:id,first_name,last_name,email'])
                ->orderBy('branch_name')
                ->get();
        } else {
            // For other users, show only their assigned branch
            $ledgers = collect();
            if ($user && $user->branch_id) {
                $userLedger = PettyCashLedger::with(['assignedUser:id,first_name,last_name,email'])->find($user->branch_id);
                if ($userLedger) {
                    $ledgers->push($userLedger);
                }
            }
        }

        if (! $user || ! $user->hasRole(['Superadmin', 'Admin'])) {
            $showAllCards = false;
        }

        $selectedLedger = $ledger ?? $ledgers->first();

        if ($selectedLedger instanceof PettyCashLedger) {
            $selectedLedger->loadMissing('assignedUser:id,first_name,last_name,email');
        }

        if ($selectedLedger && ! $ledgers->contains(fn ($item) => $item->id === $selectedLedger->id)) {
            abort(403, __('شما به این شعبه دسترسی ندارید.'));
        }

        $selectedLedgerId = $selectedLedger?->id;

        $ledgers = $ledgers->map(function (PettyCashLedger $ledgerItem) use ($selectedLedgerId, &$selectedLedger) {
            $hydrated = $this->hydrateLedgerWithMetrics($ledgerItem);

            if ($selectedLedgerId && $ledgerItem->id === $selectedLedgerId) {
                $selectedLedger = $hydrated;
            }

            return $hydrated;
        });

        if ($selectedLedger && ! isset($selectedLedger->pending_balance)) {
            $selectedLedger = $this->hydrateLedgerWithMetrics($selectedLedger);
        }

        $visibleLedgers = $showAllCards ? $ledgers : ($selectedLedger ? collect([$selectedLedger]) : collect());

        $analyticsFilters = [
            'period' => $request->get('analytics_period', 'last_30'),
            'from' => $request->get('analytics_from'),
            'to' => $request->get('analytics_to'),
        ];

        if (($analyticsFilters['period'] ?? '') !== 'custom') {
            $analyticsFilters['from'] = null;
            $analyticsFilters['to'] = null;
        }

        $analytics = $selectedLedger
            ? $this->pettyCashService->getLedgerAnalytics($selectedLedger, $analyticsFilters)
            : null;

        $analyticsPeriodOptions = [
            'today' => __('امروز'),
            'last_7' => __('۷ روز گذشته'),
            'last_30' => __('۳۰ روز گذشته'),
            'current_month' => __('ماه جاری'),
            'last_90' => __('۹۰ روز گذشته'),
            'last_180' => __('۶ ماه گذشته'),
            'current_year' => __('سال جاری'),
            'custom' => __('بازه سفارشی'),
        ];
        $chargeTimeline = collect();
        if ($selectedLedger && $user && $user->hasRole(['Superadmin', 'Admin'])) {
            $chargeTimeline = collect($this->pettyCashService->getChargeUsageTimeline($selectedLedger, 10));
        }

        $adminArchives = collect();
        $branchArchives = collect();

        if ($selectedLedger) {
            if ($user && $user->hasRole(['Superadmin', 'Admin'])) {
                $adminArchives = $selectedLedger->archivedCycles()
                    ->with(['closer:id,first_name,last_name'])
                    ->withCount('archivedTransactions')
                    ->orderByDesc('closed_at')
                    ->paginate(5, ['*'], 'archives_page')
                    ->appends($request->query());
            } elseif ($user && (int) $user->branch_id === (int) $selectedLedger->id) {
                $branchArchives = $selectedLedger->archivedCycles()
                    ->with(['closer:id,first_name,last_name'])
                    ->withCount('archivedTransactions')
                    ->orderByDesc('closed_at')
                    ->simplePaginate(5, ['*'], 'branch_archives_page')
                    ->appends($request->query());
            }
        }

        return $this->renderViewWithBreadcrumbs('backend.pages.petty-cash.index', [
            'ledgers' => $ledgers,
            'visibleLedgers' => $visibleLedgers,
            'selectedLedger' => $selectedLedger,
            'showAllCards' => $showAllCards,
            'selectedLedgerMetrics' => (! $showAllCards && $selectedLedger)
                ? $this->pettyCashService->getLedgerSnapshot($selectedLedger)
                : null,
            'showAllTransactions' => $showAllCards
                ? PettyCashTransaction::with('ledger:id,branch_name')
                    ->whereIn('ledger_id', $visibleLedgers->pluck('id'))
                    ->whereNull('archive_cycle_id')
                    ->orderByDesc('transaction_date')
                    ->orderByDesc('id')
                    ->take(50)
                    ->get()
                : collect(),
            'chargeTimeline' => $chargeTimeline,
            'adminArchives' => $adminArchives,
            'branchArchives' => $branchArchives,
            'analytics' => $analytics,
            'analyticsFilters' => $analyticsFilters,
            'analyticsPeriodOptions' => $analyticsPeriodOptions,
            'isAdminUser' => $user?->hasRole(['Superadmin', 'Admin']) ?? false,
        ]);
    }

    public function create()
    {
        $this->authorize('petty_cash.ledger.create');

        $this->setBreadcrumbTitle(__('ایجاد شعبه جدید'))
            ->addBreadcrumbItem(__('مدیریت مالی'), null)
            ->addBreadcrumbItem(__('تنخواه شعب'), route('admin.petty-cash.index'))
            ->addBreadcrumbItem(__('ایجاد'), null);

        return $this->renderViewWithBreadcrumbs('backend.pages.petty-cash.create', [
            'assignableUsers' => $this->getAssignableUsers(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('petty_cash.ledger.create');

        $validated = $request->validate([
            'branch_name' => ['required', 'string', 'max:255'],
            'limit_amount' => ['required', 'numeric', 'min:0'],
            'max_charge_request_amount' => ['nullable', 'numeric', 'min:0'],
            'max_transaction_amount' => ['nullable', 'numeric', 'min:0'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'assigned_user_id' => ['required', 'exists:users,id'],
            'account_number' => ['required', 'string', 'max:50'],
            'iban' => ['required', 'string', 'max:34'],
            'card_number' => ['required', 'string', 'max:20'],
            'account_holder' => ['required', 'string', 'max:255'],
        ]);

        $ledger = new PettyCashLedger([
            'branch_name' => $validated['branch_name'],
            'limit_amount' => $validated['limit_amount'],
            'max_charge_request_amount' => (float) ($validated['max_charge_request_amount'] ?? 0),
            'max_transaction_amount' => (float) ($validated['max_transaction_amount'] ?? 0),
            'opening_balance' => $validated['opening_balance'] ?? $validated['limit_amount'],
            'assigned_user_id' => $validated['assigned_user_id'] ?? null,
            'account_number' => $validated['account_number'],
            'iban' => $validated['iban'],
            'card_number' => $validated['card_number'],
            'account_holder' => $validated['account_holder'],
        ]);
        $ledger->current_balance = $ledger->opening_balance;
        $ledger->save();

        $this->syncLedgerAssignedUser($ledger, $validated['assigned_user_id'] ?? null, null);

        $ledger->cycles()->create([
            'status' => 'open',
            'opened_at' => now(),
            'opening_balance' => $ledger->opening_balance,
        ]);

        return redirect()
            ->route('admin.petty-cash.index', ['ledger' => $ledger->id])
            ->with('success', __('دفتر تنخواه شعبه با موفقیت ایجاد شد.'));
    }

    public function print(Request $request, PettyCashLedger $ledger)
    {
        $this->authorize('petty_cash.report.print');

        $this->setBreadcrumbTitle(__('گزارش تنخواه'))
            ->addBreadcrumbItem(__('مدیریت مالی'), null)
            ->addBreadcrumbItem(__('چاپ گزارش'), null);

        $ledger->loadMissing('assignedUser:id,first_name,last_name,email');

        $cycleId = $request->get('cycle');
        $isArchiveView = false;
        $cycle = null;

        if ($cycleId) {
            $cycle = $ledger->archivedCycles()->with('closer:id,first_name,last_name')->findOrFail($cycleId);
            $transactions = $ledger->transactions()
                ->where('archive_cycle_id', $cycle->id)
                ->orderBy('transaction_date')
                ->get();

            $approvedInPeriod = $transactions;
            $pendingInPeriod = collect();

            $totals = [
                'incoming' => (float) $approvedInPeriod
                    ->where('type', PettyCashTransaction::TYPE_CHARGE)
                    ->sum('amount'),
                'outgoing' => (float) $approvedInPeriod
                    ->where('type', PettyCashTransaction::TYPE_EXPENSE)
                    ->sum('amount'),
                'adjustment_positive' => (float) $approvedInPeriod
                    ->where('type', PettyCashTransaction::TYPE_ADJUSTMENT)
                    ->filter(fn ($transaction) => $transaction->amount >= 0)
                    ->sum('amount'),
                'adjustment_negative' => (float) $approvedInPeriod
                    ->where('type', PettyCashTransaction::TYPE_ADJUSTMENT)
                    ->filter(fn ($transaction) => $transaction->amount < 0)
                    ->sum('amount'),
                'pending_incoming' => 0.0,
                'pending_outgoing' => 0.0,
            ];

            $overallTotals = [
                'opening_balance' => (float) $cycle->opening_balance,
                'limit_amount' => (float) $ledger->limit_amount,
                'current_balance' => (float) $cycle->closing_balance,
                'approved_charges_total' => (float) ($cycle->total_charges ?? $totals['incoming']),
                'approved_expenses_total' => (float) ($cycle->total_expenses ?? $totals['outgoing']),
                'approved_adjustments_total' => (float) ($cycle->total_adjustments ?? ($totals['adjustment_positive'] + $totals['adjustment_negative'])),
                'pending_charges_total' => 0.0,
                'pending_expenses_total' => 0.0,
            ];

            $overallTotals['approved_adjustments_positive'] = $overallTotals['approved_adjustments_total'] > 0 ? $overallTotals['approved_adjustments_total'] : 0.0;
            $overallTotals['approved_adjustments_negative'] = $overallTotals['approved_adjustments_total'] < 0 ? $overallTotals['approved_adjustments_total'] : 0.0;
            $overallTotals['total_incoming'] = $overallTotals['opening_balance'] + $overallTotals['approved_charges_total'] + $overallTotals['approved_adjustments_positive'];
            $overallTotals['total_outgoing'] = $overallTotals['approved_expenses_total'] + abs($overallTotals['approved_adjustments_negative']);

            $isArchiveView = true;

            return $this->renderViewWithBreadcrumbs('backend.pages.petty-cash.print', [
                'ledger' => $ledger,
                'transactions' => $transactions,
                'period' => 'archive',
                'dateFrom' => null,
                'dateTo' => null,
                'totals' => $totals,
                'overallTotals' => $overallTotals,
                'approvedInPeriod' => $approvedInPeriod,
                'pendingInPeriod' => $pendingInPeriod,
                'isArchiveView' => $isArchiveView,
                'cycle' => $cycle,
            ]);
        }

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $period = $request->get('period', 'all');

        $query = $ledger->transactions()->whereNull('archive_cycle_id');

        switch ($period) {
            case 'today':
                $query->whereDate('transaction_date', today());
                break;
            case 'yesterday':
                $query->whereDate('transaction_date', today()->subDay());
                break;
            case '3days':
                $query->whereDate('transaction_date', '>=', today()->subDays(3));
                break;
            case '7days':
                $query->whereDate('transaction_date', '>=', today()->subDays(7));
                break;
            case 'month':
                $query->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year);
                break;
            default:
                if ($dateFrom) {
                    $query->whereDate('transaction_date', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $query->whereDate('transaction_date', '<=', $dateTo);
                }
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        $approvedInPeriod = $transactions->where('status', PettyCashTransaction::STATUS_APPROVED);
        $pendingInPeriod = $transactions->where('status', '!=', PettyCashTransaction::STATUS_APPROVED);

        $totals = [
            'incoming' => (float) $approvedInPeriod
                ->where('type', PettyCashTransaction::TYPE_CHARGE)
                ->sum('amount'),
            'outgoing' => (float) $approvedInPeriod
                ->where('type', PettyCashTransaction::TYPE_EXPENSE)
                ->sum('amount'),
            'adjustment_positive' => (float) $approvedInPeriod
                ->where('type', PettyCashTransaction::TYPE_ADJUSTMENT)
                ->filter(fn ($transaction) => $transaction->amount >= 0)
                ->sum('amount'),
            'adjustment_negative' => (float) $approvedInPeriod
                ->where('type', PettyCashTransaction::TYPE_ADJUSTMENT)
                ->filter(fn ($transaction) => $transaction->amount < 0)
                ->sum('amount'),
            'pending_incoming' => (float) $pendingInPeriod
                ->where('type', PettyCashTransaction::TYPE_CHARGE)
                ->sum('amount'),
            'pending_outgoing' => (float) $pendingInPeriod
                ->where('type', PettyCashTransaction::TYPE_EXPENSE)
                ->sum('amount'),
        ];

        $snapshot = $this->pettyCashService->getLedgerSnapshot($ledger);

        $approvedChargesTotal = (float) ($snapshot['approved_charges_total'] ?? 0);
        $approvedExpensesTotal = (float) ($snapshot['approved_expenses_total'] ?? 0);
        $approvedAdjustmentsTotal = (float) ($snapshot['approved_adjustments_total'] ?? 0);

        $overallTotals = [
            'opening_balance' => (float) $ledger->opening_balance,
            'limit_amount' => (float) $ledger->limit_amount,
            'current_balance' => (float) $ledger->current_balance,
            'approved_charges_total' => $approvedChargesTotal,
            'approved_expenses_total' => $approvedExpensesTotal,
            'approved_adjustments_total' => $approvedAdjustmentsTotal,
            'pending_charges_total' => (float) ($snapshot['pending_charges_total'] ?? 0),
            'pending_expenses_total' => (float) ($snapshot['pending_expenses_total'] ?? 0),
        ];

        $overallTotals['approved_adjustments_positive'] = $approvedAdjustmentsTotal > 0 ? $approvedAdjustmentsTotal : 0.0;
        $overallTotals['approved_adjustments_negative'] = $approvedAdjustmentsTotal < 0 ? $approvedAdjustmentsTotal : 0.0;
        $overallTotals['total_incoming'] = $overallTotals['opening_balance'] + $overallTotals['approved_charges_total'] + $overallTotals['approved_adjustments_positive'];
        $overallTotals['total_outgoing'] = $overallTotals['approved_expenses_total'] + abs($overallTotals['approved_adjustments_negative']);

        return $this->renderViewWithBreadcrumbs('backend.pages.petty-cash.print', [
            'ledger' => $ledger,
            'transactions' => $transactions,
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totals' => $totals,
            'overallTotals' => $overallTotals,
            'approvedInPeriod' => $approvedInPeriod,
            'pendingInPeriod' => $pendingInPeriod,
            'isArchiveView' => $isArchiveView,
            'cycle' => $cycle,
        ]);
    }

    public function downloadArchiveReport(Request $request, PettyCashLedger $ledger, PettyCashCycle $cycle)
    {
        $this->authorize('petty_cash.report.view');

        $user = $request->user();

        if ($cycle->ledger_id !== $ledger->id) {
            abort(404);
        }

        $isAdmin = $user && $user->hasRole(['Superadmin', 'Admin']);
        $isBranchUser = $user && (int) $user->branch_id === (int) $ledger->id;

        if (! $isAdmin && ! $isBranchUser) {
            abort(403, __('شما اجازه مشاهده این آرشیو را ندارید.'));
        }

        if (! $cycle->report_path) {
            return back()->with('error', __('فایل گزارش برای این آرشیو در دسترس نیست.'));
        }

        if (! Storage::disk('local')->exists($cycle->report_path)) {
            return back()->with('error', __('فایل گزارش آرشیو یافت نشد.'));
        }

        $filename = sprintf('petty-cash-cycle-%d-ledger-%d.xls', $cycle->id, $ledger->id);

        return Storage::disk('local')->download($cycle->report_path, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    public function chargeRequestPage(PettyCashLedger $ledger)
    {
        $this->authorize('petty_cash.ledger.view');

        $user = Auth::user();
        if (! $this->userCanAccessLedger($user, $ledger)) {
            abort(403, __('شما به این شعبه دسترسی ندارید.'));
        }

        $this->setBreadcrumbTitle(__('درخواست شارژ تنخواه'))
            ->addBreadcrumbItem(__('مدیریت مالی'), null)
            ->addBreadcrumbItem(__('تنخواه شعب'), route('admin.petty-cash.index', ['ledger' => $ledger->id]))
            ->addBreadcrumbItem(__('درخواست شارژ'), null);

        $pendingChargeRequests = $ledger->transactions()
            ->with(['requester:id,first_name,last_name'])
            ->whereNull('archive_cycle_id')
            ->where('type', PettyCashTransaction::TYPE_CHARGE)
            ->whereIn('status', [
                PettyCashTransaction::STATUS_SUBMITTED,
                PettyCashTransaction::STATUS_UNDER_REVIEW,
                PettyCashTransaction::STATUS_NEEDS_CHANGES,
            ])
            ->orderByDesc('transaction_date')
            ->take(25)
            ->get();

        return $this->renderViewWithBreadcrumbs('backend.pages.petty-cash.charge-request', [
            'ledger' => $ledger,
            'pendingChargeRequests' => $pendingChargeRequests,
            'isAdminUser' => $user?->hasRole(['Superadmin', 'Admin']) ?? false,
        ]);
    }

    public function settlementPage(PettyCashLedger $ledger)
    {
        $this->authorize('petty_cash.ledger.view');

        $user = Auth::user();
        if (! $this->userCanAccessLedger($user, $ledger)) {
            abort(403, __('شما به این شعبه دسترسی ندارید.'));
        }

        $this->setBreadcrumbTitle(__('تسویه تنخواه شعبه'))
            ->addBreadcrumbItem(__('مدیریت مالی'), null)
            ->addBreadcrumbItem(__('تنخواه شعب'), route('admin.petty-cash.index', ['ledger' => $ledger->id]))
            ->addBreadcrumbItem(__('تسویه'), null);

        return $this->renderViewWithBreadcrumbs('backend.pages.petty-cash.settlement', [
            'ledger' => $ledger,
        ]);
    }

    public function transactionsPage(PettyCashLedger $ledger)
    {
        $this->authorize('petty_cash.ledger.view');

        $user = Auth::user();
        if (! $this->userCanAccessLedger($user, $ledger)) {
            abort(403, __('شما به این شعبه دسترسی ندارید.'));
        }

        $ledger->loadMissing('assignedUser:id,first_name,last_name,email');

        $this->setBreadcrumbTitle(__('ثبت تراکنش‌های تنخواه'))
            ->addBreadcrumbItem(__('مدیریت مالی'), null)
            ->addBreadcrumbItem(__('تنخواه شعب'), route('admin.petty-cash.index', ['ledger' => $ledger->id]))
            ->addBreadcrumbItem(__('ثبت تراکنش'), null);

        $pendingChargeRequests = $ledger->transactions()
            ->with(['requester:id,first_name,last_name'])
            ->whereNull('archive_cycle_id')
            ->where('type', PettyCashTransaction::TYPE_CHARGE)
            ->whereIn('status', [
                PettyCashTransaction::STATUS_SUBMITTED,
                PettyCashTransaction::STATUS_UNDER_REVIEW,
                PettyCashTransaction::STATUS_NEEDS_CHANGES,
            ])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->take(25)
            ->get();

        // Get all available ledgers for dropdown
        $availableLedgers = collect();
        if ($user && $user->hasRole(['Superadmin', 'Admin'])) {
            $availableLedgers = PettyCashLedger::with(['assignedUser:id,first_name,last_name,email'])
                ->orderBy('branch_name')
                ->get();
        } else {
            // For other users, show only their assigned branch
            if ($user && $user->branch_id) {
                $userLedger = PettyCashLedger::with(['assignedUser:id,first_name,last_name,email'])->find($user->branch_id);
                if ($userLedger) {
                    $availableLedgers->push($userLedger);
                }
            }
        }

        return $this->renderViewWithBreadcrumbs('backend.pages.petty-cash.transactions', [
            'ledger' => $ledger,
            'availableLedgers' => $availableLedgers,
            'pendingChargeRequests' => $pendingChargeRequests,
            'isAdminUser' => $user?->hasRole(['Superadmin', 'Admin']) ?? false,
        ]);
    }

    public function archivesIndex(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole(['Superadmin', 'Admin'])) {
            abort(403, __('شما اجازه مشاهده بایگانی تنخواه را ندارید.'));
        }

        $this->setBreadcrumbTitle(__('بایگانی تنخواه'))
            ->addBreadcrumbItem(__('مدیریت مالی'), null)
            ->addBreadcrumbItem(__('بایگانی تنخواه'), null);

        $ledgerId = $request->get('ledger_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = PettyCashCycle::query()
            ->with(['ledger:id,branch_name', 'closer:id,first_name,last_name'])
            ->where('status', 'closed');

        if ($ledgerId) {
            $query->where('ledger_id', $ledgerId);
        }

        if ($dateFrom) {
            $query->whereDate('closed_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('closed_at', '<=', $dateTo);
        }

        $archives = $query->orderByDesc('closed_at')->paginate(20)->withQueryString();
        $ledgers = PettyCashLedger::orderBy('branch_name')->get(['id', 'branch_name']);

        return $this->renderViewWithBreadcrumbs('backend.pages.petty-cash.archives', [
            'archives' => $archives,
            'ledgers' => $ledgers,
            'filters' => [
                'ledger_id' => $ledgerId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'canManageArchives' => $user->hasRole('Superadmin'),
        ]);
    }

    public function showArchive(PettyCashCycle $cycle)
    {
        $user = Auth::user();

        if (! $user || ! $user->hasRole(['Superadmin', 'Admin'])) {
            abort(403, __('شما اجازه مشاهده این سند را ندارید.'));
        }

        $cycle->load(['ledger', 'closer']);

        $archivedTransactions = $cycle->archivedTransactions()
            ->with([
                'requester:id,first_name,last_name',
                'approver:id,first_name,last_name',
                'media',
            ])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $activeTransactions = PettyCashTransaction::where('ledger_id', $cycle->ledger_id)
            ->whereNull('archive_cycle_id')
            ->with([
                'requester:id,first_name,last_name',
                'approver:id,first_name,last_name',
                'media',
            ])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $statusLabels = [
            PettyCashTransaction::STATUS_DRAFT => __('پیش‌نویس'),
            PettyCashTransaction::STATUS_SUBMITTED => __('ارسال‌شده'),
            PettyCashTransaction::STATUS_APPROVED => __('تایید‌شده'),
            PettyCashTransaction::STATUS_REJECTED => __('رد‌شده'),
            PettyCashTransaction::STATUS_NEEDS_CHANGES => __('نیاز به اصلاح'),
            PettyCashTransaction::STATUS_UNDER_REVIEW => __('در حال بررسی'),
        ];

        $this->setBreadcrumbTitle(__('جزئیات سند بایگانی'))
            ->addBreadcrumbItem(__('مدیریت مالی'), null)
            ->addBreadcrumbItem(__('بایگانی تنخواه'), route('admin.petty-cash.archives.index'))
            ->addBreadcrumbItem(__('مشاهده سند'), null);

        return $this->renderViewWithBreadcrumbs('backend.pages.petty-cash.archive-show', [
            'archive' => $cycle,
            'archivedTransactions' => $archivedTransactions,
            'activeTransactions' => $activeTransactions,
            'statusLabels' => $statusLabels,
            'canManageArchives' => $user->hasRole('Superadmin'),
        ]);
    }

    public function editArchive(PettyCashCycle $cycle)
    {
        $user = Auth::user();

        if (! $user || ! $user->hasRole('Superadmin')) {
            abort(403, __('تنها سوپر ادمین می‌تواند سند را ویرایش کند.'));
        }

        $cycle->load(['ledger', 'closer', 'archivedTransactions']);

        $this->setBreadcrumbTitle(__('ویرایش سند بایگانی'))
            ->addBreadcrumbItem(__('مدیریت مالی'), null)
            ->addBreadcrumbItem(__('بایگانی تنخواه'), route('admin.petty-cash.archives.index'))
            ->addBreadcrumbItem(__('ویرایش سند'), null);

        return $this->renderViewWithBreadcrumbs('backend.pages.petty-cash.archives-edit', [
            'archive' => $cycle,
        ]);
    }

    public function updateArchive(Request $request, PettyCashCycle $cycle, PettyCashArchiveService $archiveService)
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('Superadmin')) {
            abort(403, __('تنها سوپر ادمین می‌تواند سند را ویرایش کند.'));
        }

        $validated = $request->validate([
            'closing_note' => ['nullable', 'string', 'max:1000'],
            'regenerate_report' => ['sometimes', 'boolean'],
        ]);

        $archiveService->updateCycle($cycle, $validated, $user);

        return redirect()
            ->route('admin.petty-cash.archives.index')
            ->with('success', __('سند بایگانی با موفقیت به‌روزرسانی شد.'));
    }

    public function destroyArchive(Request $request, PettyCashCycle $cycle, PettyCashArchiveService $archiveService)
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('Superadmin')) {
            abort(403, __('تنها سوپر ادمین می‌تواند سند را حذف کند.'));
        }

        $archiveService->deleteCycle($cycle);

        return redirect()
            ->route('admin.petty-cash.archives.index')
            ->with('success', __('سند بایگانی با موفقیت حذف شد.'));
    }

    public function edit(PettyCashLedger $ledger)
    {
        $this->authorize('petty_cash.ledger.edit');

        $this->setBreadcrumbTitle(__('ویرایش شعبه'))
            ->addBreadcrumbItem(__('مدیریت مالی'), null)
            ->addBreadcrumbItem(__('تنخواه شعب'), route('admin.petty-cash.index'))
            ->addBreadcrumbItem(__('ویرایش'), null);

        return $this->renderViewWithBreadcrumbs('backend.pages.petty-cash.edit', [
            'ledger' => $ledger,
            'assignableUsers' => $this->getAssignableUsers($ledger),
        ]);
    }

    public function update(Request $request, PettyCashLedger $ledger)
    {
        $this->authorize('petty_cash.ledger.edit');

        $validated = $request->validate([
            'branch_name' => ['required', 'string', 'max:255'],
            'limit_amount' => ['required', 'numeric', 'min:0'],
            'max_charge_request_amount' => ['nullable', 'numeric', 'min:0'],
            'max_transaction_amount' => ['nullable', 'numeric', 'min:0'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'assigned_user_id' => ['required', 'exists:users,id'],
            'account_number' => ['required', 'string', 'max:50'],
            'iban' => ['required', 'string', 'max:34'],
            'card_number' => ['required', 'string', 'max:20'],
            'account_holder' => ['required', 'string', 'max:255'],
        ]);

        $previousAssignedUser = $ledger->assigned_user_id;

        $ledger->update([
            'branch_name' => $validated['branch_name'],
            'limit_amount' => $validated['limit_amount'],
            'max_charge_request_amount' => (float) ($validated['max_charge_request_amount'] ?? 0),
            'max_transaction_amount' => (float) ($validated['max_transaction_amount'] ?? 0),
            'opening_balance' => $validated['opening_balance'] ?? $validated['limit_amount'],
            'is_active' => $validated['is_active'] ?? true,
            'assigned_user_id' => $validated['assigned_user_id'] ?? null,
            'account_number' => $validated['account_number'],
            'iban' => $validated['iban'],
            'card_number' => $validated['card_number'],
            'account_holder' => $validated['account_holder'],
        ]);

        // Reset current balance if opening balance changed
        if ($ledger->wasChanged('opening_balance')) {
            $ledger->current_balance = $ledger->opening_balance;
            $ledger->save();
        }

        $this->syncLedgerAssignedUser($ledger, $validated['assigned_user_id'] ?? null, $previousAssignedUser);

        return redirect()
            ->route('admin.petty-cash.index', ['ledger' => $ledger->id])
            ->with('success', __('شعبه با موفقیت به‌روزرسانی شد.'));
    }

    public function delete(PettyCashLedger $ledger)
    {
        $this->authorize('petty_cash.ledger.delete');

        $this->setBreadcrumbTitle(__('حذف شعبه'))
            ->addBreadcrumbItem(__('مدیریت مالی'), null)
            ->addBreadcrumbItem(__('تنخواه شعب'), route('admin.petty-cash.index'))
            ->addBreadcrumbItem(__('حذف'), null);

        return $this->renderViewWithBreadcrumbs('backend.pages.petty-cash.delete', [
            'ledger' => $ledger,
        ]);
    }

    public function destroy(Request $request, PettyCashLedger $ledger)
    {
        $this->authorize('petty_cash.ledger.delete');

        $hadTransactions = $ledger->transactions()->count() > 0;

        // Only Superadmin can delete ledgers with transactions
        if ($hadTransactions && !auth()->user()->hasRole('Superadmin')) {
            return back()->with('error', __('فقط مدیر ارشد می‌تواند شعبه‌ای که دارای تراکنش است را حذف کند.'));
        }

        // Check if ledger has transactions and user is Superadmin
        if ($hadTransactions) {
            // Validate double confirmation
            $request->validate([
                'confirm_delete' => 'required|in:DELETE_BRANCH_WITH_TRANSACTIONS',
                'backup_reason' => 'required|string|max:500',
            ]);

            // Create backup of transactions before deletion
            $backupData = [
                'ledger' => $ledger->toArray(),
                'transactions' => $ledger->transactions()->with(['requester', 'approver'])->get()->toArray(),
                'deleted_by' => auth()->id(),
                'deleted_at' => now(),
                'reason' => $request->backup_reason,
            ];

            // Save backup to file
            $backupFileName = 'ledger_backup_' . $ledger->id . '_' . now()->format('Y_m_d_H_i_s') . '.json';
            $backupPath = storage_path('app/backups/' . $backupFileName);

            // Ensure backup directory exists with proper permissions
            $backupDir = dirname($backupPath);
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
                $this->adjustBackupOwnership($backupDir);
            }

            // Save backup to file with error handling
            try {
                $result = file_put_contents($backupPath, json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                if ($result === false) {
                    throw new \Exception('Failed to write backup file');
                }

                // Ensure the file has correct permissions
                $this->adjustBackupOwnership($backupPath);

            } catch (\Exception $e) {
                \Log::error('Failed to create backup file', [
                    'ledger_id' => $ledger->id,
                    'backup_path' => $backupPath,
                    'error' => $e->getMessage(),
                    'file_exists' => file_exists($backupPath),
                    'is_writable' => is_writable($backupDir),
                ]);
                // Continue with deletion even if backup fails
            }

            // Log the deletion
            \Log::warning('Ledger deleted with transactions', [
                'ledger_id' => $ledger->id,
                'branch_name' => $ledger->branch_name,
                'deleted_by' => auth()->user()->full_name,
                'transactions_count' => $ledger->transactions()->count(),
                'backup_file' => $backupFileName,
                'reason' => $request->backup_reason,
            ]);
        }

        $affectedUsers = User::where('branch_id', $ledger->id)->get();

        $ledger->delete();

        $affectedUsers->each(function (User $user) {
            $user->branch_id = null;
            $user->save();

            if ($user->hasRole('Branch Manager') && ! PettyCashLedger::where('assigned_user_id', $user->id)->exists()) {
                $user->removeRole('Branch Manager');
            }
        });

        $message = $hadTransactions
            ? __('شعبه با موفقیت حذف شد. بک‌آپ تراکنش‌ها ذخیره شد.')
            : __('شعبه با موفقیت حذف شد.');

        return redirect()
            ->route('admin.petty-cash.index')
            ->with('success', $message);
    }

    public function backups(Request $request)
    {
        $this->authorize('petty_cash.ledger.delete');

        $this->setBreadcrumbTitle(__('مدیریت بک‌آپ‌ها'))
            ->addBreadcrumbItem(__('مدیریت مالی'), null)
            ->addBreadcrumbItem(__('تنخواه شعب'), route('admin.petty-cash.index'))
            ->addBreadcrumbItem(__('بک‌آپ‌ها'), null);

        $backupDir = storage_path('app/backups');
        $backupFiles = collect();

        if (file_exists($backupDir)) {
            $files = scandir($backupDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                    $filePath = $backupDir . '/' . $file;
                    $createdAt = Carbon::createFromTimestamp(filemtime($filePath));
                    $fileInfo = [
                        'name' => $file,
                        'size' => filesize($filePath),
                        'size_formatted' => $this->formatFileSize(filesize($filePath)),
                        'created_at' => $createdAt,
                        'created_at_formatted' => Verta::instance($createdAt)->format('Y/m/d H:i'),
                    ];

                    // Try to read metadata from backup file
                    $content = json_decode(file_get_contents($filePath), true);
                    if ($content && isset($content['ledger'])) {
                        $fileInfo['metadata'] = $content;

                        // Get user who deleted the ledger
                        if (isset($content['deleted_by'])) {
                            $deletedBy = \App\Models\User::find($content['deleted_by']);
                            $fileInfo['metadata']['deleted_by_name'] = $deletedBy ? $deletedBy->full_name : 'نامشخص';
                        }
                    }

                    $backupFiles->push($fileInfo);
                }
            }
        }

        // Sort by creation date (newest first)
        $backupFiles = $backupFiles->sortByDesc('created_at')->values();

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginated = new LengthAwarePaginator(
            $backupFiles->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $backupFiles->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return $this->renderViewWithBreadcrumbs('backend.pages.petty-cash.backups', [
            'backupFiles' => $paginated,
        ]);
    }

    public function downloadBackup($filename)
    {
        $this->authorize('petty_cash.ledger.delete');

        $backupPath = storage_path('app/backups/' . $filename);

        if (!file_exists($backupPath)) {
            abort(404, __('فایل بک‌آپ یافت نشد.'));
        }

        return response()->download($backupPath, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function deleteBackup(Request $request, $filename)
    {
        $this->authorize('petty_cash.ledger.delete');

        $backupPath = storage_path('app/backups/' . $filename);

        if (!file_exists($backupPath)) {
            return back()->with('error', __('فایل بک‌آپ یافت نشد.'));
        }

        if (unlink($backupPath)) {
            return back()->with('success', __('فایل بک‌آپ با موفقیت حذف شد.'));
        } else {
            return back()->with('error', __('خطا در حذف فایل بک‌آپ.'));
        }
    }

    public function downloadModulePackage(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('Superadmin')) {
            abort(403, __('شما اجازه دسترسی به این بخش را ندارید.'));
        }

        if (config('app.demo_mode', false)) {
            return back()->with('error', __('تهیه بک‌آپ ماژول در حالت دمو غیرفعال است.'));
        }

        $timestamp = now()->format('Y_m_d_H_i_s');
        $fileName = "petty-cash-module_{$timestamp}.zip";
        $backupDir = storage_path('app/backups/module-packages');
        $zipPath = $backupDir . '/' . $fileName;

        if (! File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
            $this->adjustBackupOwnership($backupDir);
        }

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', __('امکان ایجاد فایل بک‌آپ وجود ندارد.'));
        }

        foreach ($this->getPettyCashModulePaths() as $relativePath) {
            $absolutePath = base_path($relativePath);

            if (! File::exists($absolutePath)) {
                continue;
            }

            if (File::isFile($absolutePath)) {
                $zip->addFile($absolutePath, $relativePath);
                continue;
            }

            $this->addDirectoryToZip($zip, $absolutePath, $relativePath);
        }

        $zip->close();
        $this->adjustBackupOwnership($zipPath);

        return response()->download($zipPath, $fileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    private function getPettyCashModulePaths(): array
    {
        $defaultPaths = [
            'app/Console/Commands/PettyCashArchive.php',
            'app/Http/Controllers/Backend/PettyCashController.php',
            'app/Livewire/PettyCash',
            'app/Models/PettyCashLedger.php',
            'app/Models/PettyCashTransaction.php',
            'app/Services/PettyCash',
            'database/migrations/2025_10_18_182158_create_petty_cash_ledgers_table.php',
            'database/migrations/2025_10_19_000001_add_assigned_user_id_to_petty_cash_ledgers_table.php',
            'database/migrations/2025_10_18_182204_create_petty_cash_transactions_table.php',
            'config/petty-cash.php',
            'config/smart-invoice.php',
            'resources/lang/fa/smart_invoice.php',
            'resources/views/backend/pages/petty-cash',
            'resources/views/livewire/petty-cash',
            'routes/web.php',
        ];

        $additionalPaths = config('petty-cash.backups.additional_paths', []);

        if (is_string($additionalPaths)) {
            $additionalPaths = array_filter(array_map('trim', explode(',', $additionalPaths)));
        }

        $paths = array_merge($defaultPaths, is_array($additionalPaths) ? $additionalPaths : []);

        return array_values(array_unique($paths));
    }

    private function addDirectoryToZip(ZipArchive $zip, string $directory, string $relativePath): void
    {
        $zip->addEmptyDir($relativePath);

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativeName = $relativePath . '/' . str_replace('\\', '/', $files->getSubPathName());

            if ($file->isDir()) {
                $zip->addEmptyDir($relativeName);
            } else {
                $zip->addFile($filePath, $relativeName);
            }
        }
    }

    private function getAssignableUsers(?PettyCashLedger $ledger = null)
    {
        $query = User::query()
            ->orderBy('first_name')
            ->orderBy('last_name');

        if ($ledger) {
            $query->where(function ($builder) use ($ledger) {
                $builder->whereNull('branch_id')
                    ->orWhere('branch_id', $ledger->id);
            });
        } else {
            $query->whereNull('branch_id');
        }

        return $query->get();
    }

    private function syncLedgerAssignedUser(PettyCashLedger $ledger, ?int $newUserId, ?int $previousUserId = null): void
    {
        if ($previousUserId && $previousUserId !== $newUserId) {
            $previousUser = User::find($previousUserId);

            if ($previousUser) {
                $previousUser->branch_id = null;
                $previousUser->save();

                if ($previousUser->hasRole('Branch Manager') && ! PettyCashLedger::where('assigned_user_id', $previousUserId)->exists()) {
                    $previousUser->removeRole('Branch Manager');
                }
            }
        }

        if (! $newUserId) {
            return;
        }

        PettyCashLedger::where('assigned_user_id', $newUserId)
            ->where('id', '!=', $ledger->id)
            ->update(['assigned_user_id' => null]);

        $user = User::find($newUserId);

        if (! $user) {
            return;
        }

        if ($user->branch_id && $user->branch_id !== $ledger->id) {
            PettyCashLedger::where('id', $user->branch_id)->update(['assigned_user_id' => null]);
        }

        $user->branch_id = $ledger->id;
        $user->save();

        if (! $user->hasRole('Branch Manager')) {
            $user->assignRole('Branch Manager');
        }
    }

    private function hydrateLedgerWithMetrics(PettyCashLedger $ledger): PettyCashLedger
    {
        foreach ($this->pettyCashService->getLedgerSnapshot($ledger) as $key => $value) {
            $ledger->setAttribute($key, $value);
        }

        return $ledger;
    }

    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < 3) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function adjustBackupOwnership(string $path): void
    {
        if (! config('petty-cash.backups.adjust_permissions', false)) {
            return;
        }

        $canChangeOwnership = function_exists('chown') || function_exists('chgrp');
        if (! $canChangeOwnership) {
            return;
        }

        if (function_exists('posix_geteuid') && posix_geteuid() !== 0) {
            return;
        }

        $owner = config('petty-cash.backups.owner');
        $group = config('petty-cash.backups.group');

        if ($owner && function_exists('chown')) {
            @chown($path, $owner);
        }

        if ($group && function_exists('chgrp')) {
            @chgrp($path, $group);
        }
    }

    public function archive(Request $request, PettyCashLedger $ledger)
    {
        $this->authorize('petty_cash.archive.manage');

        // Archive logic will be implemented later
        return response()->json(['success' => true]);
    }

    private function userCanAccessLedger(?\App\Models\User $user, PettyCashLedger $ledger): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole(['Superadmin', 'Admin'])) {
            return true;
        }

        return (int) $user->branch_id === (int) $ledger->id;
    }
}
