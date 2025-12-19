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
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Satisfação', href: '/satisfacao' },
];

interface Satisfacao {
    cod: number;
    descricao: string;
    cod_tipo_pergunta: number | null;
}

interface PaginatedSatisfacoes {
    data: Satisfacao[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface SatisfacaoIndexProps {
    satisfacoes: PaginatedSatisfacoes;
    filters: { search: string };
}

export default function SatisfacaoIndex({
    satisfacoes,
    filters,
}: SatisfacaoIndexProps) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        router.get('/satisfacao', { search }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleDelete = (satisfacaoId: number) => {
        if (confirm('Tem certeza que deseja excluir esta satisfação?')) {
            router.delete(`/satisfacao/${satisfacaoId}`, {
                preserveScroll: true,
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Satisfação" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">Gerenciamento de Satisfação</h1>
                        <p className="text-muted-foreground mt-1">
                            Cadastre e gerencie níveis de satisfação do sistema
                        </p>
                    </div>
                    <Can permission="satisfacao.create">
                        <Link href="/satisfacao/create">
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                Nova Satisfação
                            </Button>
                        </Link>
                    </Can>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Filtros</CardTitle>
                        <CardDescription>Busque satisfações</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSearch} className="flex gap-4">
                            <div className="flex-1">
                                <Label htmlFor="search" className="sr-only">Buscar</Label>
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

                <Card>
                    <CardHeader>
                        <CardTitle>Lista de Satisfações</CardTitle>
                        <CardDescription>Total: {satisfacoes.total} satisfação(ões)</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b">
                                        <th className="px-4 py-3 text-left text-sm font-medium">Código</th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">Descrição</th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">Tipo Pergunta</th>
                                        <th className="px-4 py-3 text-right text-sm font-medium">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {satisfacoes.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={4} className="px-4 py-8 text-center text-muted-foreground">
                                                Nenhuma satisfação encontrada
                                            </td>
                                        </tr>
                                    ) : (
                                        satisfacoes.data.map((satisfacao) => (
                                            <tr key={satisfacao.cod} className="border-b hover:bg-muted/50">
                                                <td className="px-4 py-3 font-medium">#{satisfacao.cod}</td>
                                                <td className="px-4 py-3">{satisfacao.descricao}</td>
                                                <td className="px-4 py-3 text-sm text-muted-foreground">
                                                    {satisfacao.cod_tipo_pergunta || '—'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="flex justify-end gap-2">
                                                        <Can permission="satisfacao.edit">
                                                            <Link href={`/satisfacao/${satisfacao.cod}/edit`}>
                                                                <Button variant="outline" size="sm">
                                                                    <Edit className="h-4 w-4" />
                                                                </Button>
                                                            </Link>
                                                        </Can>
                                                        <Can permission="satisfacao.delete">
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() => handleDelete(satisfacao.cod)}
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
                        {satisfacoes.last_page > 1 && (
                            <div className="mt-4 flex items-center justify-between">
                                <div className="text-sm text-muted-foreground">
                                    Página {satisfacoes.current_page} de {satisfacoes.last_page}
                                </div>
                                <div className="flex gap-2">
                                    {satisfacoes.links.map((link, index) => {
                                        if (!link.url) {
                                            return (
                                                <span
                                                    key={index}
                                                    className="px-3 py-1 text-sm text-muted-foreground"
                                                    dangerouslySetInnerHTML={{ __html: link.label }}
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
                                                dangerouslySetInnerHTML={{ __html: link.label }}
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

