import Can from '@/components/Can';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useHasPermission } from '@/hooks/usePermissions';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Check, Search, Shield, Users, X } from 'lucide-react';
import React, { FormEvent, useMemo, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Permissões',
        href: '/permissions',
    },
    {
        title: 'Matriz de Permissões',
        href: '/permissions?matrix=true',
    },
];

interface Permission {
    id: number;
    name: string;
    slug: string;
    description: string | null;
}

interface Role {
    id: number;
    name: string;
    slug: string;
    description: string | null;
}

interface User {
    id: number;
    name: string;
    email: string;
    status: number;
}

interface MatrixProps {
    permissions: Permission[];
    roles: Role[];
    users: User[];
    groupedPermissions: Record<string, Permission[]>;
    rolePermissions: Record<number, number[]>; // role_id => [permission_ids]
    userPermissions: Record<number, number[]>; // user_id => [permission_ids]
    viewType: 'roles' | 'users';
    filters: {
        search: string;
    };
}

export default function PermissionsMatrix({
    permissions,
    roles,
    users,
    groupedPermissions,
    rolePermissions,
    userPermissions,
    viewType: initialViewType,
    filters,
}: MatrixProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [viewType, setViewType] = useState<'roles' | 'users'>(
        initialViewType,
    );
    const [loading, setLoading] = useState<Record<string, boolean>>({});
    const canEdit = useHasPermission('permissions.edit');

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        router.get(
            '/permissions',
            { matrix: 'true', view: viewType, search },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const handleToggleRolePermission = async (
        roleId: number,
        permissionId: number,
    ) => {
        const key = `role_${roleId}_${permissionId}`;
        setLoading({ ...loading, [key]: true });

        try {
            const response = await fetch(
                `/permissions/roles/${roleId}/toggle/${permissionId}`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN':
                            document
                                .querySelector('meta[name="csrf-token"]')
                                ?.getAttribute('content') || '',
                    },
                },
            );

            if (response.ok) {
                const data = await response.json();
                // Se houver mensagem especial, exibir aviso
                if (data.message) {
                    alert(data.message);
                }
                // Recarregar a página para atualizar os dados
                router.reload({ only: ['rolePermissions', 'userPermissions'] });
            }
        } catch (error) {
            console.error('Erro ao atualizar permissão:', error);
        } finally {
            setLoading({ ...loading, [key]: false });
        }
    };

    const handleToggleUserPermission = async (
        userId: number,
        permissionId: number,
    ) => {
        const key = `user_${userId}_${permissionId}`;
        setLoading({ ...loading, [key]: true });

        try {
            const response = await fetch(
                `/permissions/users/${userId}/toggle/${permissionId}`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN':
                            document
                                .querySelector('meta[name="csrf-token"]')
                                ?.getAttribute('content') || '',
                    },
                },
            );

            if (response.ok) {
                const data = await response.json();
                // Se houver mensagem especial, exibir aviso
                if (data.message) {
                    alert(data.message);
                }
                // Recarregar a página para atualizar os dados
                router.reload({ only: ['rolePermissions', 'userPermissions'] });
            }
        } catch (error) {
            console.error('Erro ao atualizar permissão:', error);
        } finally {
            setLoading({ ...loading, [key]: false });
        }
    };

    const hasRolePermission = (
        roleId: number,
        permissionId: number,
    ): boolean => {
        return rolePermissions[roleId]?.includes(permissionId) || false;
    };

    const hasUserPermission = (
        userId: number,
        permissionId: number,
    ): boolean => {
        return userPermissions[userId]?.includes(permissionId) || false;
    };

    const filteredPermissions = useMemo(() => {
        if (!search) return permissions;
        const lowerSearch = search.toLowerCase();
        return permissions.filter(
            (p) =>
                p.name.toLowerCase().includes(lowerSearch) ||
                p.slug.toLowerCase().includes(lowerSearch) ||
                (p.description &&
                    p.description.toLowerCase().includes(lowerSearch)),
        );
    }, [permissions, search]);

    const filteredRoles = useMemo(() => {
        if (!search) return roles;
        const lowerSearch = search.toLowerCase();
        return roles.filter(
            (r) =>
                r.name.toLowerCase().includes(lowerSearch) ||
                r.slug.toLowerCase().includes(lowerSearch),
        );
    }, [roles, search]);

    const filteredUsers = useMemo(() => {
        if (!search) return users;
        const lowerSearch = search.toLowerCase();
        return users.filter(
            (u) =>
                u.name.toLowerCase().includes(lowerSearch) ||
                u.email.toLowerCase().includes(lowerSearch),
        );
    }, [users, search]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Matriz de Permissões" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">
                            Matriz de Permissões
                        </h1>
                        <p className="mt-1 text-muted-foreground">
                            Gerencie permissões por role ou por usuário de forma
                            visual e dinâmica
                        </p>
                    </div>
                    <Button
                        variant="outline"
                        onClick={() => router.get('/permissions')}
                    >
                        Ver Lista
                    </Button>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle>Filtros</CardTitle>
                        <CardDescription>
                            Busque permissões, roles ou usuários
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSearch} className="flex gap-4">
                            <div className="flex-1">
                                <Label htmlFor="search" className="sr-only">
                                    Buscar
                                </Label>
                                <Input
                                    id="search"
                                    type="text"
                                    placeholder="Buscar por nome, slug ou descrição..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                />
                            </div>
                            <Button type="submit" variant="outline">
                                <Search className="mr-2 h-4 w-4" />
                                Buscar
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                {/* Matrix Tabs */}
                <Card>
                    <CardHeader>
                        <CardTitle>Matriz de Permissões</CardTitle>
                        <CardDescription>
                            {viewType === 'roles'
                                ? `${filteredRoles.length} role(s) × ${filteredPermissions.length} permissão(ões)`
                                : `${filteredUsers.length} usuário(s) × ${filteredPermissions.length} permissão(ões)`}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Tabs
                            value={viewType}
                            onValueChange={(value) => {
                                setViewType(value as 'roles' | 'users');
                                router.get(
                                    '/permissions',
                                    { matrix: 'true', view: value, search },
                                    {
                                        preserveState: true,
                                        replace: true,
                                    },
                                );
                            }}
                        >
                            <TabsList className="grid w-full grid-cols-2">
                                <TabsTrigger value="roles">
                                    <Shield className="mr-2 h-4 w-4" />
                                    Por Role
                                </TabsTrigger>
                                <TabsTrigger value="users">
                                    <Users className="mr-2 h-4 w-4" />
                                    Por Usuário
                                </TabsTrigger>
                            </TabsList>

                            <TabsContent value="roles" className="mt-6">
                                <Can permission="permissions.edit">
                                    <div className="overflow-x-auto">
                                        <div className="inline-block min-w-full align-middle">
                                            <div className="overflow-hidden rounded-lg border">
                                                <table className="min-w-full divide-y divide-border">
                                                    <thead className="bg-muted">
                                                        <tr>
                                                            <th className="sticky left-0 z-10 min-w-[250px] bg-muted px-4 py-3 text-left text-sm font-semibold">
                                                                Permissão
                                                            </th>
                                                            {filteredRoles.map(
                                                                (role) => (
                                                                    <th
                                                                        key={
                                                                            role.id
                                                                        }
                                                                        className="min-w-[120px] px-4 py-3 text-center text-xs font-medium text-muted-foreground"
                                                                    >
                                                                        <div className="flex flex-col items-center gap-1">
                                                                            <span className="font-semibold">
                                                                                {
                                                                                    role.name
                                                                                }
                                                                            </span>
                                                                            <span className="text-xs text-muted-foreground">
                                                                                {
                                                                                    role.slug
                                                                                }
                                                                            </span>
                                                                        </div>
                                                                    </th>
                                                                ),
                                                            )}
                                                        </tr>
                                                    </thead>
                                                    <tbody className="divide-y divide-border bg-background">
                                                        {Object.entries(
                                                            groupedPermissions,
                                                        ).map(
                                                            ([
                                                                context,
                                                                contextPermissions,
                                                            ]) => {
                                                                const contextPerms =
                                                                    contextPermissions.filter(
                                                                        (p) =>
                                                                            filteredPermissions.some(
                                                                                (
                                                                                    fp,
                                                                                ) =>
                                                                                    fp.id ===
                                                                                    p.id,
                                                                            ),
                                                                    );

                                                                if (
                                                                    contextPerms.length ===
                                                                    0
                                                                )
                                                                    return null;

                                                                return (
                                                                    <React.Fragment
                                                                        key={
                                                                            context
                                                                        }
                                                                    >
                                                                        <tr className="bg-muted/50">
                                                                            <td
                                                                                colSpan={
                                                                                    filteredRoles.length +
                                                                                    1
                                                                                }
                                                                                className="px-4 py-2 text-sm font-semibold text-muted-foreground uppercase"
                                                                            >
                                                                                {
                                                                                    context
                                                                                }
                                                                            </td>
                                                                        </tr>
                                                                        {contextPerms.map(
                                                                            (
                                                                                permission,
                                                                            ) => (
                                                                                <tr
                                                                                    key={
                                                                                        permission.id
                                                                                    }
                                                                                    className="transition-colors hover:bg-muted/30"
                                                                                >
                                                                                    <td className="sticky left-0 z-10 bg-background px-4 py-3 text-sm">
                                                                                        <div className="flex flex-col">
                                                                                            <span className="font-medium">
                                                                                                {
                                                                                                    permission.name
                                                                                                }
                                                                                            </span>
                                                                                            <span className="font-mono text-xs text-muted-foreground">
                                                                                                {
                                                                                                    permission.slug
                                                                                                }
                                                                                            </span>
                                                                                            {permission.description && (
                                                                                                <span className="mt-1 text-xs text-muted-foreground">
                                                                                                    {
                                                                                                        permission.description
                                                                                                    }
                                                                                                </span>
                                                                                            )}
                                                                                        </div>
                                                                                    </td>
                                                                                    {filteredRoles.map(
                                                                                        (
                                                                                            role,
                                                                                        ) => {
                                                                                            const hasPermission =
                                                                                                hasRolePermission(
                                                                                                    role.id,
                                                                                                    permission.id,
                                                                                                );
                                                                                            const key = `role_${role.id}_${permission.id}`;
                                                                                            const isLoading =
                                                                                                loading[
                                                                                                    key
                                                                                                ];

                                                                                            return (
                                                                                                <td
                                                                                                    key={
                                                                                                        role.id
                                                                                                    }
                                                                                                    className="px-4 py-3 text-center"
                                                                                                >
                                                                                                    <Can permission="permissions.edit">
                                                                                                        <div className="flex justify-center">
                                                                                                            <Checkbox
                                                                                                                checked={
                                                                                                                    hasPermission
                                                                                                                }
                                                                                                                onCheckedChange={() => {
                                                                                                                    if (
                                                                                                                        !isLoading
                                                                                                                    ) {
                                                                                                                        handleToggleRolePermission(
                                                                                                                            role.id,
                                                                                                                            permission.id,
                                                                                                                        );
                                                                                                                    }
                                                                                                                }}
                                                                                                                disabled={
                                                                                                                    isLoading
                                                                                                                }
                                                                                                                className="h-5 w-5"
                                                                                                            />
                                                                                                        </div>
                                                                                                    </Can>
                                                                                                    {!canEdit && (
                                                                                                        <div className="flex justify-center">
                                                                                                            {hasPermission ? (
                                                                                                                <Check className="h-5 w-5 text-green-500" />
                                                                                                            ) : (
                                                                                                                <X className="h-5 w-5 text-muted-foreground" />
                                                                                                            )}
                                                                                                        </div>
                                                                                                    )}
                                                                                                </td>
                                                                                            );
                                                                                        },
                                                                                    )}
                                                                                </tr>
                                                                            ),
                                                                        )}
                                                                    </React.Fragment>
                                                                );
                                                            },
                                                        )}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </Can>
                                {!canEdit && (
                                    <div className="py-8 text-center text-muted-foreground">
                                        Você não tem permissão para editar
                                        permissões
                                    </div>
                                )}
                            </TabsContent>

                            <TabsContent value="users" className="mt-6">
                                <Can permission="permissions.edit">
                                    <div className="overflow-x-auto">
                                        <div className="inline-block min-w-full align-middle">
                                            <div className="overflow-hidden rounded-lg border">
                                                <table className="min-w-full divide-y divide-border">
                                                    <thead className="bg-muted">
                                                        <tr>
                                                            <th className="sticky left-0 z-10 min-w-[250px] bg-muted px-4 py-3 text-left text-sm font-semibold">
                                                                Permissão
                                                            </th>
                                                            {filteredUsers.map(
                                                                (user) => (
                                                                    <th
                                                                        key={
                                                                            user.id
                                                                        }
                                                                        className="min-w-[150px] px-4 py-3 text-center text-xs font-medium text-muted-foreground"
                                                                    >
                                                                        <div className="flex flex-col items-center gap-1">
                                                                            <span className="font-semibold">
                                                                                {
                                                                                    user.name
                                                                                }
                                                                            </span>
                                                                            <span className="text-xs text-muted-foreground">
                                                                                {
                                                                                    user.email
                                                                                }
                                                                            </span>
                                                                        </div>
                                                                    </th>
                                                                ),
                                                            )}
                                                        </tr>
                                                    </thead>
                                                    <tbody className="divide-y divide-border bg-background">
                                                        {Object.entries(
                                                            groupedPermissions,
                                                        ).map(
                                                            ([
                                                                context,
                                                                contextPermissions,
                                                            ]) => {
                                                                const contextPerms =
                                                                    contextPermissions.filter(
                                                                        (p) =>
                                                                            filteredPermissions.some(
                                                                                (
                                                                                    fp,
                                                                                ) =>
                                                                                    fp.id ===
                                                                                    p.id,
                                                                            ),
                                                                    );

                                                                if (
                                                                    contextPerms.length ===
                                                                    0
                                                                )
                                                                    return null;

                                                                return (
                                                                    <React.Fragment
                                                                        key={
                                                                            context
                                                                        }
                                                                    >
                                                                        <tr className="bg-muted/50">
                                                                            <td
                                                                                colSpan={
                                                                                    filteredUsers.length +
                                                                                    1
                                                                                }
                                                                                className="px-4 py-2 text-sm font-semibold text-muted-foreground uppercase"
                                                                            >
                                                                                {
                                                                                    context
                                                                                }
                                                                            </td>
                                                                        </tr>
                                                                        {contextPerms.map(
                                                                            (
                                                                                permission,
                                                                            ) => (
                                                                                <tr
                                                                                    key={
                                                                                        permission.id
                                                                                    }
                                                                                    className="transition-colors hover:bg-muted/30"
                                                                                >
                                                                                    <td className="sticky left-0 z-10 bg-background px-4 py-3 text-sm">
                                                                                        <div className="flex flex-col">
                                                                                            <span className="font-medium">
                                                                                                {
                                                                                                    permission.name
                                                                                                }
                                                                                            </span>
                                                                                            <span className="font-mono text-xs text-muted-foreground">
                                                                                                {
                                                                                                    permission.slug
                                                                                                }
                                                                                            </span>
                                                                                            {permission.description && (
                                                                                                <span className="mt-1 text-xs text-muted-foreground">
                                                                                                    {
                                                                                                        permission.description
                                                                                                    }
                                                                                                </span>
                                                                                            )}
                                                                                        </div>
                                                                                    </td>
                                                                                    {filteredUsers.map(
                                                                                        (
                                                                                            user,
                                                                                        ) => {
                                                                                            const hasPermission =
                                                                                                hasUserPermission(
                                                                                                    user.id,
                                                                                                    permission.id,
                                                                                                );
                                                                                            const key = `user_${user.id}_${permission.id}`;
                                                                                            const isLoading =
                                                                                                loading[
                                                                                                    key
                                                                                                ];

                                                                                            return (
                                                                                                <td
                                                                                                    key={
                                                                                                        user.id
                                                                                                    }
                                                                                                    className="px-4 py-3 text-center"
                                                                                                >
                                                                                                    <Can permission="permissions.edit">
                                                                                                        <div className="flex justify-center">
                                                                                                            <Checkbox
                                                                                                                checked={
                                                                                                                    hasPermission
                                                                                                                }
                                                                                                                onCheckedChange={() => {
                                                                                                                    if (
                                                                                                                        !isLoading
                                                                                                                    ) {
                                                                                                                        handleToggleUserPermission(
                                                                                                                            user.id,
                                                                                                                            permission.id,
                                                                                                                        );
                                                                                                                    }
                                                                                                                }}
                                                                                                                disabled={
                                                                                                                    isLoading
                                                                                                                }
                                                                                                                className="h-5 w-5"
                                                                                                            />
                                                                                                        </div>
                                                                                                    </Can>
                                                                                                    {!canEdit && (
                                                                                                        <div className="flex justify-center">
                                                                                                            {hasPermission ? (
                                                                                                                <Check className="h-5 w-5 text-green-500" />
                                                                                                            ) : (
                                                                                                                <X className="h-5 w-5 text-muted-foreground" />
                                                                                                            )}
                                                                                                        </div>
                                                                                                    )}
                                                                                                </td>
                                                                                            );
                                                                                        },
                                                                                    )}
                                                                                </tr>
                                                                            ),
                                                                        )}
                                                                    </React.Fragment>
                                                                );
                                                            },
                                                        )}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </Can>
                                {!canEdit && (
                                    <div className="py-8 text-center text-muted-foreground">
                                        Você não tem permissão para editar
                                        permissões
                                    </div>
                                )}
                            </TabsContent>
                        </Tabs>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
