import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Search, Edit, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useState, FormEvent } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Roles',
        href: '/roles',
    },
];

interface Role {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    permissions_count: number;
    users_count: number;
    created_at: string;
    updated_at: string;
}

interface PaginatedRoles {
    data: Role[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

interface RolesIndexProps {
    roles: PaginatedRoles;
    filters: {
        search: string;
    };
}

export default function RolesIndex({ roles, filters }: RolesIndexProps) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        router.get('/roles', { search }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleDelete = (roleId: number) => {
        if (confirm('Tem certeza que deseja excluir esta role? Todos os relacionamentos serão removidos.')) {
            router.delete(`/roles/${roleId}`, {
                preserveScroll: true,
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Roles" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">Gerenciamento de Roles</h1>
                        <p className="text-muted-foreground mt-1">
                            Cadastre e gerencie roles (grupos de usuários) do sistema
                        </p>
                    </div>
                    <Link href="/roles/create">
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Nova Role
                        </Button>
                    </Link>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle>Filtros</CardTitle>
                        <CardDescription>Busque roles</CardDescription>
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

                {/* Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Lista de Roles</CardTitle>
                        <CardDescription>
                            Total: {roles.total} role(s)
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b">
                                        <th className="px-4 py-3 text-left text-sm font-medium">
                                            Nome
                                        </th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">
                                            Slug
                                        </th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">
                                            Descrição
                                        </th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">
                                            Permissões
                                        </th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">
                                            Usuários
                                        </th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">
                                            Criado em
                                        </th>
                                        <th className="px-4 py-3 text-right text-sm font-medium">
                                            Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {roles.data.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan={7}
                                                className="px-4 py-8 text-center text-muted-foreground"
                                            >
                                                Nenhuma role encontrada
                                            </td>
                                        </tr>
                                    ) : (
                                        roles.data.map((role) => (
                                            <tr
                                                key={role.id}
                                                className="border-b hover:bg-muted/50"
                                            >
                                                <td className="px-4 py-3 font-medium">
                                                    {role.name}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <code className="text-xs bg-muted px-2 py-1 rounded">
                                                        {role.slug}
                                                    </code>
                                                </td>
                                                <td className="px-4 py-3 text-sm text-muted-foreground">
                                                    {role.description || '—'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <span className="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        {role.permissions_count}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <span className="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                        {role.users_count}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3 text-sm text-muted-foreground">
                                                    {new Date(
                                                        role.created_at,
                                                    ).toLocaleDateString('pt-BR')}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="flex justify-end gap-2">
                                                        <Link
                                                            href={`/roles/${role.id}/edit`}
                                                        >
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                            >
                                                                <Edit className="h-4 w-4" />
                                                            </Button>
                                                        </Link>
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() =>
                                                                handleDelete(role.id)
                                                            }
                                                            className="text-red-600 hover:text-red-700 dark:text-red-400"
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {roles.last_page > 1 && (
                            <div className="mt-4 flex items-center justify-between">
                                <div className="text-sm text-muted-foreground">
                                    Página {roles.current_page} de{' '}
                                    {roles.last_page}
                                </div>
                                <div className="flex gap-2">
                                    {roles.links.map((link, index) => {
                                        if (!link.url) {
                                            return (
                                                <span
                                                    key={index}
                                                    className="px-3 py-1 text-sm text-muted-foreground"
                                                    dangerouslySetInnerHTML={{
                                                        __html: link.label,
                                                    }}
                                                />
                                            );
                                        }

                                        return (
                                            <Link
                                                key={index}
                                                href={link.url}
                                                className={`px-3 py-1 rounded-md text-sm ${
                                                    link.active
                                                        ? 'bg-primary text-primary-foreground'
                                                        : 'bg-muted text-muted-foreground hover:bg-muted/80'
                                                }`}
                                                dangerouslySetInnerHTML={{
                                                    __html: link.label,
                                                }}
                                            />
                                        );
                                    })}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
