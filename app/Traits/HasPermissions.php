<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasPermissions
{
    /**
     * Verifica se o usuário tem uma permissão específica.
     * Admin sempre retorna true.
     */
    public function hasPermission(string $permission): bool
    {
        // Admin tem acesso total
        if ($this->isAdmin()) {
            return true;
        }

        // Verificar permissão direta do usuário
        $hasDirectPermission = DB::table('user_permissions')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.user_id', $this->id)
            ->where('permissions.slug', $permission)
            ->exists();

        if ($hasDirectPermission) {
            return true;
        }

        // Verificar permissão via roles
        $hasRolePermission = DB::table('user_roles')
            ->join('role_permissions', 'user_roles.role_id', '=', 'role_permissions.role_id')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('user_roles.user_id', $this->id)
            ->where('permissions.slug', $permission)
            ->exists();

        return $hasRolePermission;
    }

    /**
     * Verifica se o usuário tem uma role específica.
     */
    public function hasRole(string $role): bool
    {
        // Verificar se é admin
        if ($role === 'admin' && $this->isAdmin()) {
            return true;
        }

        return DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $this->id)
            ->where(function ($query) use ($role) {
                $query->where('roles.slug', $role)
                    ->orWhere('roles.name', $role);
            })
            ->exists();
    }

    /**
     * Verifica se o usuário é admin.
     */
    public function isAdmin(): bool
    {
        return DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $this->id)
            ->where('roles.slug', 'admin')
            ->exists();
    }

    /**
     * Retorna todas as permissões do usuário (diretas + via roles).
     */
    public function getUserPermissions(): array
    {
        // Se for admin, retornar todas as permissões
        if ($this->isAdmin()) {
            return DB::table('permissions')
                ->pluck('slug')
                ->toArray();
        }

        // Permissões diretas
        $directPermissions = DB::table('user_permissions')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.user_id', $this->id)
            ->pluck('permissions.slug')
            ->toArray();

        // Permissões via roles
        $rolePermissions = DB::table('user_roles')
            ->join('role_permissions', 'user_roles.role_id', '=', 'role_permissions.role_id')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('user_roles.user_id', $this->id)
            ->pluck('permissions.slug')
            ->toArray();

        // Combinar e remover duplicatas
        return array_unique(array_merge($directPermissions, $rolePermissions));
    }

    /**
     * Retorna todas as roles do usuário.
     */
    public function getUserRoles(): array
    {
        return DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $this->id)
            ->pluck('roles.slug')
            ->toArray();
    }
}


