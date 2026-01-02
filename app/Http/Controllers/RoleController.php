<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = DB::table('roles');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        $roles = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get permissions count for each role
        foreach ($roles->items() as $role) {
            $role->permissions_count = DB::table('role_permissions')
                ->where('role_id', $role->id)
                ->count();

            $role->users_count = DB::table('user_roles')
                ->where('role_id', $role->id)
                ->count();
        }

        return Inertia::render('roles/index', [
            'roles' => $roles,
            'filters' => [
                'search' => $request->search ?? '',
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $permissions = DB::table('permissions')
            ->orderBy('name')
            ->get()
            ->toArray();

        // Agrupar permissões por contexto (prefixo do slug)
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->slug);
            $context = count($parts) > 1 ? $parts[0] : 'outros';
            
            if (!isset($groupedPermissions[$context])) {
                $groupedPermissions[$context] = [];
            }
            
            $groupedPermissions[$context][] = $permission;
        }

        return Inertia::render('roles/create', [
            'permissions' => $permissions,
            'groupedPermissions' => $groupedPermissions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:roles,slug'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $roleId = DB::table('roles')->insertGetId([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Attach permissions
        if (isset($validated['permissions']) && is_array($validated['permissions']) && count($validated['permissions']) > 0) {
            $permissionsToInsert = [];
            foreach ($validated['permissions'] as $permissionId) {
                $permissionId = (int) $permissionId;
                if ($permissionId > 0) {
                    $permissionsToInsert[] = [
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            if (!empty($permissionsToInsert)) {
                DB::table('role_permissions')->insert($permissionsToInsert);
            }
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): Response
    {
        $role = DB::table('roles')->where('id', $id)->first();

        if (!$role) {
            abort(404);
        }

        // Get role permissions
        $rolePermissions = DB::table('role_permissions')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('role_permissions.role_id', $id)
            ->select('permissions.*')
            ->get()
            ->toArray();

        // Get users with this role
        $users = DB::table('user_roles')
            ->join('users', 'user_roles.user_id', '=', 'users.id')
            ->where('user_roles.role_id', $id)
            ->select('users.id', 'users.name', 'users.email', 'users.status')
            ->get()
            ->toArray();

        return Inertia::render('roles/show', [
            'role' => $role,
            'permissions' => $rolePermissions,
            'users' => $users,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): Response
    {
        $role = DB::table('roles')->where('id', $id)->first();

        if (!$role) {
            abort(404);
        }

        $permissions = DB::table('permissions')
            ->orderBy('name')
            ->get()
            ->toArray();

        $rolePermissions = DB::table('role_permissions')
            ->where('role_id', $id)
            ->pluck('permission_id')
            ->toArray();

        // Agrupar permissões por contexto (prefixo do slug)
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->slug);
            $context = count($parts) > 1 ? $parts[0] : 'outros';
            
            if (!isset($groupedPermissions[$context])) {
                $groupedPermissions[$context] = [];
            }
            
            $groupedPermissions[$context][] = $permission;
        }

        return Inertia::render('roles/edit', [
            'role' => $role,
            'permissions' => $permissions,
            'groupedPermissions' => $groupedPermissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:roles,slug,' . $id],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        DB::table('roles')
            ->where('id', $id)
            ->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
                'updated_at' => now(),
            ]);

        // Sync permissions - sempre deletar e recriar
        DB::table('role_permissions')->where('role_id', $id)->delete();

        // Processar permissões mesmo se for array vazio (para limpar todas)
        $permissions = $validated['permissions'] ?? [];
        
        if (is_array($permissions) && count($permissions) > 0) {
            $permissionsToInsert = [];
            foreach ($permissions as $permissionId) {
                $permissionId = (int) $permissionId;
                if ($permissionId > 0) {
                    $permissionsToInsert[] = [
                        'role_id' => $id,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            if (!empty($permissionsToInsert)) {
                DB::table('role_permissions')->insert($permissionsToInsert);
            }
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role atualizada com sucesso! Os usuários com este role precisam fazer logout e login novamente para que as novas permissões sejam aplicadas.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        // Delete role permissions first
        DB::table('role_permissions')->where('role_id', $id)->delete();

        // Delete user roles
        DB::table('user_roles')->where('role_id', $id)->delete();

        // Delete role
        DB::table('roles')->where('id', $id)->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role excluída com sucesso!');
    }
}

