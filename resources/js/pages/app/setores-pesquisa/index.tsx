import Can from '@/components/Can';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Edit, Plus, Search, Trash2 } from 'lucide-react';
import { FormEvent, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Setores de Pesquisa',
        href: '/setores-pesquisa',
    },
];

interface SetorPesquisa {
    cod: number;
    descricao: string;
}

interface PaginatedSetoresPesquisa {
    data: SetorPesquisa[];
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

interface SetoresPesquisaIndexProps {
    setoresPesquisa: PaginatedSetoresPesquisa;
    filters: {
        search: string;
    };
}

export default function SetoresPesquisaIndex({
    setoresPesquisa,
    filters,
}: SetoresPesquisaIndexProps) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        router.get(
            '/setores-pesquisa',
            { search },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const handleDelete = (setorPesquisaId: number) => {
        if (confirm('Tem certeza que deseja excluir este setor de pesquisa?')) {
            router.delete(`/setores-pesquisa/${setorPesquisaId}`, {
                preserveScroll: true,
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Setores de Pesquisa" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">
                            Gerenciamento de Setores de Pesquisa
                        </h1>
                        <p className="mt-1 text-muted-foreground">
                            Cadastre e gerencie setores de pesquisa do sistema
                        </p>
                    </div>
                    <Can permission="setores-pesquisa.create">
                        <Link href="/setores-pesquisa/create">
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                Novo Setor de Pesquisa
                            </Button>
                        </Link>
                    </Can>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle>Filtros</CardTitle>
                        <CardDescription>
                            Busque setores de pesquisa
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
                        <CardTitle>Lista de Setores de Pesquisa</CardTitle>
                        <CardDescription>
                            Total: {setoresPesquisa.total} setor(es)
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
                                    {setoresPesquisa.data.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan={3}
                                                className="px-4 py-8 text-center text-muted-foreground"
                                            >
                                                Nenhum setor de pesquisa
                                                encontrado
                                            </td>
                                        </tr>
                                    ) : (
                                        setoresPesquisa.data.map(
                                            (setorPesquisa) => (
                                                <tr
                                                    key={setorPesquisa.cod}
                                                    className="border-b hover:bg-muted/50"
                                                >
                                                    <td className="px-4 py-3 font-medium">
                                                        #{setorPesquisa.cod}
                                                    </td>
                                                    <td className="px-4 py-3">
                                                        {
                                                            setorPesquisa.descricao
                                                        }
                                                    </td>
                                                    <td className="px-4 py-3">
                                                        <div className="flex justify-end gap-2">
                                                            <Can permission="setores-pesquisa.edit">
                                                                <Link
                                                                    href={`/setores-pesquisa/${setorPesquisa.cod}/edit`}
                                                                >
                                                                    <Button
                                                                        variant="outline"
                                                                        size="sm"
                                                                    >
                                                                        <Edit className="h-4 w-4" />
                                                                    </Button>
                                                                </Link>
                                                            </Can>
                                                            <Can permission="setores-pesquisa.delete">
                                                                <Button
                                                                    variant="outline"
                                                                    size="sm"
                                                                    onClick={() =>
                                                                        handleDelete(
                                                                            setorPesquisa.cod,
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
                                            ),
                                        )
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {setoresPesquisa.last_page > 1 && (
                            <div className="mt-4 flex items-center justify-between">
                                <div className="text-sm text-muted-foreground">
                                    Página {setoresPesquisa.current_page} de{' '}
                                    {setoresPesquisa.last_page}
                                </div>
                                <div className="flex gap-2">
                                    {setoresPesquisa.links.map(
                                        (link, index) => {
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
                                                    className={`rounded-md px-3 py-1 text-sm ${
                                                        link.active
                                                            ? 'bg-primary text-primary-foreground'
                                                            : 'bg-muted text-muted-foreground hover:bg-muted/80'
                                                    }`}
                                                    dangerouslySetInnerHTML={{
                                                        __html: link.label,
                                                    }}
                                                />
                                            );
                                        },
                                    )}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
