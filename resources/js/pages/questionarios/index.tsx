import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Search, Eye } from 'lucide-react';
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
    { title: 'Questionários', href: '/questionarios' },
];

interface Questionario {
    cod_paciente: number;
    nome: string;
    email: string;
    telefone: string;
    data_questionario: string;
    usuario_nome: string;
    total_respostas: number;
}

interface PaginatedQuestionarios {
    data: Questionario[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface QuestionariosIndexProps {
    questionarios: PaginatedQuestionarios;
    filters: { search: string };
}

export default function QuestionariosIndex({
    questionarios,
    filters,
}: QuestionariosIndexProps) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        router.get('/questionarios', { search }, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Questionários" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">Gerenciamento de Questionários</h1>
                        <p className="text-muted-foreground mt-1">
                            Visualize e gerencie questionários de pesquisa de satisfação
                        </p>
                    </div>
                    <Link href="/questionarios/create">
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Novo Questionário
                        </Button>
                    </Link>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Filtros</CardTitle>
                        <CardDescription>Busque questionários</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSearch} className="flex gap-4">
                            <div className="flex-1">
                                <Label htmlFor="search" className="sr-only">Buscar</Label>
                                <Input
                                    id="search"
                                    type="text"
                                    placeholder="Buscar por nome, email ou telefone do paciente..."
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
                        <CardTitle>Lista de Questionários</CardTitle>
                        <CardDescription>
                            Total: {questionarios.total} questionário(s)
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b">
                                        <th className="px-4 py-3 text-left text-sm font-medium">Paciente</th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">Email</th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">Telefone</th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">Data</th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">Usuário</th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">Respostas</th>
                                        <th className="px-4 py-3 text-right text-sm font-medium">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {questionarios.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="px-4 py-8 text-center text-muted-foreground">
                                                Nenhum questionário encontrado
                                            </td>
                                        </tr>
                                    ) : (
                                        questionarios.data.map((questionario) => (
                                            <tr key={questionario.cod_paciente} className="border-b hover:bg-muted/50">
                                                <td className="px-4 py-3 font-medium">{questionario.nome}</td>
                                                <td className="px-4 py-3">{questionario.email}</td>
                                                <td className="px-4 py-3">{questionario.telefone}</td>
                                                <td className="px-4 py-3 text-sm text-muted-foreground">
                                                    {questionario.data_questionario
                                                        ? new Date(questionario.data_questionario).toLocaleDateString('pt-BR')
                                                        : '—'}
                                                </td>
                                                <td className="px-4 py-3 text-sm text-muted-foreground">
                                                    {questionario.usuario_nome}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <span className="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary">
                                                        {questionario.total_respostas} resposta(s)
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="flex justify-end">
                                                        <Link href={`/questionarios/${questionario.cod_paciente}`}>
                                                            <Button variant="outline" size="sm">
                                                                <Eye className="mr-2 h-4 w-4" />
                                                                Ver
                                                            </Button>
                                                        </Link>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                        {questionarios.last_page > 1 && (
                            <div className="mt-4 flex items-center justify-between">
                                <div className="text-sm text-muted-foreground">
                                    Página {questionarios.current_page} de {questionarios.last_page}
                                </div>
                                <div className="flex gap-2">
                                    {questionarios.links.map((link, index) => {
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

