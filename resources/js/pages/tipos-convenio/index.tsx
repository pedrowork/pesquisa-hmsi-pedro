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
import Can from '@/components/Can';
import { useState, FormEvent } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Tipos de Convênio',
        href: '/tipos-convenio',
    },
];

interface TipoConvenio {
    cod: number;
    tipo_descricao: string | null;
}

interface PaginatedTiposConvenio {
    data: TipoConvenio[];
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

interface TiposConvenioIndexProps {
    tiposConvenio: PaginatedTiposConvenio;
    filters: {
        search: string;
    };
}

export default function TiposConvenioIndex({
    tiposConvenio,
    filters,
}: TiposConvenioIndexProps) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        router.get('/tipos-convenio', { search }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleDelete = (tipoConvenioId: number) => {
        if (confirm('Tem certeza que deseja excluir este tipo de convênio?')) {
            router.delete(`/tipos-convenio/${tipoConvenioId}`, {
                preserveScroll: true,
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tipos de Convênio" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">
                            Gerenciamento de Tipos de Convênio
                        </h1>
                        <p className="text-muted-foreground mt-1">
                            Cadastre e gerencie tipos de convênio do sistema
                        </p>
                    </div>
                    <Can permission="tipos-convenio.create">
                        <Link href="/tipos-convenio/create">
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                Novo Tipo de Convênio
                            </Button>
                        </Link>
                    </Can>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle>Filtros</CardTitle>
                        <CardDescription>Busque tipos de convênio</CardDescription>
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
                        <CardTitle>Lista de Tipos de Convênio</CardTitle>
                        <CardDescription>
                            Total: {tiposConvenio.total} tipo(s)
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
                                        <th className="px-4 py-3 text-right text-sm font-medium">
                                            Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {tiposConvenio.data.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan={3}
                                                className="px-4 py-8 text-center text-muted-foreground"
                                            >
                                                Nenhum tipo de convênio encontrado
                                            </td>
                                        </tr>
                                    ) : (
                                        tiposConvenio.data.map((tipoConvenio) => (
                                            <tr
                                                key={tipoConvenio.cod}
                                                className="border-b hover:bg-muted/50"
                                            >
                                                <td className="px-4 py-3 font-medium">
                                                    #{tipoConvenio.cod}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {tipoConvenio.tipo_descricao || '—'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="flex justify-end gap-2">
                                                        <Can permission="tipos-convenio.edit">
                                                            <Link
                                                                href={`/tipos-convenio/${tipoConvenio.cod}/edit`}
                                                            >
                                                                <Button
                                                                    variant="outline"
                                                                    size="sm"
                                                                >
                                                                    <Edit className="h-4 w-4" />
                                                                </Button>
                                                            </Link>
                                                        </Can>
                                                        <Can permission="tipos-convenio.delete">
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() =>
                                                                    handleDelete(
                                                                        tipoConvenio.cod
                                                                    )
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
                        {tiposConvenio.last_page > 1 && (
                            <div className="mt-4 flex items-center justify-between">
                                <div className="text-sm text-muted-foreground">
                                    Página {tiposConvenio.current_page} de{' '}
                                    {tiposConvenio.last_page}
                                </div>
                                <div className="flex gap-2">
                                    {tiposConvenio.links.map((link, index) => {
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

