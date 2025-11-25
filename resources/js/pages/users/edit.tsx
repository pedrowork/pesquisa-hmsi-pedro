import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { useEffect } from 'react';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Usuários',
        href: '/users',
    },
    {
        title: 'Editar Usuário',
        href: '/users/edit',
    },
];

interface User {
    id: number;
    name: string;
    email: string;
    status: number;
}

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

interface UsersEditProps {
    user: User;
    permissions: {
        [key: string]: Permission[];
    };
    userPermissions: number[];
    roles: Role[];
    userRoles: number[];
}

export default function UsersEdit({
    user,
    permissions,
    userPermissions,
    roles,
    userRoles,
}: UsersEditProps) {
    const { data, setData, put, processing, errors, reset } = useForm({
        name: user.name || '',
        email: user.email || '',
        password: '',
        password_confirmation: '',
        status: user.status?.toString() || '1',
        permissions: userPermissions || [],
        roles: userRoles || [],
    });

    useEffect(() => {
        if (userPermissions && userPermissions.length > 0) {
            setData('permissions', userPermissions);
        }
        if (userRoles && userRoles.length > 0) {
            setData('roles', userRoles);
        }
    }, [userPermissions, userRoles, setData]);

    const handlePermissionToggle = (permissionId: number) => {
        const currentPermissions = data.permissions || [];
        if (currentPermissions.includes(permissionId)) {
            setData(
                'permissions',
                currentPermissions.filter((id) => id !== permissionId)
            );
        } else {
            setData('permissions', [...currentPermissions, permissionId]);
        }
    };

    const handleRoleToggle = (roleId: number) => {
        const currentRoles = data.roles || [];
        if (currentRoles.includes(roleId)) {
            setData('roles', currentRoles.filter((id) => id !== roleId));
        } else {
            setData('roles', [...currentRoles, roleId]);
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/users/${user.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                reset('password', 'password_confirmation');
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Editar Usuário" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center gap-4">
                    <Link href="/users">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Voltar
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold">Editar Usuário</h1>
                        <p className="text-muted-foreground mt-1">
                            Edite os dados do usuário {user.name}
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Dados do Usuário</CardTitle>
                        <CardDescription>
                            Atualize as informações do usuário
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
                                    autoComplete="name"
                                    placeholder="Nome completo"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    Email <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="email"
                                    name="email"
                                    type="email"
                                    required
                                    value={data.email}
                                    onChange={(e) =>
                                        setData('email', e.target.value)
                                    }
                                    autoComplete="username"
                                    placeholder="email@exemplo.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">
                                    Nova Senha{' '}
                                    <span className="text-muted-foreground text-xs">
                                        (deixe em branco para manter a atual)
                                    </span>
                                </Label>
                                <Input
                                    id="password"
                                    name="password"
                                    type="password"
                                    value={data.password}
                                    onChange={(e) =>
                                        setData('password', e.target.value)
                                    }
                                    autoComplete="new-password"
                                    placeholder="Mínimo 8 caracteres"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    Confirmar Nova Senha
                                </Label>
                                <Input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    value={data.password_confirmation}
                                    onChange={(e) =>
                                        setData(
                                            'password_confirmation',
                                            e.target.value
                                        )
                                    }
                                    autoComplete="new-password"
                                    placeholder="Digite a senha novamente"
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="status">
                                    Status <span className="text-red-500">*</span>
                                </Label>
                                <select
                                    id="status"
                                    name="status"
                                    required
                                    value={data.status}
                                    onChange={(e) =>
                                        setData('status', e.target.value)
                                    }
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                                <InputError message={errors.status} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing
                                        ? 'Salvando...'
                                        : 'Atualizar Usuário'}
                                </Button>
                                <Link href="/users">
                                    <Button type="button" variant="outline">
                                        Cancelar
                                    </Button>
                                </Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                {/* Seção de Roles */}
                <Card>
                    <CardHeader>
                        <CardTitle>Roles (Grupos)</CardTitle>
                        <CardDescription>
                            Atribua roles ao usuário. As permissões das roles serão aplicadas automaticamente.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-lg border border-input p-4 max-h-64 overflow-y-auto">
                            {roles.length === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    Nenhuma role cadastrada.
                                </p>
                            ) : (
                                <div className="space-y-2">
                                    {roles.map((role) => (
                                        <div
                                            key={role.id}
                                            className="flex items-center space-x-2"
                                        >
                                            <Checkbox
                                                id={`role-${role.id}`}
                                                checked={(data.roles || []).includes(role.id)}
                                                onCheckedChange={() =>
                                                    handleRoleToggle(role.id)
                                                }
                                            />
                                            <label
                                                htmlFor={`role-${role.id}`}
                                                className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer"
                                            >
                                                {role.name}
                                                {role.description && (
                                                    <span className="block text-xs text-muted-foreground">
                                                        {role.description}
                                                    </span>
                                                )}
                                            </label>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                        <InputError message={errors.roles} />
                    </CardContent>
                </Card>

                {/* Seção de Permissões Diretas */}
                <Card>
                    <CardHeader>
                        <CardTitle>Permissões Diretas</CardTitle>
                        <CardDescription>
                            Atribua permissões diretamente ao usuário. Estas permissões são adicionadas às permissões das roles.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-6">
                            {Object.entries(permissions).map(([category, categoryPermissions]) => (
                                <div key={category}>
                                    <h3 className="text-sm font-semibold mb-3 text-foreground">
                                        {category}
                                    </h3>
                                    <div className="rounded-lg border border-input p-4 max-h-64 overflow-y-auto">
                                        {categoryPermissions.length === 0 ? (
                                            <p className="text-sm text-muted-foreground">
                                                Nenhuma permissão nesta categoria.
                                            </p>
                                        ) : (
                                            <div className="space-y-2">
                                                {categoryPermissions.map((permission) => (
                                                    <div
                                                        key={permission.id}
                                                        className="flex items-center space-x-2"
                                                    >
                                                        <Checkbox
                                                            id={`permission-${permission.id}`}
                                                            checked={(data.permissions || []).includes(permission.id)}
                                                            onCheckedChange={() =>
                                                                handlePermissionToggle(permission.id)
                                                            }
                                                        />
                                                        <label
                                                            htmlFor={`permission-${permission.id}`}
                                                            className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer flex-1"
                                                        >
                                                            <span className="font-semibold">{permission.name}</span>
                                                            <span className="block text-xs text-muted-foreground">
                                                                {permission.slug}
                                                            </span>
                                                            {permission.description && (
                                                                <span className="block text-xs text-muted-foreground mt-1">
                                                                    {permission.description}
                                                                </span>
                                                            )}
                                                        </label>
                                                    </div>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                        <InputError message={errors.permissions} />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
