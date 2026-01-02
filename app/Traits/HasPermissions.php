<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

trait HasPermissions
{
    /**
     * Cache TTL em minutos para permissões do usuário.
     */
    protected int $permissionsCacheTtl = 60;

    /**
     * Obtém o timestamp de versão do cache de permissões do usuário.
     * Usado para invalidar todos os caches quando há mudanças.
     */
    protected function getPermissionsCacheVersion(): string
    {
        $versionKey = "user_permissions_version_{$this->id}";
        $version = Cache::get($versionKey);
        
        if (!$version) {
            // Se não existe, criar com timestamp atual
            $version = (string) now()->timestamp;
            Cache::forever($versionKey, $version);
        }
        
        return $version;
    }

    /**
     * Verifica se o usuário tem uma permissão específica.
     * Admin sempre retorna true.
     * Suporta permissões temporárias, negativas (deny) e hierarquia de roles.
     */
    public function hasPermission(string $permission, ?string $context = null): bool
    {
        // Admin tem acesso total
        if ($this->isAdmin()) {
            return true;
        }

        $version = $this->getPermissionsCacheVersion();
        $cacheKey = "user_permissions_{$this->id}_v{$version}_{$permission}" . ($context ? "_{$context}" : '');

        return Cache::remember($cacheKey, now()->addMinutes($this->permissionsCacheTtl), function () use ($permission, $context) {
            // Verificar permissões negativas (deny) primeiro - elas têm prioridade
            $hasDeny = $this->hasDeniedPermission($permission);
            if ($hasDeny) {
                return false;
            }

            // Verificar permissão direta do usuário
            $hasDirectPermission = DB::table('user_permissions')
                ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
                ->where('user_permissions.user_id', $this->id)
                ->where('permissions.slug', $permission)
                ->where(function ($query) {
                    $query->whereNull('user_permissions.expires_at')
                        ->orWhere('user_permissions.expires_at', '>', now());
                })
                ->when($context, function ($query) use ($context) {
                    $query->where(function ($q) use ($context) {
                        $q->whereNull('permissions.context')
                            ->orWhere('permissions.context', $context);
                    });
                })
                ->where('user_permissions.deny', false)
                ->exists();

            if ($hasDirectPermission) {
                return true;
            }

            // Verificar permissão via roles (incluindo hierarquia)
            $hasRolePermission = $this->hasPermissionViaRoles($permission, $context);

            return $hasRolePermission;
        });
    }

    /**
     * Verifica se o usuário tem uma permissão negada (deny).
     */
    protected function hasDeniedPermission(string $permission): bool
    {
        // Verificar deny direto
        $hasDirectDeny = DB::table('user_permissions')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.user_id', $this->id)
            ->where('permissions.slug', $permission)
            ->where('user_permissions.deny', true)
            ->where(function ($query) {
                $query->whereNull('user_permissions.expires_at')
                    ->orWhere('user_permissions.expires_at', '>', now());
            })
            ->exists();

        if ($hasDirectDeny) {
            return true;
        }

        // Verificar deny via roles
        $hasRoleDeny = DB::table('user_roles')
            ->join('role_permissions', 'user_roles.role_id', '=', 'role_permissions.role_id')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('user_roles.user_id', $this->id)
            ->where('permissions.slug', $permission)
            ->where('role_permissions.deny', true)
            ->where(function ($query) {
                $query->whereNull('role_permissions.expires_at')
                    ->orWhere('role_permissions.expires_at', '>', now());
            })
            ->exists();

        return $hasRoleDeny;
    }

    /**
     * Verifica permissão via roles incluindo hierarquia.
     */
    protected function hasPermissionViaRoles(string $permission, ?string $context = null): bool
    {
        // Obter todas as roles do usuário (incluindo roles filhas via hierarquia)
        $roleIds = $this->getAllRoleIds();

        if (empty($roleIds)) {
            return false;
        }

        return DB::table('role_permissions')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->whereIn('role_permissions.role_id', $roleIds)
            ->where('permissions.slug', $permission)
            ->where('role_permissions.deny', false)
            ->where(function ($query) {
                $query->whereNull('role_permissions.expires_at')
                    ->orWhere('role_permissions.expires_at', '>', now());
            })
            ->when($context, function ($query) use ($context) {
                $query->where(function ($q) use ($context) {
                    $q->whereNull('permissions.context')
                        ->orWhere('permissions.context', $context);
                });
            })
            ->exists();
    }

    /**
     * Retorna todos os IDs de roles do usuário incluindo roles filhas via hierarquia.
     */
    protected function getAllRoleIds(): array
    {
        $cacheKey = "user_all_role_ids_{$this->id}";

        return Cache::remember($cacheKey, now()->addMinutes($this->permissionsCacheTtl), function () {
            // Roles diretas do usuário
            $directRoleIds = DB::table('user_roles')
                ->where('user_id', $this->id)
                ->pluck('role_id')
                ->toArray();

            if (empty($directRoleIds)) {
                return [];
            }

            // Buscar todas as roles filhas recursivamente
            $allRoleIds = $directRoleIds;
            $rolesToCheck = $directRoleIds;

            while (!empty($rolesToCheck)) {
                $childRoleIds = DB::table('roles')
                    ->whereIn('parent_role_id', $rolesToCheck)
                    ->pluck('id')
                    ->toArray();

                $newRoles = array_diff($childRoleIds, $allRoleIds);
                if (empty($newRoles)) {
                    break;
                }

                $allRoleIds = array_merge($allRoleIds, $newRoles);
                $rolesToCheck = $newRoles;
            }

            return array_unique($allRoleIds);
        });
    }

