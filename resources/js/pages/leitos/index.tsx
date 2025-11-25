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
        title: 'Leitos',
        href: '/leitos',
    },
];

interface Leito {
    cod: number;
    descricao: string;
    cod_setor: number | null;
}

interface PaginatedLeitos {
    data: Leito[];
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

interface LeitosIndexProps {
    leitos: PaginatedLeitos;
    filters: {
        search: string;
    };
}

export default function LeitosIndex({ leitos, filters }: LeitosIndexProps) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        router.get('/leitos', { search }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleDelete = (leitoId: number) => {
        if (confirm('Tem certeza que deseja excluir este leito?')) {
            router.delete(`/leitos/${leitoId}`, {
                preserveScroll: true,
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Leitos" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">Gerenciamento de Leitos</h1>
                        <p className="text-muted-foreground mt-1">
                            Cadastre e gerencie leitos do sistema
                        </p>
                    </div>
                    <Link href="/leitos/create">
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Novo Leito
                        </Button>
                    </Link>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle>Filtros</CardTitle>
                        <CardDescription>Busque leitos</CardDescription>
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
                                    placeholder="Buscar por descrição..."
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
                        <CardTitle>Lista de Leitos</CardTitle>
                        <CardDescription>
                            Total: {leitos.total} leito(s)
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b">
                                        <th className="px-4 py-3 text-left text-sm font-medium">
                                            Código
                                        </th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">
                                            Descrição
                                        </th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">
                                            Setor
                                        </th>
                                        <th className="px-4 py-3 text-right text-sm font-medium">
                                            Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {leitos.data.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan={4}
                                                className="px-4 py-8 text-center text-muted-foreground"
                                            >
                                                Nenhum leito encontrado
                                            </td>
                                        </tr>
                                    ) : (
                                        leitos.data.map((leito) => (
                                            <tr
                                                key={leito.cod}
                                                className="border-b hover:bg-muted/50"
                                            >
                                                <td className="px-4 py-3 font-medium">
                                                    #{leito.cod}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {leito.descricao}
                                                </td>
                                                <td className="px-4 py-3 text-sm text-muted-foreground">
                                                    {leito.cod_setor || '—'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="flex justify-end gap-2">
                                                        <Link
                                                            href={`/leitos/${leito.cod}/edit`}
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
                                                                handleDelete(leito.cod)
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
                        {leitos.last_page > 1 && (
                            <div className="mt-4 flex items-center justify-between">
                                <div className="text-sm text-muted-foreground">
                                    Página {leitos.current_page} de{' '}
                                    {leitos.last_page}
                                </div>
                                <div className="flex gap-2">
                                    {leitos.links.map((link, index) => {
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

