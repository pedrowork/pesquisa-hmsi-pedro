import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import {
    ArrowLeft,
    CheckSquare,
    ChevronDown,
    ChevronRight,
    Search,
    Square,
} from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Roles',
        href: '/roles',
    },
    {
        title: 'Editar Role',
        href: '/roles/edit',
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

interface RolesEditProps {
    role: Role;
    permissions: Permission[];
    groupedPermissions: Record<string, Permission[]>;
    rolePermissions: number[];
}

export default function RolesEdit({
    role,
    permissions,
    groupedPermissions,
    rolePermissions,
}: RolesEditProps) {
    const [search, setSearch] = useState('');
    const [expandedGroups, setExpandedGroups] = useState<
        Record<string, boolean>
    >({});
    const { data, setData, put, processing, errors } = useForm({
        name: role.name || '',
        slug: role.slug || '',
        description: role.description || '',
        permissions: rolePermissions || [],
    });

    useEffect(() => {
        if (rolePermissions && rolePermissions.length > 0) {
            setData('permissions', rolePermissions);
        }
    }, [rolePermissions, setData]);

    const handlePermissionToggle = (permissionId: number) => {
        const currentPermissions = data.permissions || [];
        if (currentPermissions.includes(permissionId)) {
            setData(
                'permissions',
                currentPermissions.filter((id) => id !== permissionId),
            );
        } else {
            setData('permissions', [...currentPermissions, permissionId]);
        }
    };

    const handleGroupToggle = (groupPermissions: Permission[]) => {
        const currentPermissions = data.permissions || [];
        const groupIds = groupPermissions.map((p) => p.id);
        const allSelected = groupIds.every((id) =>
            currentPermissions.includes(id),
        );

        if (allSelected) {
            // Desmarcar todas do grupo
            setData(
                'permissions',
                currentPermissions.filter((id) => !groupIds.includes(id)),
            );
        } else {
            // Marcar todas do grupo
            const newPermissions = [...currentPermissions];
            groupIds.forEach((id) => {
                if (!newPermissions.includes(id)) {
                    newPermissions.push(id);
                }
            });
            setData('permissions', newPermissions);
        }
    };

    const toggleGroup = (groupName: string) => {
        setExpandedGroups((prev) => ({
            ...prev,
            [groupName]: !prev[groupName],
        }));
    };

    // Filtrar permissões baseado na busca
    const filteredGroupedPermissions = useMemo(() => {
        if (!search) return groupedPermissions;

        const lowerSearch = search.toLowerCase();
        const filtered: Record<string, Permission[]> = {};

        Object.entries(groupedPermissions).forEach(([group, perms]) => {
            const filteredPerms = perms.filter(
                (p) =>
                    p.name.toLowerCase().includes(lowerSearch) ||
                    p.slug.toLowerCase().includes(lowerSearch) ||
                    (p.description &&
                        p.description.toLowerCase().includes(lowerSearch)),
            );

            if (filteredPerms.length > 0) {
                filtered[group] = filteredPerms;
            }
        });

        return filtered;
    }, [groupedPermissions, search]);

    // Contar permissões selecionadas por grupo
    const getGroupSelectionCount = (groupPermissions: Permission[]) => {
        const currentPermissions = data.permissions || [];
        return groupPermissions.filter((p) => currentPermissions.includes(p.id))
            .length;
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/roles/${role.id}`, {
            preserveScroll: true,
            onError: (errors) => {
                console.error('Erros ao atualizar role:', errors);
            },
            onSuccess: () => {
                // Redirecionar para a lista de roles
                router.visit('/roles');
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Editar Role" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center gap-4">
                    <Link href="/roles">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Voltar
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold">Editar Role</h1>
                        <p className="mt-1 text-muted-foreground">
                            Edite os dados da role {role.name}
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Dados da Role</CardTitle>
                        <CardDescription>
                            Atualize as informações da role
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="grid gap-2">
                                <Label htmlFor="name">
                                    Nome <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="name"
                                    name="name"
                                    type="text"
                                    required
                                    value={data.name}
                                    onChange={(e) =>
                                        setData('name', e.target.value)
                                    }
                                    placeholder="Ex: Administrador"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="slug">
                                    Slug <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="slug"
                                    name="slug"
                                    type="text"
                                    required
                                    value={data.slug}
                                    onChange={(e) =>
                                        setData('slug', e.target.value)
                                    }
                                    placeholder="Ex: admin"
                                />
                                <p className="text-xs text-muted-foreground">
                                    Identificador único (sem espaços, use hífens
                                    ou underscores)
                                </p>
                                <InputError message={errors.slug} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="description">Descrição</Label>
                                <textarea
                                    id="description"
                                    name="description"
                                    rows={3}
                                    value={data.description}
                                    onChange={(e) =>
                                        setData('description', e.target.value)
                                    }
                                    className="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                    placeholder="Descrição da role..."
                                />
                                <InputError message={errors.description} />
                            </div>

                            <div className="grid gap-4">
                                <div className="flex items-center justify-between">
                                    <Label className="text-base font-semibold">
                                        Permissões
                                        <span className="ml-2 text-sm font-normal text-muted-foreground">
                                            ({data.permissions?.length || 0} de{' '}
                                            {permissions.length} selecionadas)
                                        </span>
                                    </Label>
                                </div>

                                {/* Busca */}
                                <div className="relative">
                                    <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        type="text"
                                        placeholder="Buscar permissões por nome, slug ou descrição..."
                                        value={search}
                                        onChange={(e) =>
                                            setSearch(e.target.value)
                                        }
                                        className="pl-9"
                                    />
                                </div>

                                {/* Lista de permissões agrupadas */}
                                <div className="max-h-[600px] space-y-4 overflow-y-auto rounded-lg border border-input p-4">
                                    {Object.keys(filteredGroupedPermissions)
                                        .length === 0 ? (
                                        <p className="py-8 text-center text-sm text-muted-foreground">
                                            {search
                                                ? 'Nenhuma permissão encontrada.'
                                                : 'Nenhuma permissão cadastrada.'}
                                        </p>
                                    ) : (
                                        Object.entries(
                                            filteredGroupedPermissions,
                                        ).map(
                                            ([groupName, groupPermissions]) => {
                                                const isExpanded =
                                                    expandedGroups[
                                                        groupName
                                                    ] !== false; // Por padrão expandido
                                                const selectedCount =
                                                    getGroupSelectionCount(
                                                        groupPermissions,
                                                    );
                                                const allSelected =
                                                    selectedCount ===
                                                    groupPermissions.length;
                                                const someSelected =
                                                    selectedCount > 0 &&
                                                    selectedCount <
                                                        groupPermissions.length;

                                                return (
                                                    <Collapsible
                                                        key={groupName}
                                                        open={isExpanded}
                                                        onOpenChange={() =>
                                                            toggleGroup(
                                                                groupName,
                                                            )
                                                        }
                                                        className="rounded-lg border"
                                                    >
                                                        <CollapsibleTrigger className="w-full">
                                                            <div className="flex items-center justify-between p-3 transition-colors hover:bg-muted/50">
                                                                <div className="flex flex-1 items-center gap-3">
                                                                    {isExpanded ? (
                                                                        <ChevronDown className="h-4 w-4 text-muted-foreground" />
                                                                    ) : (
                                                                        <ChevronRight className="h-4 w-4 text-muted-foreground" />
                                                                    )}
                                                                    <div className="flex items-center gap-2">
                                                                        <span className="text-sm font-semibold text-foreground uppercase">
                                                                            {
                                                                                groupName
                                                                            }
                                                                        </span>
                                                                        <Badge
                                                                            variant="secondary"
                                                                            className="text-xs"
                                                                        >
                                                                            {
                                                                                selectedCount
                                                                            }
                                                                            /
                                                                            {
                                                                                groupPermissions.length
                                                                            }
                                                                        </Badge>
                                                                    </div>
                                                                </div>
                                                                <Button
                                                                    type="button"
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    onClick={(
                                                                        e,
                                                                    ) => {
                                                                        e.stopPropagation();
                                                                        handleGroupToggle(
                                                                            groupPermissions,
                                                                        );
                                                                    }}
                                                                    className="h-7 px-2"
                                                                >
                                                                    {allSelected ? (
                                                                        <>
                                                                            <CheckSquare className="mr-1 h-3.5 w-3.5" />
                                                                            Desmarcar
                                                                            todas
                                                                        </>
                                                                    ) : (
                                                                        <>
                                                                            <Square className="mr-1 h-3.5 w-3.5" />
                                                                            Selecionar
                                                                            todas
                                                                        </>
                                                                    )}
                                                                </Button>
                                                            </div>
                                                        </CollapsibleTrigger>
                                                        <CollapsibleContent>
                                                            <div className="space-y-2 border-t px-3 pt-3 pb-3">
                                                                {groupPermissions.map(
                                                                    (
                                                                        permission,
                                                                    ) => {
                                                                        const isSelected =
                                                                            (
                                                                                data.permissions ||
                                                                                []
                                                                            ).includes(
                                                                                permission.id,
                                                                            );

                                                                        return (
                                                                            <div
                                                                                key={
                                                                                    permission.id
                                                                                }
                                                                                className={`flex items-start space-x-3 rounded-md p-2 transition-colors ${
                                                                                    isSelected
                                                                                        ? 'border border-primary/20 bg-primary/5'
                                                                                        : 'hover:bg-muted/30'
                                                                                }`}
                                                                            >
                                                                                <Checkbox
                                                                                    id={`permission-${permission.id}`}
                                                                                    checked={
                                                                                        isSelected
                                                                                    }
                                                                                    onCheckedChange={() =>
                                                                                        handlePermissionToggle(
                                                                                            permission.id,
                                                                                        )
                                                                                    }
                                                                                    className="mt-0.5"
                                                                                />
                                                                                <label
                                                                                    htmlFor={`permission-${permission.id}`}
                                                                                    className="flex-1 cursor-pointer"
                                                                                >
                                                                                    <div className="flex items-start justify-between gap-2">
                                                                                        <div className="flex-1">
                                                                                            <div className="text-sm leading-none font-medium">
                                                                                                {
                                                                                                    permission.name
                                                                                                }
                                                                                            </div>
                                                                                            <div className="mt-1 font-mono text-xs text-muted-foreground">
                                                                                                {
                                                                                                    permission.slug
                                                                                                }
                                                                                            </div>
                                                                                            {permission.description && (
                                                                                                <div className="mt-1 text-xs text-muted-foreground">
                                                                                                    {
                                                                                                        permission.description
                                                                                                    }
                                                                                                </div>
                                                                                            )}
                                                                                        </div>
                                                                                        {isSelected && (
                                                                                            <Badge
                                                                                                variant="default"
                                                                                                className="text-xs"
                                                                                            >
                                                                                                Selecionada
                                                                                            </Badge>
                                                                                        )}
                                                                                    </div>
                                                                                </label>
                                                                            </div>
                                                                        );
                                                                    },
                                                                )}
                                                            </div>
                                                        </CollapsibleContent>
                                                    </Collapsible>
                                                );
                                            },
                                        )
                                    )}
                                </div>
                                <InputError message={errors.permissions} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing
                                        ? 'Salvando...'
                                        : 'Atualizar Role'}
                                </Button>
                                <Link href="/roles">
                                    <Button type="button" variant="outline">
                                        Cancelar
                                    </Button>
                                </Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
