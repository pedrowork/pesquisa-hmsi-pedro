import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

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
        title: 'Editar Setor de Pesquisa',
        href: '/setores-pesquisa/edit',
    },
];

interface SetorPesquisa {
    cod: number;
    descricao: string;
}

interface SetoresPesquisaEditProps {
    setorPesquisa: SetorPesquisa;
}

export default function SetoresPesquisaEdit({
    setorPesquisa,
}: SetoresPesquisaEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        descricao: setorPesquisa.descricao || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/setores-pesquisa/${setorPesquisa.cod}`, {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Editar Setor de Pesquisa" />
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
                            Editar Setor de Pesquisa
                        </h1>
                        <p className="text-muted-foreground mt-1">
                            Edite os dados do setor de pesquisa{' '}
                            {setorPesquisa.descricao}
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Dados do Setor de Pesquisa</CardTitle>
                        <CardDescription>
                            Atualize as informações do setor de pesquisa
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
                                        : 'Atualizar Setor de Pesquisa'}
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

