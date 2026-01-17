<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class UserApprovalController extends Controller
{
    /**
     * Obtém o ID do usuário admin (com role 'admin').
     */
    protected function getAdminUserId(): ?int
    {
        $adminRole = DB::table('roles')->where('slug', 'admin')->first();
        
        if (!$adminRole) {
            return null;
        }

        $adminUser = DB::table('user_roles')
            ->where('role_id', $adminRole->id)
            ->first();

        return $adminUser ? (int) $adminUser->user_id : null;
    }

    /**
     * Lista usuários pendentes de aprovação.
     */
    public function index(Request $request): Response
    {
        $query = User::where('approval_status', 'pending')
            ->orderBy('created_at', 'desc');

        // Excluir admin da listagem - ninguém pode ver o admin
        $adminId = $this->getAdminUserId();
        if ($adminId) {
            $query->where('id', '!=', $adminId);
        }

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
        // Proteger admin - não pode ser aprovado/rejeitado via este controller
        $adminId = $this->getAdminUserId();
        if ($adminId && $user->id === $adminId) {
            abort(403, 'Acesso negado: operações no usuário admin não são permitidas.');
        }

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

        // Adicionar permissão de criar questionário automaticamente
        $questionarioCreatePermission = \Illuminate\Support\Facades\DB::table('permissions')
            ->where('slug', 'questionarios.create')
            ->first();
        
        if ($questionarioCreatePermission) {
            \Illuminate\Support\Facades\DB::table('user_permissions')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'permission_id' => $questionarioCreatePermission->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

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
        // Proteger admin - não pode ser aprovado/rejeitado via este controller
        $adminId = $this->getAdminUserId();
        if ($adminId && $user->id === $adminId) {
            abort(403, 'Acesso negado: operações no usuário admin não são permitidas.');
        }

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

