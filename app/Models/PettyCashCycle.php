<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PettyCashCycle extends Model
{
    protected $fillable = [
        'ledger_id',
        'status',
        'opened_at',
        'opening_balance',
        'requested_close_by',
        'requested_close_at',
        'request_note',
        'closed_by',
        'closed_at',
        'closing_balance',
        'closing_note',
        'transactions_count',
        'expenses_count',
        'total_charges',
        'total_expenses',
        'total_adjustments',
        'summary',
        'report_path',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'requested_close_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'total_charges' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'total_adjustments' => 'decimal:2',
        'summary' => 'array',
    ];

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(PettyCashLedger::class, 'ledger_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_close_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function archivedTransactions(): HasMany
    {
        return $this->hasMany(PettyCashTransaction::class, 'archive_cycle_id');
    }
}
