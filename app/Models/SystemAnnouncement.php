<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SystemAnnouncement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'type',
        'priority',
        'is_active',
        'is_pinned',
        'starts_at',
        'expires_at',
        'created_by',
        'target_roles',
        'target_users',
        'icon',
        'action_url',
        'action_text',
        'view_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_pinned' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'target_roles' => 'array',
        'target_users' => 'array',
        'view_count' => 'integer',
    ];

    /**
     * رابطه با کاربر ایجاد کننده
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope برای اطلاعیه‌های فعال
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope برای اطلاعیه‌های در حال نمایش
     */
    public function scopeVisible($query)
    {
        $now = Carbon::now();
        
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', $now);
            });
    }

    /**
     * Scope برای اطلاعیه‌های سنجاق شده
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope برای فیلتر بر اساس نقش کاربر
     */
    public function scopeForUser($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            // اگر target_roles null باشد = همه
            $q->whereNull('target_roles');
            
            // یا اگر نقش کاربر در target_roles باشد
            if ($user) {
                $userRoles = $user->roles->pluck('name')->toArray();
                foreach ($userRoles as $role) {
                    $q->orWhereJsonContains('target_roles', $role);
                }
            }
        })->where(function ($q) use ($user) {
            // اگر target_users null باشد = همه
            $q->whereNull('target_users');
            
            // یا اگر ID کاربر در target_users باشد
            if ($user) {
                $q->orWhereJsonContains('target_users', $user->id);
            }
        });
    }

    /**
     * Scope برای مرتب‌سازی بر اساس اولویت
     */
    public function scopeByPriority($query)
    {
        $priorityOrder = [
            'urgent' => 4,
            'high' => 3,
            'normal' => 2,
            'low' => 1,
        ];

        return $query->orderByRaw("
            CASE priority
                WHEN 'urgent' THEN 4
                WHEN 'high' THEN 3
                WHEN 'normal' THEN 2
                WHEN 'low' THEN 1
                ELSE 0
            END DESC
        ")->orderBy('is_pinned', 'desc')
          ->orderBy('created_at', 'desc');
    }

    /**
     * افزایش تعداد بازدید
     */
    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    /**
     * بررسی اینکه آیا اطلاعیه منقضی شده
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * بررسی اینکه آیا زمان نمایش اطلاعیه رسیده
     */
    public function hasStarted(): bool
    {
        if (!$this->starts_at) {
            return true;
        }

        return Carbon::now()->isAfter($this->starts_at);
    }

    /**
     * بررسی اینکه آیا اطلاعیه قابل نمایش است
     */
    public function isVisibleNow(): bool
    {
        return $this->is_active 
            && $this->hasStarted() 
            && !$this->isExpired();
    }

    /**
     * دریافت رنگ بر اساس نوع
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'success' => 'emerald',
            'warning' => 'amber',
            'danger' => 'rose',
            default => 'blue',
        };
    }

    /**
     * دریافت آیکون بر اساس نوع
     */
    public function getDefaultIconAttribute(): string
    {
        return match($this->type) {
            'success' => 'lucide:check-circle',
            'warning' => 'lucide:alert-triangle',
            'danger' => 'lucide:alert-octagon',
            default => 'lucide:info',
        };
    }
}
