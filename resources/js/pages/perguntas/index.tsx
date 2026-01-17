import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Plus, Search, Edit, Trash2, AlertCircle, ArrowUpDown, Save, ChevronUp, ChevronDown } from 'lucide-react';
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
import { useState, FormEvent, useEffect } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Perguntas', href: '/perguntas' },
];

interface Pergunta {
    cod: number;
    descricao: string;
    cod_setor_pesquis: number | null;
    cod_tipo_pergunta: number | null;
    ativo: boolean;
    total_pesquisas: number;
    ordem: number | null;
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
    canOrder?: boolean;
}

export default function PerguntasIndex({
    perguntas,
    filters,
    canOrder = false,
}: PerguntasIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [isOrdering, setIsOrdering] = useState(false);
    const [orderValues, setOrderValues] = useState<Record<number, number>>({});
    const { flash } = usePage().props as { flash?: { success?: string; warning?: string; error?: string } };

    // Debug: verificar valor de canOrder (remover em produção)
    useEffect(() => {
        if (typeof window !== 'undefined' && process.env.NODE_ENV === 'development') {
            console.log('🔍 canOrder:', canOrder);
        }
    }, [canOrder]);

    // Inicializar valores de ordem quando perguntas mudarem
    useEffect(() => {
        if (isOrdering) {
            const initialOrder: Record<number, number> = {};
            perguntas.data.forEach((p, index) => {
                initialOrder[p.cod] = p.ordem ?? index + 1;
            });
            setOrderValues(initialOrder);
        }
    }, [perguntas.data, isOrdering]);

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        router.get('/perguntas', { search }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleMoveUp = (index: number) => {
        if (index === 0) return;
        const newOrder = { ...orderValues };
        const currentPergunta = perguntas.data[index];
        const previousPergunta = perguntas.data[index - 1];
        // Trocar valores de ordem
        const currentOrder = newOrder[currentPergunta.cod] ?? currentPergunta.ordem ?? index + 1;
        const previousOrder = newOrder[previousPergunta.cod] ?? previousPergunta.ordem ?? index;
        newOrder[currentPergunta.cod] = previousOrder;
        newOrder[previousPergunta.cod] = currentOrder;
        setOrderValues(newOrder);
    };

    const handleMoveDown = (index: number) => {
        if (index === perguntas.data.length - 1) return;
        const newOrder = { ...orderValues };
        const currentPergunta = perguntas.data[index];
        const nextPergunta = perguntas.data[index + 1];
        // Trocar valores de ordem
        const currentOrder = newOrder[currentPergunta.cod] ?? currentPergunta.ordem ?? index + 1;
        const nextOrder = newOrder[nextPergunta.cod] ?? nextPergunta.ordem ?? index + 2;
        newOrder[currentPergunta.cod] = nextOrder;
        newOrder[nextPergunta.cod] = currentOrder;
        setOrderValues(newOrder);
    };

    const handleSaveOrder = () => {
        const ordem = perguntas.data.map((p) => ({
            id: p.cod,
            ordem: orderValues[p.cod] || p.ordem || 0,
        }));

        router.post(
            '/perguntas/update-order',
            { ordem },
            {
                onSuccess: () => {
                    setIsOrdering(false);
                    router.reload({ only: ['perguntas'] });
                },
                onError: (errors) => {
                    alert('Erro ao salvar ordem: ' + JSON.stringify(errors));
                },
            }
        );
    };

    const handleDelete = (perguntaId: number, totalPesquisas: number) => {
        if (totalPesquisas > 0) {
            const message = `Esta pergunta possui ${totalPesquisas} pesquisa(s) associada(s). Para manter o histórico, a pergunta será apenas desativada ao invés de excluída. Deseja continuar?`;
            if (confirm(message)) {
                router.delete(`/perguntas/${perguntaId}`, {
                    preserveScroll: true,
                });
            }
        } else {
            if (confirm('Tem certeza que deseja excluir esta pergunta?')) {
                router.delete(`/perguntas/${perguntaId}`, {
                    preserveScroll: true,
                });
            }
        }
    };

    // Exibir mensagens flash
    useEffect(() => {
        if (flash?.success) {
            alert(flash.success);
        } else if (flash?.warning) {
            alert(flash.warning);
        } else if (flash?.error) {
            alert(flash.error);
        }
    }, [flash]);

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
                    <div className="flex gap-2">
                        {canOrder && (
                            <Button
                                variant={isOrdering ? 'default' : 'outline'}
                                onClick={() => {
                                    if (isOrdering) {
                                        handleSaveOrder();
                                    } else {
                                        setIsOrdering(true);
                                    }
                                }}
                            >
                                {isOrdering ? (
                                    <>
                                        <Save className="mr-2 h-4 w-4" />
                                        Salvar Ordem
                                    </>
                                ) : (
                                    <>
                                        <ArrowUpDown className="mr-2 h-4 w-4" />
                                        Ordenar
                                    </>
                                )}
                            </Button>
                        )}
                        <Can permission="perguntas.create">
                            <Link href="/perguntas/create">
                                <Button>
                                    <Plus className="mr-2 h-4 w-4" />
                                    Nova Pergunta
                                </Button>
                            </Link>
                        </Can>
                    </div>
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
                                        {canOrder && isOrdering && (
                                            <th className="px-4 py-3 text-left text-sm font-medium">Ordem</th>
                                        )}
                                        <th className="px-4 py-3 text-left text-sm font-medium">Código</th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">Descrição</th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">Status</th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">Pesquisas</th>
                                        <th className="px-4 py-3 text-right text-sm font-medium">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {perguntas.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={canOrder && isOrdering ? 6 : 5} className="px-4 py-8 text-center text-muted-foreground">
                                                Nenhuma pergunta encontrada
                                            </td>
                                        </tr>
                                    ) : (
                                        perguntas.data.map((pergunta) => (
                                            <tr 
                                                key={pergunta.cod} 
                                                className={`border-b hover:bg-muted/50 ${
                                                    pergunta.ativo === false || pergunta.ativo === 0 
                                                        ? 'bg-red-50/50 dark:bg-red-950/20' 
                                                        : ''
                                                }`}
                                            >
                                                {canOrder && isOrdering && (
                                                    <td className="px-4 py-3">
                                                        <div className="flex items-center gap-2">
                                                            <Input
                                                                type="number"
                                                                min="1"
                                                                className="w-20"
                                                                value={orderValues[pergunta.cod] ?? pergunta.ordem ?? ''}
                                                                onChange={(e) => {
                                                                    const value = parseInt(e.target.value) || 1;
                                                                    setOrderValues({
                                                                        ...orderValues,
                                                                        [pergunta.cod]: value,
                                                                    });
                                                                }}
                                                            />
                                                            <div className="flex flex-col gap-1">
                                                                <Button
                                                                    type="button"
                                                                    variant="outline"
                                                                    size="sm"
                                                                    className="h-6 w-6 p-0"
                                                                    onClick={() => {
                                                                        const currentIndex = perguntas.data.findIndex(p => p.cod === pergunta.cod);
                                                                        handleMoveUp(currentIndex);
                                                                    }}
                                                                    disabled={perguntas.data.findIndex(p => p.cod === pergunta.cod) === 0}
                                                                    title="Mover para cima"
                                                                >
                                                                    <ChevronUp className="h-3 w-3" />
                                                                </Button>
                                                                <Button
                                                                    type="button"
                                                                    variant="outline"
                                                                    size="sm"
                                                                    className="h-6 w-6 p-0"
                                                                    onClick={() => {
                                                                        const currentIndex = perguntas.data.findIndex(p => p.cod === pergunta.cod);
                                                                        handleMoveDown(currentIndex);
                                                                    }}
                                                                    disabled={perguntas.data.findIndex(p => p.cod === pergunta.cod) === perguntas.data.length - 1}
                                                                    title="Mover para baixo"
                                                                >
                                                                    <ChevronDown className="h-3 w-3" />
                                                                </Button>
                                                            </div>
                                                        </div>
                                                    </td>
                                                )}
                                                <td className="px-4 py-3 font-medium">#{pergunta.cod}</td>
                                                <td className={`px-4 py-3 ${
                                                    pergunta.ativo === false || pergunta.ativo === 0 
                                                        ? 'text-red-600 dark:text-red-400' 
                                                        : ''
                                                }`}>
                                                    {pergunta.descricao}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {pergunta.ativo === false || pergunta.ativo === 0 ? (
                                                        <span className="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                                            Desativada
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                            Ativa
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3 text-sm text-muted-foreground">
                                                    {pergunta.total_pesquisas || 0} pesquisa(s)
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="flex justify-end gap-2">
                                                        <Can permission="perguntas.edit">
                                                            <Link href={`/perguntas/${pergunta.cod}/edit`}>
                                                                <Button variant="outline" size="sm">
                                                                    <Edit className="h-4 w-4" />
                                                                </Button>
                                                            </Link>
                                                        </Can>
                                                        <Can permission="perguntas.delete">
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() => handleDelete(pergunta.cod, pergunta.total_pesquisas || 0)}
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

