<?php

namespace App\Traits;

use Illuminate\Support\Carbon;

trait AccountSecurity
{
    /**
     * Verifica se a conta está bloqueada.
     */
    public function isAccountLocked(): bool
    {
        if (!$this->account_locked_until) {
            return false;
        }

        if (Carbon::now()->isAfter($this->account_locked_until)) {
            // Desbloquear automaticamente se o tempo expirou
            $this->unlockAccount();
            return false;
        }

        return true;
    }

    /**
     * Bloqueia a conta por um período específico.
     */
    public function lockAccount(int $minutes = 30): void
    {
        $this->update([
            'account_locked_until' => Carbon::now()->addMinutes($minutes),
            'failed_login_attempts' => 0, // Reset após bloquear
        ]);
    }

    /**
     * Desbloqueia a conta.
     */
    public function unlockAccount(): void
    {
        $this->update([
            'account_locked_until' => null,
            'failed_login_attempts' => 0,
        ]);
    }

    /**
     * Incrementa tentativas de login falhadas.
     */
    public function incrementFailedLoginAttempts(): void
    {
        $attempts = ($this->failed_login_attempts ?? 0) + 1;
        
        $this->update([
            'failed_login_attempts' => $attempts,
        ]);

        // Bloquear após 5 tentativas falhadas
        if ($attempts >= 5) {
            $this->lockAccount(30); // Bloqueia por 30 minutos
        }
    }

    /**
     * Reseta tentativas de login falhadas.
     */
    public function resetFailedLoginAttempts(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
        ]);
    }

    /**
     * Registra um login bem-sucedido.
     */
    public function recordSuccessfulLogin(string $ipAddress): void
    {
        $this->update([
            'last_login_at' => Carbon::now(),
            'last_login_ip' => $ipAddress,
            'failed_login_attempts' => 0,
            'account_locked_until' => null,
        ]);
    }
}
