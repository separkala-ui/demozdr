<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\AuthorizationChecker;
use App\Concerns\QueryBuilderTrait;
use App\Notifications\AdminResetPasswordNotification;
use App\Observers\UserObserver;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Auth\Notifications\ResetPassword as DefaultResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable implements MustVerifyEmail, HasName, FilamentUser
{
    use AuthorizationChecker;
    use HasApiTokens;
    use HasFactory;
    use HasPermissions;
    use HasRoles;
    use Notifiable;
    use QueryBuilderTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'mobile',
        'password',
        'username',
        'avatar_id',
        'branch_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The attributes that should be appended to the model.
     */
    protected $appends = [
        'avatar_url',
        'full_name',
    ];

    /**
     * The relationships that should be eager loaded.
     */
    protected $with = [
        'avatar',
    ];

    public function actionLogs()
    {
        return $this->hasMany(ActionLog::class, 'action_by');
    }

    /**
     * Get the user's metadata.
     */
    public function userMeta()
    {
        return $this->hasMany(UserMeta::class);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        // Check if the request is for the admin panel
        if (request()->is('admin/*')) {
            $this->notify(new AdminResetPasswordNotification($token));
        } else {
            $this->notify(new DefaultResetPassword($token));
        }
    }

    /**
     * Override hasPermissionTo to give Superadmin all permissions
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        // Superadmin has all permissions
        if ($this->hasRole('Superadmin')) {
            return true;
        }

        // For regular users, check via Gate or Spatie
        return $this->hasPermissionViaRole($permission, $guardName) || $this->hasDirectPermission($permission);
    }

    /**
     * Check if user has permission via role
     */
    public function hasPermissionViaRole($permission, $guardName = null): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission, $guardName) {
                $query->where('name', $permission);
                if ($guardName) {
                    $query->where('guard_name', $guardName);
                }
            })
            ->exists();
    }

    /**
     * Check if user has direct permission
     */
    public function hasDirectPermission($permission, $guardName = null): bool
    {
        return $this->permissions()
            ->where('name', $permission)
            ->when($guardName, function ($query) use ($guardName) {
                return $query->where('guard_name', $guardName);
            })
            ->exists();
    }

    /**
     * Check if user is Superadmin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Superadmin');
    }

    /**
     * Get the user's avatar media.
     */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'avatar_id', 'id');
    }

    /**
     * Get the user's branch (petty cash ledger).
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(PettyCashLedger::class, 'branch_id');
    }

    /**
     * Get all branches this user has access to (many-to-many).
     */
    public function branchUsers()
    {
        return $this->hasMany(BranchUser::class);
    }

    /**
     * Get all branches (ledgers) this user has access to.
     */
    public function branches()
    {
        return $this->belongsToMany(Ledger::class, 'branch_users', 'user_id', 'ledger_id')
            ->withPivot('access_type', 'is_active', 'permissions')
            ->withTimestamps();
    }

    /**
     * Check if user has access to a specific branch with a specific access type.
     */
    public function hasAccessToBranch(int $ledgerId, ?string $accessType = null): bool
    {
        $query = $this->branchUsers()->where('ledger_id', $ledgerId)->where('is_active', true);
        
        if ($accessType) {
            $query->where('access_type', $accessType);
        }
        
        return $query->exists();
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_id) {
            return asset('storage/media/' . $this->avatar->file_name);
        }

        return $this->getGravatarUrl();
    }

    /**
     * Get the Gravatar URL for the model's email.
     */
    public function getGravatarUrl(int $size = 80): string
    {
        return "https://ui-avatars.com/api/{$this->full_name}/{$size}/635bff/fff/2";
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getFilamentName(): string
    {
        return $this->full_name;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            if ($this->isSuperAdmin()) {
                return true;
            }

            $panelPermissions = [
                'form.view',
                'form.create',
                'form.report.view',
                'inspection.view',
                'quality_control.view',
                'production.view',
            ];

            return $this->hasAnyPermission($panelPermissions);
        }

        return true;
    }
}
