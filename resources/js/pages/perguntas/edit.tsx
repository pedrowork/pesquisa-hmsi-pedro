import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Perguntas', href: '/perguntas' },
    { title: 'Editar Pergunta', href: '/perguntas/edit' },
];

interface SetorPesquisa {
    cod: number;
    descricao: string;
}

interface Pergunta {
    cod: number;
    descricao: string;
    cod_setor_pesquis: number | null;
    cod_tipo_pergunta: number | null;
    ativo: boolean;
    obrigatoria: boolean;
}

interface PerguntasEditProps {
    pergunta: Pergunta;
    setoresPesquisa: SetorPesquisa[];
}

export default function PerguntasEdit({ pergunta, setoresPesquisa }: PerguntasEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        descricao: pergunta.descricao || '',
        cod_setor_pesquis: pergunta.cod_setor_pesquis?.toString() || '',
        cod_tipo_pergunta: pergunta.cod_tipo_pergunta?.toString() || '',
        ativo: pergunta.ativo ?? true,
        obrigatoria: pergunta.obrigatoria ?? false,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/perguntas/${pergunta.cod}`, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Editar Pergunta" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center gap-4">
                    <Link href="/perguntas">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Voltar
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold">Editar Pergunta</h1>
                        <p className="text-muted-foreground mt-1">
                            Edite os dados da pergunta
                        </p>
                    </div>
                </div>
                <Card>
                    <CardHeader>
                        <CardTitle>Dados da Pergunta</CardTitle>
                        <CardDescription>
                            Atualize as informações da pergunta
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="grid gap-2">
                                <Label htmlFor="descricao">
                                    Descrição <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="descricao"
                                    name="descricao"
                                    type="text"
                                    required
                                    value={data.descricao}
                                    onChange={(e) => setData('descricao', e.target.value)}
                                    placeholder="Ex: Como você avalia o atendimento?"
                                    maxLength={255}
                                />
                                <InputError message={errors.descricao} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="cod_setor_pesquis">Setor de Pesquisa</Label>
                                <select
                                    id="cod_setor_pesquis"
                                    name="cod_setor_pesquis"
                                    value={data.cod_setor_pesquis}
                                    onChange={(e) => setData('cod_setor_pesquis', e.target.value)}
                                    className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">Selecione um setor de pesquisa</option>
                                    {setoresPesquisa.map((setor) => (
                                        <option key={setor.cod} value={setor.cod}>
                                            {setor.descricao}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.cod_setor_pesquis} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="cod_tipo_pergunta">Tipo de Resposta</Label>
                                <select
                                    id="cod_tipo_pergunta"
                                    name="cod_tipo_pergunta"
                                    value={data.cod_tipo_pergunta}
                                    onChange={(e) => setData('cod_tipo_pergunta', e.target.value)}
                                    className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">Selecione o tipo de resposta</option>
                                    <option value="1">Avaliativa (Ruim, Regular, Bom, Ótimo, Excelente)</option>
                                    <option value="2">Objetiva (Sim/Não)</option>
                                    <option value="3">Classificação (0-10)</option>
                                    <option value="4">Livre (texto)</option>
                                </select>
                                <InputError message={errors.cod_tipo_pergunta} />
                            </div>
                            <div className="flex items-center gap-2">
                                <Checkbox
                                    id="ativo"
                                    checked={data.ativo}
                                    onCheckedChange={(checked) => setData('ativo', checked === true)}
                                />
                                <Label htmlFor="ativo" className="cursor-pointer">
                                    Pergunta ativa
                                </Label>
                            </div>
                            <div className="flex items-center gap-2">
                                <Checkbox
                                    id="obrigatoria"
                                    checked={data.obrigatoria}
                                    onCheckedChange={(checked) => setData('obrigatoria', checked === true)}
                                />
                                <Label htmlFor="obrigatoria" className="cursor-pointer">
                                    Pergunta obrigatória
                                </Label>
                            </div>
                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Salvando...' : 'Atualizar Pergunta'}
                                </Button>
                                <Link href="/perguntas">
                                    <Button type="button" variant="outline">Cancelar</Button>
                                </Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

