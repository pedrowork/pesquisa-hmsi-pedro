import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Satisfação', href: '/satisfacao' },
    { title: 'Editar Satisfação', href: '/satisfacao/edit' },
];

interface Satisfacao {
    cod: number;
    descricao: string;
    cod_tipo_pergunta: number | null;
}

interface SatisfacaoEditProps {
    satisfacao: Satisfacao;
}

export default function SatisfacaoEdit({ satisfacao }: SatisfacaoEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        descricao: satisfacao.descricao || '',
        cod_tipo_pergunta: satisfacao.cod_tipo_pergunta?.toString() || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/satisfacao/${satisfacao.cod}`, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Editar Satisfação" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center gap-4">
                    <Link href="/satisfacao">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Voltar
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold">Editar Satisfação</h1>
                        <p className="text-muted-foreground mt-1">Edite os dados da satisfação {satisfacao.descricao}</p>
                    </div>
                </div>
                <Card>
                    <CardHeader>
                        <CardTitle>Dados da Satisfação</CardTitle>
                        <CardDescription>Atualize as informações da satisfação</CardDescription>
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
                                    placeholder="Ex: Muito Satisfeito"
                                />
                                <InputError message={errors.descricao} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="cod_tipo_pergunta">Tipo de Pergunta</Label>
                                <Input
                                    id="cod_tipo_pergunta"
                                    name="cod_tipo_pergunta"
                                    type="number"
                                    value={data.cod_tipo_pergunta}
                                    onChange={(e) => setData('cod_tipo_pergunta', e.target.value)}
                                />
                                <InputError message={errors.cod_tipo_pergunta} />
                            </div>
                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Salvando...' : 'Atualizar Satisfação'}
                                </Button>
                                <Link href="/satisfacao">
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

