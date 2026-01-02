<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // Se o parâmetro 'matrix' estiver presente, retornar a matriz
        if ($request->has('matrix') && $request->matrix === 'true') {
            return $this->matrix($request);
        }

        $query = DB::table('permissions');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        $permissions = $query->orderBy('name')->paginate(10);

        // Get roles count for each permission
        foreach ($permissions->items() as $permission) {
            $permission->roles_count = DB::table('role_permissions')
                ->where('permission_id', $permission->id)
                ->count();

            $permission->users_count = DB::table('user_permissions')
                ->where('permission_id', $permission->id)
                ->count();
        }

        return Inertia::render('permissions/index', [
            'permissions' => $permissions,
            'filters' => [
                'search' => $request->search ?? '',
            ],
        ]);
    }

    /**
     * Display permissions matrix.
     */
    public function matrix(Request $request): Response
    {
        $viewType = $request->get('view', 'roles'); // 'roles' ou 'users'
        $search = $request->get('search', '');

        // Buscar todas as permissões
        $permissionsQuery = DB::table('permissions');
        if ($search) {
            $permissionsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        $permissions = $permissionsQuery->orderBy('name')->get();

        // Buscar todas as roles
        $roles = DB::table('roles')->orderBy('name')->get();

        // Buscar usuários ativos (limitado para performance)
        $usersQuery = DB::table('users')->where('status', 1);
        if ($search) {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $users = $usersQuery->orderBy('name')->limit(100)->get();

        // Buscar permissões por role (formato: role_id => [permission_ids])
        $rolePermissionsData = DB::table('role_permissions')->get();
        $rolePermissions = [];
        foreach ($rolePermissionsData as $rp) {
            if (!isset($rolePermissions[$rp->role_id])) {
                $rolePermissions[$rp->role_id] = [];
            }
            $rolePermissions[$rp->role_id][] = $rp->permission_id;
        }

        // Buscar permissões por usuário (formato: user_id => [permission_ids])
        $userPermissionsData = DB::table('user_permissions')->get();
        $userPermissions = [];
        foreach ($userPermissionsData as $up) {
            if (!isset($userPermissions[$up->user_id])) {
                $userPermissions[$up->user_id] = [];
            }
            $userPermissions[$up->user_id][] = $up->permission_id;
        }

        // Organizar permissões por contexto (agrupar por prefixo)
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->slug);
            $context = count($parts) > 1 ? $parts[0] : 'outros';
            
            if (!isset($groupedPermissions[$context])) {
                $groupedPermissions[$context] = [];
            }
            
            $groupedPermissions[$context][] = $permission;
        }

        return Inertia::render('permissions/matrix', [
            'permissions' => $permissions,
            'roles' => $roles,
            'users' => $users,
            'groupedPermissions' => $groupedPermissions,
            'rolePermissions' => $rolePermissions,
            'userPermissions' => $userPermissions,
            'viewType' => $viewType,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string|int $id): Response
    {
        $id = (int) $id;
        $permission = DB::table('permissions')->where('id', $id)->first();

        if (!$permission) {
            abort(404);
        }

        // Get roles with this permission
        $roles = DB::table('role_permissions')
            ->join('roles', 'role_permissions.role_id', '=', 'roles.id')
            ->where('role_permissions.permission_id', $id)
            ->select('roles.*')
            ->get()
            ->toArray();

        // Get users with direct permission
        $users = DB::table('user_permissions')
            ->join('users', 'user_permissions.user_id', '=', 'users.id')
            ->where('user_permissions.permission_id', $id)
            ->select('users.id', 'users.name', 'users.email', 'users.status')
            ->get()
            ->toArray();

        return Inertia::render('permissions/show', [
            'permission' => $permission,
            'roles' => $roles,
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('permissions/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:permissions,slug'],
            'description' => ['nullable', 'string'],
        ]);

        DB::table('permissions')->insert([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permissão criada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string|int $id): Response
    {
        $id = (int) $id;
        $permission = DB::table('permissions')->where('id', $id)->first();

        if (!$permission) {
            abort(404);
        }

        return Inertia::render('permissions/edit', [
            'permission' => $permission,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string|int $id): RedirectResponse
    {
        $id = (int) $id;
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:permissions,slug,' . $id],
            'description' => ['nullable', 'string'],
        ]);

        DB::table('permissions')
            ->where('id', $id)
            ->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
                'updated_at' => now(),
            ]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permissão atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string|int $id): RedirectResponse
    {
        $id = (int) $id;
        // Delete role permissions first
        DB::table('role_permissions')->where('permission_id', $id)->delete();

        // Delete user permissions
        DB::table('user_permissions')->where('permission_id', $id)->delete();

        // Delete permission
        DB::table('permissions')->where('id', $id)->delete();

        return redirect()->route('permissions.index')
            ->with('success', 'Permissão excluída com sucesso!');
    }

    /**
     * Update permissions for a role.
     */
    public function updateRolePermissions(Request $request, int $roleId): RedirectResponse
    {
        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        // Remover todas as permissões atuais da role
        DB::table('role_permissions')->where('role_id', $roleId)->delete();

        // Adicionar novas permissões
        if (!empty($validated['permissions'])) {
            $insertData = array_map(function ($permissionId) use ($roleId) {
                return [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $validated['permissions']);

            DB::table('role_permissions')->insert($insertData);
        }

        return redirect()->back()
            ->with('success', 'Permissões da role atualizadas com sucesso!');
    }

    /**
     * Update permissions for a user.
     */
    public function updateUserPermissions(Request $request, int $userId): RedirectResponse
    {
        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        // Remover todas as permissões diretas do usuário
        DB::table('user_permissions')->where('user_id', $userId)->delete();

        // Adicionar novas permissões diretas
        if (!empty($validated['permissions'])) {
            $insertData = array_map(function ($permissionId) use ($userId) {
                return [
                    'user_id' => $userId,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $validated['permissions']);

            DB::table('user_permissions')->insert($insertData);
        }

        return redirect()->back()
            ->with('success', 'Permissões do usuário atualizadas com sucesso!');
    }

    /**
     * Toggle a single permission for a role.
     */
    public function toggleRolePermission(Request $request, int $roleId, int $permissionId): \Illuminate\Http\JsonResponse
    {
        $exists = DB::table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->exists();

        if ($exists) {
            DB::table('role_permissions')
                ->where('role_id', $roleId)
                ->where('permission_id', $permissionId)
                ->delete();
            
            return response()->json(['granted' => false]);
        } else {
            DB::table('role_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return response()->json(['granted' => true]);
        }
    }

    /**
     * Toggle a single permission for a user.
     */
    public function toggleUserPermission(Request $request, int $userId, int $permissionId): \Illuminate\Http\JsonResponse
    {
        $exists = DB::table('user_permissions')
            ->where('user_id', $userId)
            ->where('permission_id', $permissionId)
            ->exists();

        if ($exists) {
            DB::table('user_permissions')
                ->where('user_id', $userId)
                ->where('permission_id', $permissionId)
                ->delete();
            
            return response()->json(['granted' => false]);
        } else {
            DB::table('user_permissions')->insert([
                'user_id' => $userId,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return response()->json(['granted' => true]);
        }
    }
}

