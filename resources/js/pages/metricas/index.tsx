import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart3, Users, Heart, ClipboardList } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Métricas', href: '/metricas' },
];

interface MetricasIndexProps {
    stats: {
        totalQuestionarios: number;
        totalPacientes: number;
        totalSatisfacoes: number;
    };
}

export default function MetricasIndex({ stats }: MetricasIndexProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Métricas" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div>
                    <h1 className="text-3xl font-bold">Métricas de Pesquisa</h1>
                    <p className="text-muted-foreground mt-1">
                        Visualize estatísticas e métricas do sistema de pesquisa
                    </p>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle className="text-lg">Total de Questionários</CardTitle>
                                <ClipboardList className="h-5 w-5 text-muted-foreground" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold">{stats.totalQuestionarios}</div>
                            <CardDescription className="mt-2">
                                Questionários registrados no sistema
                            </CardDescription>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle className="text-lg">Total de Pacientes</CardTitle>
                                <Users className="h-5 w-5 text-muted-foreground" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold">{stats.totalPacientes}</div>
                            <CardDescription className="mt-2">
                                Pacientes cadastrados no sistema
                            </CardDescription>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle className="text-lg">Total de Satisfações</CardTitle>
                                <Heart className="h-5 w-5 text-muted-foreground" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold">{stats.totalSatisfacoes}</div>
                            <CardDescription className="mt-2">
                                Níveis de satisfação cadastrados
                            </CardDescription>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Informações</CardTitle>
                        <CardDescription>Métricas em desenvolvimento</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p className="text-muted-foreground">
                            Esta página está em desenvolvimento. Mais métricas e gráficos serão adicionados em breve.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

