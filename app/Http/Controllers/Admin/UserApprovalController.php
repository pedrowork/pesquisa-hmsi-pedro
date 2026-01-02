<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class UserApprovalController extends Controller
{
    /**
     * Lista usuários pendentes de aprovação.
     */
    public function index(Request $request): Response
    {
        $query = User::where('approval_status', 'pending')
            ->orderBy('created_at', 'desc');

        $users = $query->paginate(20);

        return Inertia::render('admin/users/pending-approval', [
            'users' => $users,
        ]);
    }

    /**
     * Aprova um usuário.
     */
    public function approve(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'approval_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($user->approval_status !== 'pending') {
            return back()->withErrors(['message' => 'Este usuário já foi processado.']);
        }

        $user->update([
            'approval_status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'approval_notes' => $validated['approval_notes'] ?? null,
            'status' => 1, // Ativar o usuário
        ]);

        // Registrar no audit log
        app(\App\Services\AuditService::class)->log(
            'user_approved',
            'user_management',
            "Usuário aprovado: {$user->email}",
            $user,
            ['approval_status' => 'pending'],
            ['approval_status' => 'approved', 'status' => 1],
            null,
            'info',
            false
        );

        // Enviar email de notificação (opcional)
        // Mail::to($user->email)->send(new UserApprovedMail($user));

        return back()->with('success', 'Usuário aprovado com sucesso!');
    }

    /**
     * Rejeita um usuário.
     */
    public function reject(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'approval_notes' => ['required', 'string', 'max:1000'],
        ]);

        if ($user->approval_status !== 'pending') {
            return back()->withErrors(['message' => 'Este usuário já foi processado.']);
        }

        $user->update([
            'approval_status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'approval_notes' => $validated['approval_notes'],
            'status' => 0, // Desativar o usuário
        ]);

        // Registrar no audit log
        app(\App\Services\AuditService::class)->log(
            'user_rejected',
            'user_management',
            "Usuário rejeitado: {$user->email}",
            $user,
            ['approval_status' => 'pending'],
            ['approval_status' => 'rejected', 'status' => 0],
            null,
            'warning',
            false
        );

        // Enviar email de notificação (opcional)
        // Mail::to($user->email)->send(new UserRejectedMail($user, $validated['approval_notes']));

        return back()->with('success', 'Usuário rejeitado.');
    }
}

