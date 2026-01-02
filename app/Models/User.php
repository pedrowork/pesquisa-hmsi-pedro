<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasPermissions;
use App\Traits\AccountSecurity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasPermissions, AccountSecurity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'failed_login_attempts',
        'account_locked_until',
        'last_login_at',
        'last_login_ip',
        'password_changed_at',
        'current_session_id',
        'last_activity',
        'single_session_enabled',
        // Aprovação
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_notes',
        // Metadados
        'department',
        'position',
        'phone',
        'bio',
        'profile_photo_path',
        // Política de senha
        'password_expires_in_days',
        'password_expires_at',
        'password_change_required',
        // Recuperação
        'security_question',
        'security_answer',
        // Inatividade
        'last_activity_at',
        'inactive_days_threshold',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'account_locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'last_activity' => 'datetime',
            'single_session_enabled' => 'boolean',
            'approved_at' => 'datetime',
            'password_expires_at' => 'datetime',
            'password_change_required' => 'boolean',
            'last_activity_at' => 'datetime',
            'security_answer' => 'hashed',
        ];
    }

    /**
     * Relacionamento com tentativas de login.
     */
    public function loginAttempts()
    {
        return $this->hasMany(\App\Models\LoginAttempt::class);
    }

    /**
     * Relacionamento com histórico de senhas.
     */
    public function passwordHistory()
    {
        return $this->hasMany(\App\Models\PasswordHistory::class);
    }

    /**
     * Usuário que aprovou este usuário.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Verifica se o usuário está aprovado.
     */
    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    /**
     * Verifica se o usuário está pendente de aprovação.
     */
    public function isPendingApproval(): bool
    {
        return $this->approval_status === 'pending';
    }

    /**
     * Obtém URL da foto de perfil.
     */
    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (!$this->profile_photo_path) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->profile_photo_path);
    }

    /**
     * Limpar cache de permissões ao fazer refresh.
     */
    public function refresh(): static
    {
        parent::refresh();
        $this->clearPermissionsCache();
        return $this;
    }
}