    /**
     * Verifica se o usuário tem uma role específica (incluindo hierarquia).
     */
    public function hasRole(string $role): bool
    {
        // Verificar se é admin
        if ($role === 'admin' && $this->isAdmin()) {
            return true;
        }

        $cacheKey = "user_has_role_{$this->id}_{$role}";

        return Cache::remember($cacheKey, now()->addMinutes($this->permissionsCacheTtl), function () use ($role) {
            // Verificar role direta
            $hasDirectRole = DB::table('user_roles')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('user_roles.user_id', $this->id)
                ->where(function ($query) use ($role) {
                    $query->where('roles.slug', $role)
                        ->orWhere('roles.name', $role);
                })
                ->exists();

            if ($hasDirectRole) {
                return true;
            }

            // Verificar via hierarquia (se alguma role filha do usuário tem a role pai)
            $allRoleIds = $this->getAllRoleIds();
            
            return DB::table('roles')
                ->whereIn('id', $allRoleIds)
                ->where(function ($query) use ($role) {
                    $query->where('slug', $role)
                        ->orWhere('name', $role);
                })
                ->exists();
        });
    }

    /**
     * Verifica se o usuário é admin.
     */
    public function isAdmin(): bool
    {
        $cacheKey = "user_is_admin_{$this->id}";

        return Cache::remember($cacheKey, now()->addMinutes($this->permissionsCacheTtl), function () {
            return DB::table('user_roles')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('user_roles.user_id', $this->id)
                ->where('roles.slug', 'admin')
                ->exists();
        });
    }

    /**
     * Retorna todas as permissões do usuário (diretas + via roles).
     * Respeita permissões temporárias e negativas.
     */
    public function getUserPermissions(): array
    {
        $cacheKey = "user_all_permissions_{$this->id}";

        return Cache::remember($cacheKey, now()->addMinutes($this->permissionsCacheTtl), function () {
            // Se for admin, retornar todas as permissões
            if ($this->isAdmin()) {
                return DB::table('permissions')
                    ->pluck('slug')
                    ->toArray();
            }

            // Permissões diretas (apenas não expiradas e não negadas)
            $directPermissions = DB::table('user_permissions')
                ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
                ->where('user_permissions.user_id', $this->id)
                ->where('user_permissions.deny', false)
                ->where(function ($query) {
                    $query->whereNull('user_permissions.expires_at')
                        ->orWhere('user_permissions.expires_at', '>', now());
                })
                ->pluck('permissions.slug')
                ->toArray();

            // Permissões via roles (incluindo hierarquia)
            $roleIds = $this->getAllRoleIds();
            $rolePermissions = [];

            if (!empty($roleIds)) {
                $rolePermissions = DB::table('role_permissions')
                    ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                    ->whereIn('role_permissions.role_id', $roleIds)
                    ->where('role_permissions.deny', false)
                    ->where(function ($query) {
                        $query->whereNull('role_permissions.expires_at')
                            ->orWhere('role_permissions.expires_at', '>', now());
                    })
                    ->pluck('permissions.slug')
                    ->toArray();
            }

            // Remover permissões negadas
            $deniedPermissions = $this->getDeniedPermissions();

            // Combinar e remover duplicatas e permissões negadas
            $allPermissions = array_unique(array_merge($directPermissions, $rolePermissions));
            return array_diff($allPermissions, $deniedPermissions);
        });
    }

    /**
     * Retorna todas as permissões negadas (deny) do usuário.
     */
    protected function getDeniedPermissions(): array
    {
        // Deny direto
        $directDeny = DB::table('user_permissions')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.user_id', $this->id)
            ->where('user_permissions.deny', true)
            ->where(function ($query) {
                $query->whereNull('user_permissions.expires_at')
                    ->orWhere('user_permissions.expires_at', '>', now());
            })
            ->pluck('permissions.slug')
            ->toArray();

        // Deny via roles
        $roleIds = $this->getAllRoleIds();
        $roleDeny = [];

        if (!empty($roleIds)) {
            $roleDeny = DB::table('role_permissions')
                ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                ->whereIn('role_permissions.role_id', $roleIds)
                ->where('role_permissions.deny', true)
                ->where(function ($query) {
                    $query->whereNull('role_permissions.expires_at')
                        ->orWhere('role_permissions.expires_at', '>', now());
                })
                ->pluck('permissions.slug')
                ->toArray();
        }

        return array_unique(array_merge($directDeny, $roleDeny));
    }

    /**
     * Retorna todas as roles do usuário (incluindo hierarquia).
     */
    public function getUserRoles(): array
    {
        $cacheKey = "user_all_roles_{$this->id}";

        return Cache::remember($cacheKey, now()->addMinutes($this->permissionsCacheTtl), function () {
            $roleIds = $this->getAllRoleIds();

            if (empty($roleIds)) {
                return [];
            }

            return DB::table('roles')
                ->whereIn('id', $roleIds)
                ->pluck('slug')
                ->toArray();
        });
    }

    /**
     * Limpa o cache de permissões do usuário.
     * Isso atualiza o timestamp de versão, invalidando todos os caches antigos.
     */
    public function clearPermissionsCache(): void
    {
        // Atualizar versão do cache (isso invalida todos os caches antigos)
        // Usar microtime para garantir que seja sempre diferente
        $versionKey = "user_permissions_version_{$this->id}";
        Cache::forget($versionKey);
        Cache::forever($versionKey, (string) microtime(true));

        // Limpar caches principais
        Cache::forget("user_all_permissions_{$this->id}");
        Cache::forget("user_all_role_ids_{$this->id}");
        Cache::forget("user_all_roles_{$this->id}");
        Cache::forget("user_is_admin_{$this->id}");
    }
}
