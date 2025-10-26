<?php

namespace App\Models;

use App\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Hekmatinasser\Verta\Verta;

class PettyCashTransaction extends Model implements SpatieHasMedia
{
    use HasMedia;

    public const TYPE_CHARGE = 'charge';
    public const TYPE_EXPENSE = 'expense';
    public const TYPE_ADJUSTMENT = 'adjustment';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_NEEDS_CHANGES = 'needs_changes';
    public const STATUS_UNDER_REVIEW = 'under_review';

    protected $fillable = [
        'ledger_id',
        'type',
        'status',
        'amount',
        'amount_local_currency',
        'currency',
        'transaction_date',
        'reference_number',
        'description',
        'category',
        'meta',
        'requested_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'carry_over_id',
        'archive_cycle_id',
        'archived_at',
        'charge_origin',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_local_currency' => 'decimal:2',
        'transaction_date' => 'datetime',
        'meta' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    protected $dates = ['transaction_date', 'approved_at', 'rejected_at'];

    protected $attributes = [
        'currency' => 'IRR',
    ];

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(PettyCashLedger::class, 'ledger_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function carryOverSource(): BelongsTo
    {
        return $this->belongsTo(self::class, 'carry_over_id');
    }

    public function archiveCycle(): BelongsTo
    {
        return $this->belongsTo(PettyCashCycle::class, 'archive_cycle_id');
    }

    protected function isCredit(): Attribute
    {
        return Attribute::get(fn () => in_array($this->type, [self::TYPE_CHARGE, self::TYPE_ADJUSTMENT]) && $this->amount >= 0);
    }

    protected function isDebit(): Attribute
    {
        return Attribute::get(fn () => $this->type === self::TYPE_EXPENSE && $this->amount >= 0);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeCharges($query)
    {
        return $query->where('type', self::TYPE_CHARGE);
    }

    public function scopeExpenses($query)
    {
        return $query->where('type', self::TYPE_EXPENSE);
    }

    /**
     * Get the transaction date in Jalali format
     */
    public function getTransactionDateJalaliAttribute()
    {
        return $this->transaction_date ? Verta::instance($this->transaction_date)->format('Y/m/d H:i') : null;
    }

    /**
     * Get the approved at in Jalali format
     */
    public function getApprovedAtJalaliAttribute()
    {
        return $this->approved_at ? Verta::instance($this->approved_at)->format('Y/m/d H:i') : null;
    }

    /**
     * Get the rejected at in Jalali format
     */
    public function getRejectedAtJalaliAttribute()
    {
        return $this->rejected_at ? Verta::instance($this->rejected_at)->format('Y/m/d H:i') : null;
    }

    /**
     * Get formatted amount with currency (always in Rials)
     */
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount) . ' ریال';
    }

    /**
     * Get amount in Rials (formatted for display)
     */
    public function getAmountInRialsAttribute()
    {
        return number_format($this->amount) . ' ریال';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('invoice')->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp']);
        $this->addMediaCollection('bank_receipt')->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp']);
        $this->addMediaCollection('charge_request')->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->sharpen(10);

        $this->addMediaConversion('medium')
            ->width(600)
            ->height(600);
    }
}
