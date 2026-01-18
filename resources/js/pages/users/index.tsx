import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Plus, Search, Edit, Trash2, Filter, UserCheck, Power } from 'lucide-react';
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
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Select } from '@/components/ui/select';
import Can from '@/components/Can';
import { useState, FormEvent } from 'react';
import { AlertTriangle } from 'lucide-react';

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

interface Role {
    name: string;
    slug: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    status: number;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    roles?: Role[];
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
    firstMasterId?: number | null;
    currentUserId?: number | null;
    canModifyFirstMaster?: boolean;
}

export default function UsersIndex({ 
    users, 
    filters, 
    firstMasterId = null,
    currentUserId = null,
    canModifyFirstMaster = false,
}: UsersIndexProps) {
    const { auth } = usePage<SharedData>().props;
    const isAdmin = auth?.isAdmin || false;
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [userToDelete, setUserToDelete] = useState<number | null>(null);
    const [deleteError, setDeleteError] = useState<string | null>(null);

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        router.get('/users', { search, status }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleDeleteClick = (userId: number) => {
        setUserToDelete(userId);
        setDeleteError(null);
        setShowDeleteModal(true);
    };

    const handleDeleteConfirm = () => {
        if (userToDelete) {
            router.delete(`/users/${userToDelete}`, {
                preserveScroll: true,
                onSuccess: () => {
                    setShowDeleteModal(false);
                    setUserToDelete(null);
                    setDeleteError(null);
                },
                onError: (errors) => {
                    // Capturar erros de validação ou outros erros
                    const errorMessage = errors.user || errors.message || 'Erro ao excluir usuário. Tente novamente.';
                    setDeleteError(errorMessage);
                },
            });
        }
    };

    const handleToggleStatus = (userId: number) => {
        router.post(`/users/${userId}/toggle-status`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['users'] });
            },
        });
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
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold sm:text-3xl">Gerenciamento de Usuários</h1>
                        <p className="text-muted-foreground mt-1 text-sm sm:text-base">
                            Cadastre e gerencie usuários do sistema
                        </p>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <Can permission="users.approve">
                            <Link href="/admin/users/pending-approval">
                                <Button variant="outline">
                                    <UserCheck className="mr-2 h-4 w-4" />
                                    Aprovações Pendentes
                                </Button>
                            </Link>
                        </Can>
                    <Can permission="users.create">
                        <Link href="/users/create">
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                Novo Usuário
                            </Button>
                        </Link>
                    </Can>
                    </div>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle>Filtros</CardTitle>
                        <CardDescription>Busque e filtre usuários</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSearch} className="flex flex-col gap-4 sm:flex-row">
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
                            <div className="w-full sm:w-48">
                                <Label htmlFor="status" className="sr-only">
                                    Status
                                </Label>
                                <select
                                    id="status"
                                    className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                    value={status}
                                    onChange={(e) => setStatus(e.target.value)}
                                >
                                    <option value="">Todos</option>
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                            </div>
                            <Button type="submit" variant="outline" className="w-full sm:w-auto">
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
                                            Cargo
                                        </th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">
                                            Status
                                        </th>
                                        {isAdmin && (
                                            <th className="px-4 py-3 text-left text-sm font-medium">
                                                Ativar/Desativar
                                            </th>
                                        )}
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
                                                colSpan={isAdmin ? 8 : 7}
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
                                                    {user.roles && user.roles.length > 0 ? (
                                                        <div className="flex flex-wrap gap-1">
                                                            {user.roles.map((role, index) => (
                                                                <span
                                                                    key={index}
                                                                    className="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                                                >
                                                                    {role.name}
                                                                </span>
                                                            ))}
                                                        </div>
                                                    ) : (
                                                        <span className="text-muted-foreground text-sm">
                                                            Sem cargo
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {getStatusBadge(user.status)}
                                                </td>
                                                {isAdmin && (
                                                    <td className="px-4 py-3">
                                                        {(() => {
                                                            // Verificar se é o próprio admin (não pode desativar a si mesmo)
                                                            const adminId = user.roles?.some((role: Role) => role.slug === 'admin');
                                                            const isCurrentUserAdmin = currentUserId && user.id === currentUserId;
                                                            // Não pode desativar: próprio admin
                                                            const canToggle = !isCurrentUserAdmin;
                                                            
                                                            return (
                                                                <Button
                                                                    variant="outline"
                                                                    size="sm"
                                                                    onClick={() => handleToggleStatus(user.id)}
                                                                    disabled={!canToggle}
                                                                    title={
                                                                        isCurrentUserAdmin
                                                                            ? 'Você não pode desativar sua própria conta'
                                                                            : (user.status === 1 ? 'Desativar usuário' : 'Ativar usuário')
                                                                    }
                                                                    className={user.status === 1 
                                                                        ? 'text-yellow-600 hover:text-yellow-700 dark:text-yellow-400'
                                                                        : 'text-green-600 hover:text-green-700 dark:text-green-400'
                                                                    }
                                                                >
                                                                    <Power className={`h-4 w-4 ${user.status === 1 ? '' : 'opacity-50'}`} />
                                                                </Button>
                                                            );
                                                        })()}
                                                    </td>
                                                )}
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
                                                        {(() => {
                                                            const isFirstMaster = firstMasterId && user.id === firstMasterId;
                                                            const canEdit = !isFirstMaster || canModifyFirstMaster;
                                                            const canDelete = !isFirstMaster; // Primeiro Master nunca pode ser deletado
                                                            
                                                            return (
                                                                <>
                                                                    <Can permission="users.edit">
                                                                        {canEdit ? (
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
                                                                        ) : (
                                                                            <Button
                                                                                variant="outline"
                                                                                size="sm"
                                                                                disabled
                                                                                title="O primeiro Master não pode ser editado por outros usuários"
                                                                            >
                                                                                <Edit className="h-4 w-4 opacity-50" />
                                                                            </Button>
                                                                        )}
                                                                    </Can>
                                                                    <Can permission="users.delete">
                                                                        {canDelete ? (
                                                                            <Button
                                                                                variant="outline"
                                                                                size="sm"
                                                                                onClick={() =>
                                                                                    handleDeleteClick(user.id)
                                                                                }
                                                                                className="text-red-600 hover:text-red-700 dark:text-red-400"
                                                                            >
                                                                                <Trash2 className="h-4 w-4" />
                                                                            </Button>
                                                                        ) : (
                                                                            <Button
                                                                                variant="outline"
                                                                                size="sm"
                                                                                disabled
                                                                                title="O primeiro Master não pode ser removido"
                                                                                className="opacity-50"
                                                                            >
                                                                                <Trash2 className="h-4 w-4" />
                                                                            </Button>
                                                                        )}
                                                                    </Can>
                                                                </>
                                                            );
                                                        })()}
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

            {/* Modal de Confirmação de Exclusão */}
            <Dialog open={showDeleteModal} onOpenChange={setShowDeleteModal}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <div className="flex items-center justify-center mb-4">
                            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/30">
                                <AlertTriangle className="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                            </div>
                        </div>
                        <DialogTitle className="text-center">
                            Confirmar Exclusão
                        </DialogTitle>
                        <DialogDescription className="text-center">
                            {deleteError ? (
                                <span className="text-red-600 dark:text-red-400 font-medium">
                                    {deleteError}
                                </span>
                            ) : (
                                'Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.'
                            )}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter className="sm:justify-center gap-2">
                        <Button
                            variant="outline"
                            onClick={() => {
                                setShowDeleteModal(false);
                                setUserToDelete(null);
                                setDeleteError(null);
                            }}
                        >
                            {deleteError ? 'Fechar' : 'Cancelar'}
                        </Button>
                        {!deleteError && (
                            <Button
                                variant="destructive"
                                onClick={handleDeleteConfirm}
                            >
                                Excluir
                            </Button>
                        )}
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
