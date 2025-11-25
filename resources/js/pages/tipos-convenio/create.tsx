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
        title: 'Tipos de Convênio',
        href: '/tipos-convenio',
    },
    {
        title: 'Novo Tipo de Convênio',
        href: '/tipos-convenio/create',
    },
];

export default function TiposConvenioCreate() {
    const { data, setData, post, processing, errors } = useForm({
        tipo_descricao: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/tipos-convenio', {
            preserveScroll: true,
            onSuccess: () => {
                // Reset form on success
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Novo Tipo de Convênio" />
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
                            Novo Tipo de Convênio
                        </h1>
                        <p className="text-muted-foreground mt-1">
                            Preencha os dados para criar um novo tipo de convênio
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Dados do Tipo de Convênio</CardTitle>
                        <CardDescription>
                            Informe os dados necessários para criar o tipo de convênio
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="grid gap-2">
                                <Label htmlFor="tipo_descricao">
                                    Descrição <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="tipo_descricao"
                                    name="tipo_descricao"
                                    type="text"
                                    required
                                    value={data.tipo_descricao}
                                    onChange={(e) =>
                                        setData('tipo_descricao', e.target.value)
                                    }
                                    placeholder="Ex: Convênio Particular"
                                />
                                <InputError message={errors.tipo_descricao} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing
                                        ? 'Salvando...'
                                        : 'Criar Tipo de Convênio'}
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

