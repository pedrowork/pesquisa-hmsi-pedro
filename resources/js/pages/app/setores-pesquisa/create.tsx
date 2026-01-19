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
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Setores de Pesquisa',
        href: '/setores-pesquisa',
    },
    {
        title: 'Novo Setor de Pesquisa',
        href: '/setores-pesquisa/create',
    },
];

export default function SetoresPesquisaCreate() {
    const { data, setData, post, processing, errors } = useForm({
        descricao: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/setores-pesquisa', {
            preserveScroll: true,
            onSuccess: () => {
                // Reset form on success
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Novo Setor de Pesquisa" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center gap-4">
                    <Link href="/setores-pesquisa">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Voltar
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold">
                            Novo Setor de Pesquisa
                        </h1>
                        <p className="mt-1 text-muted-foreground">
                            Preencha os dados para criar um novo setor de
                            pesquisa
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Dados do Setor de Pesquisa</CardTitle>
                        <CardDescription>
                            Informe os dados necessários para criar o setor de
                            pesquisa
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
                                    placeholder="Ex: Pesquisa de Satisfação Hospitalar"
                                    maxLength={128}
                                />
                                <InputError message={errors.descricao} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing
                                        ? 'Salvando...'
                                        : 'Criar Setor de Pesquisa'}
                                </Button>
                                <Link href="/setores-pesquisa">
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
