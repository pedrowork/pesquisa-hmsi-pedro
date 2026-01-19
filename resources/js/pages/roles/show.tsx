import Can from '@/components/Can';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

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
        title: 'Detalhes da Role',
        href: '/roles/show',
    },
];

interface Permission {
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

interface Role {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    guard_name: string;
    created_at: string;
    updated_at: string;
}

interface RolesShowProps {
    role: Role;
    permissions: Permission[];
    users: User[];
}

export default function RolesShow({
    role,
    permissions,
    users,
}: RolesShowProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Role: ${role.name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center gap-4">
                    <Link href="/roles">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Voltar
                        </Button>
                    </Link>
                    <div className="flex-1">
                        <h1 className="text-3xl font-bold">{role.name}</h1>
                        <p className="mt-1 text-muted-foreground">
                            Detalhes da role
                        </p>
                    </div>
                    <Can permission="roles.edit">
                        <Link href={`/roles/${role.id}/edit`}>
                            <Button variant="default" size="sm">
                                Editar
                            </Button>
                        </Link>
                    </Can>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Informações da Role</CardTitle>
                            <CardDescription>
                                Dados básicos da role
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">
                                    Nome
                                </label>
                                <p className="text-sm font-semibold">
                                    {role.name}
                                </p>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">
                                    Slug
                                </label>
                                <p className="font-mono text-sm">{role.slug}</p>
                            </div>
                            {role.description && (
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">
                                        Descrição
                                    </label>
                                    <p className="text-sm">
                                        {role.description}
                                    </p>
                                </div>
                            )}
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">
                                    Guard
                                </label>
                                <p className="text-sm">{role.guard_name}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Estatísticas</CardTitle>
                            <CardDescription>
                                Informações sobre a role
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">
                                    Total de Permissões
                                </label>
                                <p className="text-2xl font-bold">
                                    {permissions.length}
                                </p>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">
                                    Total de Usuários
                                </label>
                                <p className="text-2xl font-bold">
                                    {users.length}
                                </p>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">
                                    Criada em
                                </label>
                                <p className="text-sm">
                                    {new Date(role.created_at).toLocaleString(
                                        'pt-BR',
                                    )}
                                </p>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">
                                    Atualizada em
                                </label>
                                <p className="text-sm">
                                    {new Date(role.updated_at).toLocaleString(
                                        'pt-BR',
                                    )}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Permissões</CardTitle>
                        <CardDescription>
                            Lista de permissões atribuídas a esta role
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {permissions.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Nenhuma permissão atribuída a esta role.
                            </p>
                        ) : (
                            <div className="space-y-2">
                                {permissions.map((permission) => (
                                    <div
                                        key={permission.id}
                                        className="flex items-center justify-between rounded-lg border border-input p-3"
                                    >
                                        <div>
                                            <p className="text-sm font-semibold">
                                                {permission.name}
                                            </p>
                                            <p className="font-mono text-xs text-muted-foreground">
                                                {permission.slug}
                                            </p>
                                            {permission.description && (
                                                <p className="mt-1 text-xs text-muted-foreground">
                                                    {permission.description}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Usuários</CardTitle>
                        <CardDescription>
                            Lista de usuários que possuem esta role
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {users.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Nenhum usuário possui esta role.
                            </p>
                        ) : (
                            <div className="space-y-2">
                                {users.map((user) => (
                                    <div
                                        key={user.id}
                                        className="flex items-center justify-between rounded-lg border border-input p-3"
                                    >
                                        <div>
                                            <p className="text-sm font-semibold">
                                                {user.name}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                {user.email}
                                            </p>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <span
                                                className={`rounded px-2 py-1 text-xs ${
                                                    user.status === 1
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                }`}
                                            >
                                                {user.status === 1
                                                    ? 'Ativo'
                                                    : 'Inativo'}
                                            </span>
                                            <Can permission="users.view">
                                                <Link
                                                    href={`/users/${user.id}`}
                                                >
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                    >
                                                        Ver
                                                    </Button>
                                                </Link>
                                            </Can>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
