<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemUpdateLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'current_version',
        'target_version',
        'triggered_by',
        'log',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function appendLog(string $message): void
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $existing = $this->log ? rtrim($this->log) . "\n" : '';
        $this->log = $existing . "[{$timestamp}] {$message}";
        $this->save();
    }
}
