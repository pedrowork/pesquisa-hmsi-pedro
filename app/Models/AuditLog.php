<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_type',
        'category',
        'severity',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'metadata',
        'description',
        'ip_address',
        'user_agent',
        'is_security_alert',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
            'is_security_alert' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento polimórfico com o modelo relacionado.
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope para filtrar por categoria.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope para filtrar por severidade.
     */
    public function scopeSeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope para filtrar alertas de segurança.
     */
    public function scopeSecurityAlerts($query)
    {
        return $query->where('is_security_alert', true);
    }

    /**
     * Scope para eventos recentes.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
