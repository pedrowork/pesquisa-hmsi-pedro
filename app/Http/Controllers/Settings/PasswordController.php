<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    /**
     * Show the user's password settings page.
     */
    public function edit(): Response
    {
        return Inertia::render('app/settings/password');
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        
        // Verificar se a senha está no histórico
        $passwordPolicy = app(\App\Services\PasswordPolicyService::class);
        if ($passwordPolicy->isPasswordInHistory($user, $validated['password'])) {
            return back()->withErrors([
                'password' => 'Você não pode reutilizar uma senha recente. Escolha uma senha diferente.',
            ]);
        }

        $user->update([
            'password' => $validated['password'],
            'password_changed_at' => now(),
        ]);

        // Atualizar política de senha
        $passwordPolicy->updatePasswordPolicy($user, $validated['password']);

        // Invalidar todas as outras sessões após mudança de senha
        app(\App\Services\SessionSecurityService::class)->invalidateOtherSessions($user);

        // Registrar mudança de senha
        app(\App\Services\AuditService::class)->logPasswordChanged($user);

        return back();
    }
}
