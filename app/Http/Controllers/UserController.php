<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = User::query();

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

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return Inertia::render('users/index', [
            'users' => $users,
            'filters' => [
                'search' => $request->search ?? '',
                'status' => $request->status ?? '',
            ],
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

        return Inertia::render('users/create', [
            'roles' => $roles,
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

        return redirect()->route('users.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): Response
    {
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

        return Inertia::render('users/show', [
            'user' => $user,
            'roles' => $userRoles,
            'directPermissions' => $userPermissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): Response
    {
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

        return Inertia::render('users/edit', [
            'user' => $user,
            'permissions' => $allPermissions,
            'userPermissions' => $userPermissions,
            'roles' => $allRoles,
            'userRoles' => $userRoles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
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

        // Sincronizar roles
        DB::table('user_roles')->where('user_id', $user->id)->delete();
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

        return redirect()->route('users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
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
}

