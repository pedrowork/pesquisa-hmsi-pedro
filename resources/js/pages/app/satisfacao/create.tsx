import InputError from '@/components/input-error';
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
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Satisfação', href: '/satisfacao' },
    { title: 'Nova Satisfação', href: '/satisfacao/create' },
];

export default function SatisfacaoCreate() {
    const { data, setData, post, processing, errors } = useForm({
        descricao: '',
        cod_tipo_pergunta: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/satisfacao', { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Nova Satisfação" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center gap-4">
                    <Link href="/satisfacao">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Voltar
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold">Nova Satisfação</h1>
                        <p className="mt-1 text-muted-foreground">
                            Preencha os dados para criar uma nova satisfação
                        </p>
                    </div>
                </div>
                <Card>
                    <CardHeader>
                        <CardTitle>Dados da Satisfação</CardTitle>
                        <CardDescription>
                            Informe os dados necessários para criar a satisfação
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="grid gap-2">
                                <Label htmlFor="descricao">
                                    Descrição{' '}
                                    <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="descricao"
                                    name="descricao"
                                    type="text"
                                    required
                                    value={data.descricao}
                                    onChange={(e) =>
                                        setData('descricao', e.target.value)
                                    }
                                    placeholder="Ex: Muito Satisfeito"
                                />
                                <InputError message={errors.descricao} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="cod_tipo_pergunta">
                                    Tipo de Resposta
                                </Label>
                                <select
                                    id="cod_tipo_pergunta"
                                    name="cod_tipo_pergunta"
                                    value={data.cod_tipo_pergunta}
                                    onChange={(e) =>
                                        setData(
                                            'cod_tipo_pergunta',
                                            e.target.value,
                                        )
                                    }
                                    className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">
                                        Selecione o tipo de resposta
                                    </option>
                                    <option value="1">
                                        Avaliativa (Ruim, Regular, Bom, Ótimo,
                                        Excelente)
                                    </option>
                                    <option value="2">
                                        Objetiva (Sim/Não)
                                    </option>
                                    <option value="3">
                                        Classificação (0-10)
                                    </option>
                                    <option value="4">Livre (texto)</option>
                                </select>
                                <InputError
                                    message={errors.cod_tipo_pergunta}
                                />
                            </div>
                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing
                                        ? 'Salvando...'
                                        : 'Criar Satisfação'}
                                </Button>
                                <Link href="/satisfacao">
                                    <Button type="button" variant="outline">
                                        Cancelar
                                    </Button>
                                </Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
