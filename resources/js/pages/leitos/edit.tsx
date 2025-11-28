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
        title: 'Leitos',
        href: '/leitos',
    },
    {
        title: 'Editar Leito',
        href: '/leitos/edit',
    },
];

interface Setor {
    cod: number;
    descricao: string;
}

interface Leito {
    cod: number;
    descricao: string;
    cod_setor: number | null;
}

interface LeitosEditProps {
    leito: Leito;
    setores: Setor[];
}

export default function LeitosEdit({
    leito,
    setores,
}: LeitosEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        descricao: leito.descricao || '',
        cod_setor: leito.cod_setor?.toString() || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/leitos/${leito.cod}`, {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Editar Leito" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center gap-4">
                    <Link href="/leitos">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Voltar
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold">Editar Leito</h1>
                        <p className="text-muted-foreground mt-1">
                            Edite os dados do leito {leito.descricao}
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Dados do Leito</CardTitle>
                        <CardDescription>
                            Atualize as informações do leito
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
                                    placeholder="Ex: Leito 101"
                                />
                                <InputError message={errors.descricao} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="cod_setor">
                                    Setor
                                </Label>
                                <select
                                    id="cod_setor"
                                    name="cod_setor"
                                    value={data.cod_setor}
                                    onChange={(e) =>
                                        setData('cod_setor', e.target.value)
                                    }
                                    className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">Selecione um setor</option>
                                    {setores.map((setor) => (
                                        <option key={setor.cod} value={setor.cod}>
                                            {setor.descricao}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.cod_setor} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing
                                        ? 'Salvando...'
                                        : 'Atualizar Leito'}
                                </Button>
                                <Link href="/leitos">
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

