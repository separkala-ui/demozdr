<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'requested_close_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
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
}
