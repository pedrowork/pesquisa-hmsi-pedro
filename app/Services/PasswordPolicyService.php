<?php

namespace App\Services;

use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordPolicyService
{
    /**
     * Verifica se a senha está no histórico.
     */
    public function isPasswordInHistory(User $user, string $password, int $historyCount = 5): bool
    {
        $history = PasswordHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($historyCount)
            ->pluck('password_hash');

        foreach ($history as $hash) {
            if (Hash::check($password, $hash)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adiciona senha ao histórico.
     */
    public function addToHistory(User $user, string $password): void
    {
        $historyLimit = config('security.password_history_limit', 10);

        // Limitar histórico
        $count = PasswordHistory::where('user_id', $user->id)->count();
        if ($count >= $historyLimit) {
            $oldest = PasswordHistory::where('user_id', $user->id)
                ->orderBy('created_at', 'asc')
                ->first();
            if ($oldest) {
                $oldest->delete();
            }
        }

        PasswordHistory::create([
            'user_id' => $user->id,
            'password_hash' => Hash::make($password),
            'created_at' => now(),
        ]);
    }

    /**
     * Verifica se a senha expirou.
     */
    public function isPasswordExpired(User $user): bool
    {
        if (!$user->password_expires_at) {
            return false;
        }

        return now()->isAfter($user->password_expires_at);
    }

    /**
     * Calcula data de expiração da senha.
     */
    public function calculateExpirationDate(User $user): ?\Carbon\Carbon
    {
        $expiresInDays = $user->password_expires_in_days ?? config('security.password_expires_in_days');

        if (!$expiresInDays) {
            return null;
        }

        return now()->addDays($expiresInDays);
    }

    /**
     * Atualiza política de senha após mudança.
     */
    public function updatePasswordPolicy(User $user, string $newPassword): void
    {
        // Adicionar ao histórico
        $this->addToHistory($user, $newPassword);

        // Atualizar data de expiração
        $expiresAt = $this->calculateExpirationDate($user);
        
        $user->update([
            'password_changed_at' => now(),
            'password_expires_at' => $expiresAt,
            'password_change_required' => false,
        ]);
    }

    /**
     * Verifica se o usuário precisa trocar a senha.
     */
    public function requiresPasswordChange(User $user): bool
    {
        return $user->password_change_required || $this->isPasswordExpired($user);
    }
}

