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
        title: 'Tipos de Convênio',
        href: '/tipos-convenio',
    },
    {
        title: 'Editar Tipo de Convênio',
        href: '/tipos-convenio/edit',
    },
];

interface TipoConvenio {
    cod: number;
    tipo_descricao: string | null;
}

interface TiposConvenioEditProps {
    tipoConvenio: TipoConvenio;
}

export default function TiposConvenioEdit({
    tipoConvenio,
}: TiposConvenioEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        tipo_descricao: tipoConvenio.tipo_descricao || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/tipos-convenio/${tipoConvenio.cod}`, {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Editar Tipo de Convênio" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center gap-4">
                    <Link href="/tipos-convenio">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Voltar
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold">
                            Editar Tipo de Convênio
                        </h1>
                        <p className="mt-1 text-muted-foreground">
                            Edite os dados do tipo de convênio
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Dados do Tipo de Convênio</CardTitle>
                        <CardDescription>
                            Atualize as informações do tipo de convênio
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="grid gap-2">
                                <Label htmlFor="tipo_descricao">
                                    Descrição{' '}
                                    <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="tipo_descricao"
                                    name="tipo_descricao"
                                    type="text"
                                    required
                                    value={data.tipo_descricao}
                                    onChange={(e) =>
                                        setData(
                                            'tipo_descricao',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="Ex: Convênio Particular"
                                />
                                <InputError message={errors.tipo_descricao} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing
                                        ? 'Salvando...'
                                        : 'Atualizar Tipo de Convênio'}
                                </Button>
                                <Link href="/tipos-convenio">
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
