<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Obtém o ID do usuário admin (com role 'admin').
     * Retorna null se não encontrar.
     * Usa cache permanente para evitar queries repetidas.
     */
    protected function getAdminUserId(): ?int
    {
        return Cache::rememberForever('system.admin_user_id', function () {
            $adminRole = DB::table('roles')->where('slug', 'admin')->first();

            if (!$adminRole) {
                return null;
            }

            $adminUser = DB::table('user_roles')
                ->where('role_id', $adminRole->id)
                ->first();

            return $adminUser ? (int) $adminUser->user_id : null;
        });
    }

    /**
     * Obtém o ID do primeiro Master criado (protegido).
     * Retorna null se não encontrar.
     * Usa cache permanente para evitar queries repetidas.
     */
    protected function getFirstMasterUserId(): ?int
    {
        return Cache::rememberForever('system.first_master_user_id', function () {
            $masterRole = DB::table('roles')->where('slug', 'master')->first();

            if (!$masterRole) {
                return null;
            }

            // Busca o primeiro Master criado (por ordem de criação)
            $firstMaster = DB::table('user_roles')
                ->join('users', 'user_roles.user_id', '=', 'users.id')
                ->where('user_roles.role_id', $masterRole->id)
                ->orderBy('users.created_at', 'asc')
                ->orderBy('users.id', 'asc')
                ->select('users.id')
                ->first();

            return $firstMaster ? (int) $firstMaster->id : null;
        });
    }

    /**
     * Verifica se um usuário é admin e lança exceção se for.
     */
    protected function preventAdminAccess(User $user): void
    {
        $adminId = $this->getAdminUserId();

        if ($adminId && $user->id === $adminId) {
            abort(403, 'Acesso negado: operações no usuário admin não são permitidas.');
        }
    }

    /**
     * Verifica se um usuário é o primeiro Master e lança exceção se for.
     * Apenas o próprio Master pode modificar a si mesmo.
     */
    protected function preventFirstMasterAccess(User $user, Request $request): void
    {
        $firstMasterId = $this->getFirstMasterUserId();
        $currentUser = $request->user();

        if ($firstMasterId && $user->id === $firstMasterId) {
            // Apenas o próprio Master pode modificar a si mesmo
            if (!$currentUser || $currentUser->id !== $firstMasterId) {
                abort(403, 'Acesso negado: o primeiro Master não pode ser alterado ou removido por outros usuários.');
            }
        }
    }

    /**
     * Exclui o admin da query base.
     */
    protected function excludeAdminFromQuery($query)
    {
        $adminId = $this->getAdminUserId();

        if ($adminId) {
            $query->where('users.id', '!=', $adminId);
        }

        return $query;
    }

    /**
     * Exclui o primeiro Master da query base.
     */
    protected function excludeFirstMasterFromQuery($query)
    {
        $firstMasterId = $this->getFirstMasterUserId();

        if ($firstMasterId) {
            $query->where('users.id', '!=', $firstMasterId);
        }

        return $query;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = User::query();

        $currentUser = $request->user();
        $isAdmin = $currentUser && $currentUser->isAdmin();

        // Apenas admin pode ver TODOS os usuários (incluindo admin e primeiro Master)
        // Outros usuários não veem admin e primeiro Master
        if (!$isAdmin) {
            // Excluir admin da listagem - ninguém pode ver o admin (exceto admin)
            $this->excludeAdminFromQuery($query);

            // Excluir primeiro Master da listagem - apenas ele ou admin pode ver
            $firstMasterId = $this->getFirstMasterUserId();
            if ($firstMasterId && (!$currentUser || $currentUser->id !== $firstMasterId)) {
                $this->excludeFirstMasterFromQuery($query);
            }
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Eager loading de roles para evitar queries N+1
        $users = $query->with('roles')->orderBy('created_at', 'desc')->paginate(10);

        // Transformar roles para o formato esperado pelo frontend
        $users->getCollection()->transform(function ($user) {
            $user->roles = $user->roles->map(function ($role) {
                return [
                    'name' => $role->name,
                    'slug' => $role->slug,
                ];
            })->toArray();
            return $user;
        });

        $firstMasterId = $this->getFirstMasterUserId();
        $currentUser = $request->user();
        $canModifyFirstMaster = $firstMasterId && $currentUser && $currentUser->id === $firstMasterId;

        return Inertia::render('app/users/index', [
            'users' => $users,
            'filters' => [
                'search' => $request->search ?? '',
                'status' => $request->status ?? '',
            ],
            'firstMasterId' => $firstMasterId,
            'currentUserId' => $currentUser?->id,
            'canModifyFirstMaster' => $canModifyFirstMaster,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $roles = DB::table('roles')
            ->orderBy('name')
            ->get()
            ->toArray();

        $currentUser = auth()->user();
        $isAdmin = $currentUser && $currentUser->isAdmin();

        return Inertia::render('app/users/create', [
            'roles' => $roles,
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'status' => ['required', 'integer', 'in:0,1'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
            'department' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        // Verificar se o usuário atual é admin
        $currentUser = $request->user();
        $isCurrentUserAdmin = $currentUser && $currentUser->isAdmin();

        // Verificar se está tentando atribuir role de admin
        if (!empty($validated['roles'])) {
            $adminRole = DB::table('roles')->where('slug', 'admin')->first();

            if ($adminRole && in_array($adminRole->id, $validated['roles'])) {
                // Se não for admin, não pode criar usuário com role admin
                if (!$isCurrentUserAdmin) {
                    return back()->withErrors([
                        'roles' => 'Você não tem permissão para criar usuários com perfil de Administrador. Apenas administradores podem criar outros administradores.',
                    ])->withInput();
                }
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
            'department' => $validated['department'] ?? null,
            'position' => $validated['position'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'approval_status' => config('security.user_approval_required', true) ? 'pending' : 'approved',
        ]);

        // Registrar criação (o observer também registrará, mas aqui temos roles)
        app(\App\Services\AuditService::class)->logUserCreated(
            $user,
            $user->getAttributes(),
            "Usuário criado via UserController"
        );

        // Attach roles
        if (!empty($validated['roles'])) {
            foreach ($validated['roles'] as $roleId) {
                DB::table('user_roles')->insert([
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // As permissões básicas (dashboard.view, questionarios.create) são adicionadas
        // automaticamente pelo UserObserver::created()
        // Limpar cache de permissões do usuário após adicionar roles
        $user->clearPermissionsCache();

        return redirect()->route('users.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user, Request $request): Response
    {
        // Proteger admin - ninguém pode ver o admin
        $this->preventAdminAccess($user);

        // Proteger primeiro Master - apenas ele pode ver a si mesmo
        $this->preventFirstMasterAccess($user, $request);

        // Get user roles
        $userRoles = DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $user->id)
            ->select('roles.*')
            ->get()
            ->toArray();

        // Get user direct permissions
        $userPermissions = DB::table('user_permissions')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.user_id', $user->id)
            ->select('permissions.*')
            ->get()
            ->toArray();

        // Get all permissions from roles
        $rolePermissions = DB::table('user_roles')
            ->join('role_permissions', 'user_roles.role_id', '=', 'role_permissions.role_id')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('user_roles.user_id', $user->id)
            ->select('permissions.*')
            ->distinct()
            ->get()
            ->toArray();

        return Inertia::render('app/users/show', [
            'user' => $user,
            'roles' => $userRoles,
            'directPermissions' => $userPermissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user, Request $request): Response
    {
        // Proteger admin - ninguém pode editar o admin
        $this->preventAdminAccess($user);

        // Proteger primeiro Master - apenas ele pode editar a si mesmo
        $this->preventFirstMasterAccess($user, $request);

        // Buscar todas as permissões organizadas por categoria
        $allPermissions = DB::table('permissions')
            ->orderBy('name')
            ->get()
            ->groupBy(function ($permission) {
                // Organizar por categoria baseado no slug
                if (str_starts_with($permission->slug, 'dashboard.')) {
                    return 'Dashboard';
                } elseif (str_starts_with($permission->slug, 'users.') ||
                          str_starts_with($permission->slug, 'roles.') ||
                          str_starts_with($permission->slug, 'permissions.')) {
                    return 'Gerenciamento';
                } else {
                    return 'Pesquisa';
                }
            })
            ->map(function ($group) {
                return $group->values()->toArray();
            })
            ->toArray();

        // Buscar permissões diretas do usuário
        $userPermissions = DB::table('user_permissions')
            ->where('user_id', $user->id)
            ->pluck('permission_id')
            ->toArray();

        // Buscar todas as roles
        $allRoles = DB::table('roles')
            ->orderBy('name')
            ->get()
            ->toArray();

        // Buscar roles do usuário
        $userRoles = DB::table('user_roles')
            ->where('user_id', $user->id)
            ->pluck('role_id')
            ->toArray();

        $currentUser = $request->user();
        $isAdmin = $currentUser && $currentUser->isAdmin();

        $firstMasterId = $this->getFirstMasterUserId();
        $adminId = $this->getAdminUserId();
        $isCurrentUserFirstMaster = $currentUser && $firstMasterId && $currentUser->id === $firstMasterId;
        $isEditingSelf = $currentUser && $user->id === $currentUser->id;

        // Verificar se pode alterar permissões deste usuário
        $canModifyPermissions = true;
        // Prevenir auto-elevação: usuários não podem alterar suas próprias permissões (exceto primeiro Master)
        if ($isEditingSelf && !$isCurrentUserFirstMaster) {
            $canModifyPermissions = false;
        } elseif ($user->id === $firstMasterId && !$isCurrentUserFirstMaster) {
            $canModifyPermissions = false;
        } elseif ($adminId && $user->id === $adminId && !$isCurrentUserFirstMaster) {
            $canModifyPermissions = false;
        }

        // Verificar se pode alterar roles deste usuário
        $canModifyRoles = true;
        // Prevenir auto-elevação: usuários não podem alterar suas próprias roles (exceto primeiro Master)
        if ($isEditingSelf && !$isCurrentUserFirstMaster) {
            $canModifyRoles = false;
        } elseif ($user->id === $firstMasterId && !$isCurrentUserFirstMaster) {
            // Ninguém pode alterar roles do primeiro Master exceto ele mesmo
            $canModifyRoles = false;
        } elseif ($adminId && $user->id === $adminId && !$isCurrentUserFirstMaster) {
            // Apenas o primeiro Master pode alterar roles do Admin
            $canModifyRoles = false;
        }

        return Inertia::render('app/users/edit', [
            'user' => $user,
            'permissions' => $allPermissions,
            'userPermissions' => $userPermissions,
            'roles' => $allRoles,
            'userRoles' => $userRoles,
            'isAdmin' => $isAdmin,
            'canModifyPermissions' => $canModifyPermissions,
            'canModifyRoles' => $canModifyRoles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        // Proteger admin - ninguém pode atualizar o admin
        $this->preventAdminAccess($user);

        // Proteger primeiro Master - apenas ele pode atualizar a si mesmo
        $this->preventFirstMasterAccess($user, $request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'status' => ['required', 'integer', 'in:0,1'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
            'department' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->status = $validated['status'];
        $user->department = $validated['department'] ?? null;
        $user->position = $validated['position'] ?? null;
        $user->phone = $validated['phone'] ?? null;
        $user->bio = $validated['bio'] ?? null;

        if (!empty($validated['password'])) {
            // Verificar histórico de senhas
            $passwordPolicy = app(\App\Services\PasswordPolicyService::class);
            if ($passwordPolicy->isPasswordInHistory($user, $validated['password'])) {
                return back()->withErrors([
                    'password' => 'Esta senha foi usada recentemente. Escolha uma senha diferente.',
                ]);
            }

            $user->password = Hash::make($validated['password']);
            $passwordPolicy->updatePasswordPolicy($user, $validated['password']);
        }

        $oldValues = $user->getOriginal();
        $user->save();
        $newValues = $user->getChanges();

        // Registrar no audit log (o observer também registrará, mas aqui temos mais contexto)
        if (!empty($newValues)) {
            app(\App\Services\AuditService::class)->logUserUpdated(
                $user,
                $oldValues,
                $newValues,
                "Usuário atualizado via UserController"
            );
        }

        // Proteger permissões do primeiro Master e primeiro Admin
        $firstMasterId = $this->getFirstMasterUserId();
        $adminId = $this->getAdminUserId();
        $currentUser = $request->user();
        $isCurrentUserFirstMaster = $currentUser && $firstMasterId && $currentUser->id === $firstMasterId;

        // Prevenir auto-elevação de privilégios: usuários não podem alterar suas próprias permissões/roles
        // Exceção: primeiro Master pode alterar suas próprias permissões/roles
        if ($currentUser && $user->id === $currentUser->id && !$isCurrentUserFirstMaster) {
            return back()->withErrors([
                'permissions' => 'Você não pode alterar suas próprias permissões ou roles. Isso previne elevação de privilégios não autorizada.',
            ])->withInput();
        }

        // Verificar se está tentando alterar permissões de usuários protegidos
        if ($user->id === $firstMasterId && !$isCurrentUserFirstMaster) {
            // Ninguém pode alterar permissões do primeiro Master exceto ele mesmo
            return back()->withErrors([
                'permissions' => 'Você não tem permissão para alterar as permissões do primeiro Master.',
            ])->withInput();
        }

        if ($adminId && $user->id === $adminId && !$isCurrentUserFirstMaster) {
            // Apenas o primeiro Master pode alterar permissões do Admin
            return back()->withErrors([
                'permissions' => 'Apenas o primeiro Master pode alterar as permissões do Administrador.',
            ])->withInput();
        }

        // Sincronizar permissões diretas
        DB::table('user_permissions')->where('user_id', $user->id)->delete();
        if (!empty($validated['permissions'])) {
            foreach ($validated['permissions'] as $permissionId) {
                DB::table('user_permissions')->insert([
                    'user_id' => $user->id,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Verificar se o usuário atual é admin
        $currentUser = $request->user();
        $isCurrentUserAdmin = $currentUser && $currentUser->isAdmin();
        $isCurrentUserFirstMaster = $currentUser && $firstMasterId && $currentUser->id === $firstMasterId;

        // Proteger roles do primeiro Master e primeiro Admin
        // Ninguém pode alterar roles do primeiro Master exceto ele mesmo
        if ($user->id === $firstMasterId && !$isCurrentUserFirstMaster) {
            return back()->withErrors([
                'roles' => 'Você não tem permissão para alterar as roles do primeiro Master.',
            ])->withInput();
        }

        // Apenas o primeiro Master pode alterar roles do Admin
        if ($adminId && $user->id === $adminId && !$isCurrentUserFirstMaster) {
            return back()->withErrors([
                'roles' => 'Apenas o primeiro Master pode alterar as roles do Administrador.',
            ])->withInput();
        }

        // Sincronizar roles
        DB::table('user_roles')->where('user_id', $user->id)->delete();
        if (!empty($validated['roles'])) {
            // Verificar se está tentando atribuir role de admin
            $adminRole = DB::table('roles')->where('slug', 'admin')->first();

            if ($adminRole && in_array($adminRole->id, $validated['roles'])) {
                // Se não for admin, não pode atribuir role admin
                if (!$isCurrentUserAdmin) {
                    return back()->withErrors([
                        'roles' => 'Você não tem permissão para atribuir perfil de Administrador. Apenas administradores podem atribuir este perfil.',
                    ])->withInput();
                }
            }

            foreach ($validated['roles'] as $roleId) {
                DB::table('user_roles')->insert([
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Limpar cache de permissões do usuário após atualizar permissões/roles
        $user->clearPermissionsCache();

        return redirect()->route('users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, Request $request): RedirectResponse
    {
        // Proteger admin - ninguém pode deletar o admin
        $this->preventAdminAccess($user);

        // Proteger primeiro Master - ninguém pode deletar o primeiro Master (nem ele mesmo)
        $firstMasterId = $this->getFirstMasterUserId();
        if ($firstMasterId && $user->id === $firstMasterId) {
            abort(403, 'Acesso negado: o primeiro Master não pode ser removido.');
        }

        // Verificar se o usuário possui questionários associados
        $questionariosCount = DB::table('questionario')
            ->where('cod_usuario', $user->id)
            ->count();

        if ($questionariosCount > 0) {
            return back()->withErrors([
                'user' => "Este usuário não pode ser excluído porque possui {$questionariosCount} questionário(s) associado(s). Para manter o histórico, desative o usuário ao invés de excluí-lo.",
            ])->withInput();
        }

        $deletedData = $user->toArray();

        // Registrar antes de deletar (o observer também registrará)
        app(\App\Services\AuditService::class)->logUserDeleted(
            $user,
            $deletedData,
            "Usuário excluído via UserController"
        );

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuário excluído com sucesso!');
    }

    /**
     * Toggle user status (activate/deactivate)
     * Only admins can toggle user status
     */
    public function toggleStatus(User $user, Request $request): RedirectResponse
    {
        // Apenas admin pode alterar status de usuários
        $currentUser = $request->user();
        if (!$currentUser || !$currentUser->isAdmin()) {
            abort(403, 'Acesso negado: apenas administradores podem alterar o status de usuários.');
        }

        // Proteger admin - não pode ser desativado (incluindo o próprio admin)
        $this->preventAdminAccess($user);

        // Proteger o próprio admin - não pode desativar a si mesmo
        $adminId = $this->getAdminUserId();
        if ($adminId && $currentUser->id === $user->id && $user->id === $adminId) {
            abort(403, 'Acesso negado: você não pode desativar sua própria conta de administrador.');
        }

        // O admin pode desativar qualquer Master (exceto ele mesmo, que já está protegido acima)

        // Alternar status
        $newStatus = $user->status === 1 ? 0 : 1;
        $user->update(['status' => $newStatus]);

        $statusMessage = $newStatus === 1 ? 'ativado' : 'desativado';

        return redirect()->route('users.index')
            ->with('success', "Usuário {$statusMessage} com sucesso!");
    }
}

