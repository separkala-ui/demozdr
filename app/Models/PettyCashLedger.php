<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User;
use App\Models\PettyCashCycle;
use Hekmatinasser\Verta\Verta;

class PettyCashLedger extends Model
{
    protected $fillable = [
        'branch_id',
        'branch_name',
        'limit_amount',
        'max_charge_request_amount',
        'max_transaction_amount',
        'opening_balance',
        'current_balance',
        'last_reconciled_at',
        'last_charge_at',
        'is_active',
        'settings',
        'assigned_user_id',
        'account_number',
        'iban',
        'card_number',
        'account_holder',
        'manager_mobile',
    ];

    protected $casts = [
        'limit_amount' => 'decimal:2',
        'max_charge_request_amount' => 'decimal:2',
        'max_transaction_amount' => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'last_reconciled_at' => 'datetime',
        'last_charge_at' => 'datetime',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    protected $dates = ['last_reconciled_at', 'last_charge_at'];

    public function transactions(): HasMany
    {
        return $this->hasMany(PettyCashTransaction::class, 'ledger_id');
    }

    public function approvedTransactions(): HasMany
    {
        return $this->transactions()->where('status', 'approved')->whereNull('archive_cycle_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Get all users with access to this branch (many-to-many).
     */
    public function branchUsers()
    {
        return $this->hasMany(BranchUser::class, 'ledger_id');
    }

    /**
     * Get all users with access to this branch through pivot table.
     */
    public function accessUsers()
    {
        return $this->belongsToMany(User::class, 'branch_users', 'ledger_id', 'user_id')
            ->withPivot('access_type', 'is_active', 'permissions')
            ->withTimestamps();
    }

    public function cycles(): HasMany
    {
        return $this->hasMany(PettyCashCycle::class, 'ledger_id');
    }

    public function archivedCycles(): HasMany
    {
        return $this->cycles()->where('status', 'closed')->orderByDesc('closed_at');
    }

    public function currentCycle(): HasOne
    {
        return $this->hasOne(PettyCashCycle::class, 'ledger_id')->whereIn('status', ['open', 'pending_close'])->latestOfMany('opened_at');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the last reconciled at in Jalali format
     */
    public function getLastReconciledAtJalaliAttribute()
    {
        return $this->last_reconciled_at ? Verta::instance($this->last_reconciled_at)->format('Y/m/d H:i') : null;
    }

    /**
     * Get the last charge at in Jalali format
     */
    public function getLastChargeAtJalaliAttribute()
    {
        return $this->last_charge_at ? Verta::instance($this->last_charge_at)->format('Y/m/d H:i') : null;
    }
}
