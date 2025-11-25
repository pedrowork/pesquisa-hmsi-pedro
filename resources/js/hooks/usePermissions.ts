import { usePage } from '@inertiajs/react';
import type { PermissionName } from '@/types/permissions';

interface SharedAuthData {
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
        } | null;
        permissions: PermissionName[];
        isAdmin: boolean;
    };
}

/**
 * Hook para acessar permissões do usuário autenticado
 */
export const usePermissions = () => {
    const { auth } = usePage<SharedAuthData>().props;

    return {
        permissions: auth.permissions || [],
        isAdmin: auth.isAdmin || false,
        user: auth.user,
    };
};

/**
 * Hook para verificar se o usuário tem uma permissão específica
 */
export const useHasPermission = (permission: PermissionName): boolean => {
    const { permissions, isAdmin } = usePermissions();

    // Admin tem acesso total
    if (isAdmin) {
        return true;
    }

    return permissions.includes(permission);
};

/**
 * Hook para verificar se o usuário é admin
 */
export const useIsAdmin = (): boolean => {
    const { isAdmin } = usePermissions();
    return isAdmin;
};

/**
 * Hook para verificar se o usuário tem qualquer uma das permissões fornecidas
 */
export const useHasAnyPermission = (permissions: PermissionName[]): boolean => {
    const { permissions: userPermissions, isAdmin } = usePermissions();

    if (isAdmin) {
        return true;
    }

    return permissions.some((permission) => userPermissions.includes(permission));
};

/**
 * Hook para verificar se o usuário tem todas as permissões fornecidas
 */
export const useHasAllPermissions = (permissions: PermissionName[]): boolean => {
    const { permissions: userPermissions, isAdmin } = usePermissions();

    if (isAdmin) {
        return true;
    }

    return permissions.every((permission) => userPermissions.includes(permission));
};


