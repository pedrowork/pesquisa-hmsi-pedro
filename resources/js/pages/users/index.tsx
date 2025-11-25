import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Search, Edit, Trash2, Filter } from 'lucide-react';
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
import { Select } from '@/components/ui/select';
import Can from '@/components/Can';
import { useState, FormEvent } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Usuários',
        href: '/users',
    },
];

interface User {
    id: number;
    name: string;
    email: string;
    status: number;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

interface PaginatedUsers {
    data: User[];
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

interface UsersIndexProps {
    users: PaginatedUsers;
    filters: {
        search: string;
        status: string;
    };
}

export default function UsersIndex({ users, filters }: UsersIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        router.get('/users', { search, status }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleDelete = (userId: number) => {
        if (confirm('Tem certeza que deseja excluir este usuário?')) {
            router.delete(`/users/${userId}`, {
                preserveScroll: true,
            });
        }
    };

    const getStatusBadge = (status: number) => {
        return status === 1 ? (
            <span className="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                Ativo
            </span>
        ) : (
            <span className="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200">
                Inativo
            </span>
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Usuários" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">Gerenciamento de Usuários</h1>
                        <p className="text-muted-foreground mt-1">
                            Cadastre e gerencie usuários do sistema
                        </p>
                    </div>
                    <Can permission="users.create">
                        <Link href="/users/create">
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                Novo Usuário
                            </Button>
                        </Link>
                    </Can>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle>Filtros</CardTitle>
                        <CardDescription>Busque e filtre usuários</CardDescription>
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
                                    placeholder="Buscar por nome ou email..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                />
                            </div>
                            <div className="w-48">
                                <Label htmlFor="status" className="sr-only">
                                    Status
                                </Label>
                                <select
                                    id="status"
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                    value={status}
                                    onChange={(e) => setStatus(e.target.value)}
                                >
                                    <option value="">Todos</option>
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
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
                        <CardTitle>Lista de Usuários</CardTitle>
                        <CardDescription>
                            Total: {users.total} usuário(s)
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
                                            Email
                                        </th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">
                                            Status
                                        </th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">
                                            Verificado
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
                                    {users.data.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan={6}
                                                className="px-4 py-8 text-center text-muted-foreground"
                                            >
                                                Nenhum usuário encontrado
                                            </td>
                                        </tr>
                                    ) : (
                                        users.data.map((user) => (
                                            <tr
                                                key={user.id}
                                                className="border-b hover:bg-muted/50"
                                            >
                                                <td className="px-4 py-3">
                                                    {user.name}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {user.email}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {getStatusBadge(user.status)}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {user.email_verified_at ? (
                                                        <span className="text-green-600 dark:text-green-400">
                                                            Sim
                                                        </span>
                                                    ) : (
                                                        <span className="text-muted-foreground">
                                                            Não
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3 text-sm text-muted-foreground">
                                                    {new Date(
                                                        user.created_at,
                                                    ).toLocaleDateString('pt-BR')}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="flex justify-end gap-2">
                                                        <Can permission="users.edit">
                                                            <Link
                                                                href={`/users/${user.id}/edit`}
                                                            >
                                                                <Button
                                                                    variant="outline"
                                                                    size="sm"
                                                                >
                                                                    <Edit className="h-4 w-4" />
                                                                </Button>
                                                            </Link>
                                                        </Can>
                                                        <Can permission="users.delete">
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() =>
                                                                    handleDelete(user.id)
                                                                }
                                                                className="text-red-600 hover:text-red-700 dark:text-red-400"
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                            </Button>
                                                        </Can>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {users.last_page > 1 && (
                            <div className="mt-4 flex items-center justify-between">
                                <div className="text-sm text-muted-foreground">
                                    Página {users.current_page} de{' '}
                                    {users.last_page}
                                </div>
                                <div className="flex gap-2">
                                    {users.links.map((link, index) => {
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
