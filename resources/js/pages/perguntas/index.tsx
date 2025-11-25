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
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Perguntas', href: '/perguntas' },
];

interface Pergunta {
    cod: number;
    descricao: string;
    cod_setor_pesquis: number | null;
    cod_tipo_pergunta: number | null;
}

interface PaginatedPerguntas {
    data: Pergunta[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface PerguntasIndexProps {
    perguntas: PaginatedPerguntas;
    filters: { search: string };
}

export default function PerguntasIndex({
    perguntas,
    filters,
}: PerguntasIndexProps) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        router.get('/perguntas', { search }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleDelete = (perguntaId: number) => {
        if (confirm('Tem certeza que deseja excluir esta pergunta?')) {
            router.delete(`/perguntas/${perguntaId}`, {
                preserveScroll: true,
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Perguntas" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">Gerenciamento de Perguntas</h1>
                        <p className="text-muted-foreground mt-1">
                            Cadastre e gerencie perguntas do sistema
                        </p>
                    </div>
                    <Link href="/perguntas/create">
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Nova Pergunta
                        </Button>
                    </Link>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Filtros</CardTitle>
                        <CardDescription>Busque perguntas</CardDescription>
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
                        <CardTitle>Lista de Perguntas</CardTitle>
                        <CardDescription>Total: {perguntas.total} pergunta(s)</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b">
                                        <th className="px-4 py-3 text-left text-sm font-medium">Código</th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">Descrição</th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">Setor Pesquisa</th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">Tipo Pergunta</th>
                                        <th className="px-4 py-3 text-right text-sm font-medium">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {perguntas.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={5} className="px-4 py-8 text-center text-muted-foreground">
                                                Nenhuma pergunta encontrada
                                            </td>
                                        </tr>
                                    ) : (
                                        perguntas.data.map((pergunta) => (
                                            <tr key={pergunta.cod} className="border-b hover:bg-muted/50">
                                                <td className="px-4 py-3 font-medium">#{pergunta.cod}</td>
                                                <td className="px-4 py-3">{pergunta.descricao}</td>
                                                <td className="px-4 py-3 text-sm text-muted-foreground">
                                                    {pergunta.cod_setor_pesquis || '—'}
                                                </td>
                                                <td className="px-4 py-3 text-sm text-muted-foreground">
                                                    {pergunta.cod_tipo_pergunta || '—'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="flex justify-end gap-2">
                                                        <Link href={`/perguntas/${pergunta.cod}/edit`}>
                                                            <Button variant="outline" size="sm">
                                                                <Edit className="h-4 w-4" />
                                                            </Button>
                                                        </Link>
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() => handleDelete(pergunta.cod)}
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
                        {perguntas.last_page > 1 && (
                            <div className="mt-4 flex items-center justify-between">
                                <div className="text-sm text-muted-foreground">
                                    Página {perguntas.current_page} de {perguntas.last_page}
                                </div>
                                <div className="flex gap-2">
                                    {perguntas.links.map((link, index) => {
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

