import {
    useHasAnyPermission,
    useHasPermission,
    useIsAdmin,
} from '@/hooks/usePermissions';
import type { PermissionName } from '@/types/permissions';
import { ReactNode } from 'react';

interface CanProps {
    permission?: PermissionName;
    permissions?: PermissionName[];
    role?: string;
    isAdmin?: boolean;
    children: ReactNode;
    fallback?: ReactNode;
}

/**
 * Componente para renderização condicional baseada em permissões
 *
 * @example
 * <Can permission="users.create">
 *   <Button>Criar Usuário</Button>
 * </Can>
 *
 * @example
 * <Can permissions={['users.edit', 'users.create']} fallback={<p>Sem permissão</p>}>
 *   <Button>Ações</Button>
 * </Can>
 *
 * @example
 * <Can role="admin">
 *   <Button>Apenas Admin</Button>
 * </Can>
 */
export default function Can({
    permission,
    permissions,
    role,
    isAdmin: requireAdmin,
    children,
    fallback = null,
}: CanProps) {
    const isAdmin = useIsAdmin();

    // Se requer admin e não é admin, não renderiza
    if (requireAdmin && !isAdmin) {
        return <>{fallback}</>;
    }

    // Se requer role específica
    if (role) {
        // Por enquanto, verificamos apenas se é admin
        // Pode ser expandido para verificar outras roles
        if (role === 'admin' && !isAdmin) {
            return <>{fallback}</>;
        }
    }

    // Se tem permissão específica
    if (permission) {
        if (!useHasPermission(permission)) {
            return <>{fallback}</>;
        }
    }

    // Se tem array de permissões (verifica se tem qualquer uma)
    if (permissions && permissions.length > 0) {
        if (!useHasAnyPermission(permissions)) {
            return <>{fallback}</>;
        }
    }

    // Se passou todas as verificações, renderiza children
    return <>{children}</>;
}
