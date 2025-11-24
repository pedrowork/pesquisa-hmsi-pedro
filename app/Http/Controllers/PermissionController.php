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
    public function edit(int $id): Response
    {
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
    public function update(Request $request, int $id): RedirectResponse
    {
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
    public function destroy(int $id): RedirectResponse
    {
        // Delete role permissions first
        DB::table('role_permissions')->where('permission_id', $id)->delete();

        // Delete user permissions
        DB::table('user_permissions')->where('permission_id', $id)->delete();

        // Delete permission
        DB::table('permissions')->where('id', $id)->delete();

        return redirect()->route('permissions.index')
            ->with('success', 'Permissão excluída com sucesso!');
    }
}

