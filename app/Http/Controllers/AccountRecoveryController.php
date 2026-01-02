<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AccountRecoveryController extends Controller
{
    /**
     * Exibe formulário de recuperação de conta.
     */
    public function show(): Response
    {
        return Inertia::render('auth/account-recovery');
    }

    /**
     * Processa recuperação de conta via pergunta secreta.
     */
    public function recover(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'security_answer' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Usuário não encontrado.']);
        }

        if (!$user->security_question || !$user->security_answer) {
            return back()->withErrors(['email' => 'Este usuário não possui pergunta secreta configurada.']);
        }

        // Verificar resposta da pergunta secreta
        if (!Hash::check(strtolower(trim($validated['security_answer'])), $user->security_answer)) {
            return back()->withErrors(['security_answer' => 'Resposta incorreta.']);
        }

        // Atualizar senha
        $user->update([
            'password' => $validated['password'],
            'password_changed_at' => now(),
        ]);

        // Registrar no audit log
        app(\App\Services\AuditService::class)->log(
            'account_recovered',
            'user_management',
            "Conta recuperada via pergunta secreta: {$user->email}",
            $user,
            null,
            ['password_changed_at' => now()],
            ['ip_address' => $request->ip()],
            'warning',
            true
        );

        return redirect()->route('login')
            ->with('success', 'Senha redefinida com sucesso! Faça login com sua nova senha.');
    }
}

