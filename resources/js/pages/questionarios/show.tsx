import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Questionários', href: '/questionarios' },
    { title: 'Detalhes do Questionário', href: '/questionarios/show' },
];

interface Resposta {
    id: number;
    cod_pergunta: number;
    resposta: number;
    pergunta_descricao: string;
    resposta_descricao: string;
    usuario_nome: string;
    data_questionario: string | null;
    hora_questionario: string | null;
    observacao: string | null;
}

interface Paciente {
    id: number;
    nome: string;
    telefone: string;
    email: string;
    sexo: string;
    tipo_paciente: string | null;
    idade: number;
    leito: string | null;
    setor: string | null;
    renda: string | null;
    tp_cod_convenio: number | null;
    tipo_descricao: string | null;
}

interface QuestionariosShowProps {
    paciente: Paciente;
    respostas: Resposta[];
}

export default function QuestionariosShow({
    paciente,
    respostas,
}: QuestionariosShowProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Questionário - ${paciente.nome}`} />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center gap-4">
                    <Link href="/questionarios">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Voltar
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold">Detalhes do Questionário</h1>
                        <p className="text-muted-foreground mt-1">
                            Visualização completa do questionário do paciente
                        </p>
                    </div>
                </div>

                {/* Dados do Paciente */}
                <Card>
                    <CardHeader>
                        <CardTitle>Dados do Paciente</CardTitle>
                        <CardDescription>
                            Informações cadastrais do paciente
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Nome</p>
                                <p className="text-base">{paciente.nome}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Email</p>
                                <p className="text-base">{paciente.email}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Telefone</p>
                                <p className="text-base">{paciente.telefone}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Sexo</p>
                                <p className="text-base">{paciente.sexo}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Idade</p>
                                <p className="text-base">{paciente.idade} anos</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Tipo de Paciente</p>
                                <p className="text-base">{paciente.tipo_paciente || '—'}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Leito</p>
                                <p className="text-base">{paciente.leito || '—'}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Setor</p>
                                <p className="text-base">{paciente.setor || '—'}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Renda</p>
                                <p className="text-base">{paciente.renda || '—'}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Tipo de Convênio</p>
                                <p className="text-base">{paciente.tipo_descricao || '—'}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Respostas */}
                <Card>
                    <CardHeader>
                        <CardTitle>Perguntas e Respostas</CardTitle>
                        <CardDescription>
                            Total de {respostas.length} resposta(s)
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {respostas.length === 0 ? (
                            <p className="text-muted-foreground text-center py-4">
                                Nenhuma resposta encontrada
                            </p>
                        ) : (
                            <div className="space-y-4">
                                {respostas.map((resposta) => (
                                    <div
                                        key={resposta.id}
                                        className="rounded-lg border p-4 space-y-2"
                                    >
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">
                                                Pergunta
                                            </p>
                                            <p className="text-base font-medium">
                                                {resposta.pergunta_descricao}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">
                                                Resposta
                                            </p>
                                            <p className="text-base">{resposta.resposta_descricao}</p>
                                        </div>
                                        {resposta.observacao && (
                                            <div>
                                                <p className="text-sm font-medium text-muted-foreground">
                                                    Observação
                                                </p>
                                                <p className="text-base text-sm">
                                                    {resposta.observacao}
                                                </p>
                                            </div>
                                        )}
                                        <div className="flex items-center gap-4 text-xs text-muted-foreground pt-2 border-t">
                                            <span>
                                                Registrado por: {resposta.usuario_nome}
                                            </span>
                                            {resposta.data_questionario && (
                                                <span>
                                                    Data:{' '}
                                                    {new Date(
                                                        resposta.data_questionario
                                                    ).toLocaleDateString('pt-BR')}
                                                    {resposta.hora_questionario && ` ${resposta.hora_questionario}`}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

