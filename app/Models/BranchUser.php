<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Branch User Model
 * 
 * مدل ارتباط بین شعبه و کاربران
 * این جدول امکان اضافه کردن چند کاربر با دسترسی‌های مختلف به یک شعبه را فراهم می‌کند
 */
class BranchUser extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'branch_users';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ledger_id',
        'user_id',
        'access_type',
        'is_active',
        'permissions',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'permissions' => 'array',
    ];

    /**
     * Access types available
     */
    public const ACCESS_TYPES = [
        'petty_cash' => 'تنخواه',
        'inspection' => 'بازرسی',
        'quality_control' => 'کنترل کیفیت',
        'production_engineering' => 'مهندسی تولید',
    ];

    /**
     * دریافت شعبه (Ledger)
     */
    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    /**
     * دریافت کاربر
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: فقط کاربران فعال
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: فیلتر بر اساس نوع دسترسی
     */
    public function scopeByAccessType($query, string $accessType)
    {
        return $query->where('access_type', $accessType);
    }

    /**
     * بررسی دسترسی خاص
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * اضافه کردن دسترسی
     */
    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }
    }

    /**
     * حذف دسترسی
     */
    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_values(array_filter($permissions, fn($p) => $p !== $permission));
        $this->permissions = $permissions;
        $this->save();
    }

    /**
     * دریافت نام نوع دسترسی
     */
    public function getAccessTypeNameAttribute(): string
    {
        return self::ACCESS_TYPES[$this->access_type] ?? $this->access_type;
    }
}
