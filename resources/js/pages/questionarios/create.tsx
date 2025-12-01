import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, router } from '@inertiajs/react';
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
import { Checkbox } from '@/components/ui/checkbox';
import { cn } from '@/lib/utils';
import { useState, useEffect, useMemo } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Questionários', href: '/questionarios' },
    { title: 'Novo Questionário', href: '/questionarios/create' },
];

interface Pergunta {
    cod: number;
    descricao: string;
    cod_setor_pesquis: number | null;
    cod_tipo_pergunta: number | null;
}

interface Satisfacao {
    cod: number;
    descricao: string;
    cod_tipo_pergunta: number | null;
}

interface TipoConvenio {
    cod: number;
    tipo_descricao: string | null;
}

interface Setor {
    cod: number;
    descricao: string;
}

interface Leito {
    cod: number;
    descricao: string;
    cod_setor: number | null;
}

interface SetorPesquisa {
    cod: number;
    descricao: string;
}

interface QuestionariosCreateProps {
    perguntas: Pergunta[];
    satisfacoes: Satisfacao[];
    tiposConvenio: TipoConvenio[];
    setores: Setor[];
    leitos: Leito[];
    setoresPesquisa: SetorPesquisa[];
}

// Função para aplicar máscara de telefone
const applyPhoneMask = (value: string): string => {
    // Remove tudo que não é número
    const numbers = value.replace(/\D/g, '');

    // Aplica máscara: (XX) XXXXX-XXXX ou (XX) XXXX-XXXX
    if (numbers.length <= 10) {
        return numbers
            .replace(/(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{4})(\d)/, '$1-$2');
    } else {
        return numbers
            .replace(/(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{5})(\d)/, '$1-$2');
    }
};

export default function QuestionariosCreate({
    perguntas,
    satisfacoes,
    tiposConvenio,
    setores,
    leitos,
    setoresPesquisa,
}: QuestionariosCreateProps) {
    const [respostas, setRespostas] = useState<Record<number, number>>({});
    const [respostasTexto, setRespostasTexto] = useState<Record<number, string>>({});

    const { data, setData, post, processing, errors } = useForm({
        // Dados do paciente
        nome: '',
        telefone: '',
        email: '',
        sexo: '',
        tipo_paciente: '',
        idade: '',
        leito: '',
        cod_setor: '',
        renda: '',
        tp_cod_convenio: '',
        // Dados do questionário
        data_isretroativa: false,
        data_retroativa: '',
        respostas: [] as Array<{ cod_pergunta: number; resposta: number | null; resposta_texto: string | null }>,
    });

    // Filtrar leitos por setor selecionado
    const leitosFiltrados = useMemo(() => {
        if (!data.cod_setor) {
            return [];
        }
        return leitos.filter((leito) => leito.cod_setor === Number(data.cod_setor));
    }, [data.cod_setor, leitos]);

    // O setor de pesquisa será vinculado automaticamente no backend baseado no cadastro de cada pergunta

    const handleTelefoneChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const maskedValue = applyPhoneMask(e.target.value);
        setData('telefone', maskedValue);
    };

    const handleRespostaChange = (perguntaCod: number, satisfacaoCod: number) => {
        setRespostas((prev) => ({
            ...prev,
            [perguntaCod]: satisfacaoCod,
        }));
    };

    // Toggle via checkbox (mantém comportamento de seleção única por pergunta)
    const handleRespostaCheckbox = (
        perguntaCod: number,
        satisfacaoCod: number,
        checked: boolean | string,
    ) => {
        const isChecked = checked === true;
        setRespostas((prev) => {
            if (!isChecked) {
                const { [perguntaCod]: _remove, ...rest } = prev;
                return rest;
            }
            return {
                ...prev,
                [perguntaCod]: satisfacaoCod,
            };
        });
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        // Preparar array de respostas incluindo texto livre
        const respostasArray: Array<{ cod_pergunta: number; resposta: number | null; resposta_texto: string | null }> = [];

        // Adicionar respostas de opções (tipos 1, 2, 3)
        Object.entries(respostas).forEach(([perguntaCod, satisfacaoCod]) => {
            respostasArray.push({
                cod_pergunta: Number(perguntaCod),
                resposta: Number(satisfacaoCod),
                resposta_texto: null,
            });
        });

        // Adicionar respostas de texto livre (tipo 4)
        Object.entries(respostasTexto).forEach(([perguntaCod, texto]) => {
            respostasArray.push({
                cod_pergunta: Number(perguntaCod),
                resposta: null,
                resposta_texto: texto || null,
            });
        });

        if (respostasArray.length === 0) {
            alert('Por favor, responda pelo menos uma pergunta.');
            return;
        }

        // Remover máscara do telefone antes de enviar
        const telefoneSemMascara = data.telefone.replace(/\D/g, '');

        // Preparar dados para envio
        const dadosParaEnviar = {
            nome: data.nome,
            telefone: telefoneSemMascara,
            email: data.email,
            sexo: data.sexo,
            tipo_paciente: data.tipo_paciente || null,
            idade: Number(data.idade),
            cod_setor: Number(data.cod_setor),
            leito: data.leito ? Number(data.leito) : null,
            renda: data.renda || null,
            tp_cod_convenio: data.tp_cod_convenio ? Number(data.tp_cod_convenio) : null,
            data_isretroativa: data.data_isretroativa || false,
            data_retroativa: data.data_retroativa || null,
            respostas: respostasArray,
        };

        console.log('Dados sendo enviados:', dadosParaEnviar);

        // Usar router.post diretamente para garantir que os dados sejam enviados corretamente
        router.post('/questionarios', dadosParaEnviar, {
            preserveScroll: true,
            onError: (errors) => {
                console.error('Erros de validação:', errors);
                console.error('Detalhes dos erros:', JSON.stringify(errors, null, 2));
            },
            onSuccess: (page) => {
                console.log('Questionário salvo com sucesso!', page);
            },
            onFinish: () => {
                console.log('Requisição finalizada');
            },
        });
    };

    const opcoesRenda = [
        { value: '1 salário mínimo', label: '1 salário mínimo' },
        { value: '2 salários mínimos', label: '2 salários mínimos' },
        { value: '3 salários mínimos', label: '3 salários mínimos' },
        { value: '4 salários mínimos', label: '4 salários mínimos' },
        { value: '5 salários mínimos', label: '5 salários mínimos' },
        { value: '6 salários mínimos', label: '6 salários mínimos' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Novo Questionário" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center gap-4">
                    <Link href="/questionarios">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Voltar
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold">Novo Questionário</h1>
                        <p className="text-muted-foreground mt-1">
                            Preencha os dados do paciente e responda as perguntas
                        </p>
                    </div>
                </div>

                {/* Exibir erro geral se houver */}
                {errors.error && (
                    <div className="rounded-md bg-destructive/15 p-4 text-sm text-destructive">
                        {errors.error}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Dados do Paciente */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Dados do Paciente</CardTitle>
                            <CardDescription>
                                Informe os dados do paciente
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="nome">
                                        Nome <span className="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="nome"
                                        name="nome"
                                        type="text"
                                        required
                                        value={data.nome}
                                        onChange={(e) => setData('nome', e.target.value)}
                                    />
                                    <InputError message={errors.nome} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="email">
                                        Email <span className="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="email"
                                        name="email"
                                        type="email"
                                        required
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="telefone">
                                        Telefone <span className="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="telefone"
                                        name="telefone"
                                        type="text"
                                        required
                                        maxLength={15}
                                        placeholder="(00) 00000-0000"
                                        value={data.telefone}
                                        onChange={handleTelefoneChange}
                                    />
                                    <InputError message={errors.telefone} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="sexo">
                                        Sexo <span className="text-red-500">*</span>
                                    </Label>
                                    <select
                                        id="sexo"
                                        name="sexo"
                                        required
                                        value={data.sexo}
                                        onChange={(e) => setData('sexo', e.target.value)}
                                        className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option value="">Selecione</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Feminino</option>
                                        <option value="Outro">Outro</option>
                                    </select>
                                    <InputError message={errors.sexo} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="idade">
                                        Idade <span className="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="idade"
                                        name="idade"
                                        type="number"
                                        required
                                        min="0"
                                        value={data.idade}
                                        onChange={(e) => setData('idade', e.target.value)}
                                    />
                                    <InputError message={errors.idade} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="tipo_paciente">Tipo de Paciente</Label>
                                    <select
                                        id="tipo_paciente"
                                        name="tipo_paciente"
                                        value={data.tipo_paciente}
                                        onChange={(e) => setData('tipo_paciente', e.target.value)}
                                        className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option value="">Selecione</option>
                                        <option value="Paciente">Paciente</option>
                                        <option value="Acompanhante">Acompanhante</option>
                                    </select>
                                    <InputError message={errors.tipo_paciente} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="tp_cod_convenio">Tipo de Convênio</Label>
                                    <select
                                        id="tp_cod_convenio"
                                        name="tp_cod_convenio"
                                        value={data.tp_cod_convenio}
                                        onChange={(e) => setData('tp_cod_convenio', e.target.value)}
                                        className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option value="">Selecione</option>
                                        {tiposConvenio.map((tipo) => (
                                            <option key={tipo.cod} value={tipo.cod}>
                                                {tipo.tipo_descricao || `Convênio ${tipo.cod}`}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.tp_cod_convenio} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="cod_setor">
                                        Setor <span className="text-red-500">*</span>
                                    </Label>
                                    <select
                                        id="cod_setor"
                                        name="cod_setor"
                                        required
                                        value={data.cod_setor}
                                        onChange={(e) => {
                                            setData('cod_setor', e.target.value);
                                            // Limpar leito quando trocar setor
                                            setData('leito', '');
                                        }}
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

                                <div className="space-y-2">
                                    <Label htmlFor="leito">Leito</Label>
                                    <select
                                        id="leito"
                                        name="leito"
                                        value={data.leito}
                                        onChange={(e) => setData('leito', e.target.value)}
                                        disabled={!data.cod_setor || leitosFiltrados.length === 0}
                                        className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option value="">
                                            {!data.cod_setor
                                                ? 'Selecione um setor primeiro'
                                                : leitosFiltrados.length === 0
                                                ? 'Nenhum leito disponível'
                                                : 'Selecione um leito'}
                                        </option>
                                        {leitosFiltrados.map((leito) => (
                                            <option key={leito.cod} value={leito.cod}>
                                                {leito.descricao}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.leito} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="renda">Renda</Label>
                                    <select
                                        id="renda"
                                        name="renda"
                                        value={data.renda}
                                        onChange={(e) => setData('renda', e.target.value)}
                                        className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option value="">Selecione</option>
                                        {opcoesRenda.map((opcao) => (
                                            <option key={opcao.value} value={opcao.value}>
                                                {opcao.label}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.renda} />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Perguntas e Respostas */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Perguntas</CardTitle>
                            <CardDescription>
                                Responda as perguntas de pesquisa de satisfação
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            {perguntas.length === 0 ? (
                                <p className="text-muted-foreground text-center py-4">
                                    Nenhuma pergunta cadastrada. Cadastre perguntas primeiro.
                                </p>
                            ) : (
                                [...perguntas].sort((a, b) => a.cod - b.cod).map((pergunta) => {
                                    // Tipo 4 = texto livre
                                    const isTipoLivre = pergunta.cod_tipo_pergunta === 4;

                                    if (isTipoLivre) {
                                        // Renderizar textarea para perguntas tipo 4
                                        return (
                                            <div key={pergunta.cod} className="space-y-2">
                                                <Label htmlFor={`pergunta-texto-${pergunta.cod}`}>
                                                    {pergunta.descricao} <span className="text-red-500">*</span>
                                                </Label>
                                                <textarea
                                                    id={`pergunta-texto-${pergunta.cod}`}
                                                    name={`pergunta-texto-${pergunta.cod}`}
                                                    rows={4}
                                                    value={respostasTexto[pergunta.cod] || ''}
                                                    onChange={(e) =>
                                                        setRespostasTexto((prev) => ({
                                                            ...prev,
                                                            [pergunta.cod]: e.target.value,
                                                        }))
                                                    }
                                                    className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                                    maxLength={1000}
                                                    required
                                                />
                                            </div>
                                        );
                                    }

                                    // Filtrar satisfações baseado no cod_tipo_pergunta da pergunta
                                    const satisfacoesFiltradas = satisfacoes
                                        .filter((satisfacao) => {
                                            // Se a pergunta não tem tipo, mostrar todas as satisfações sem tipo
                                            if (!pergunta.cod_tipo_pergunta) {
                                                return !satisfacao.cod_tipo_pergunta;
                                            }
                                            // Se a pergunta tem tipo, mostrar apenas satisfações com o mesmo tipo
                                            return satisfacao.cod_tipo_pergunta === pergunta.cod_tipo_pergunta;
                                        })
                                        .sort((a, b) => {
                                            // Para perguntas tipo 3 (escala 0-10), ordenar numericamente
                                            if (pergunta.cod_tipo_pergunta === 3) {
                                                // Tentar converter descrição para número
                                                const numA = parseInt(a.descricao, 10);
                                                const numB = parseInt(b.descricao, 10);

                                                // Se ambas são números, ordenar numericamente
                                                if (!isNaN(numA) && !isNaN(numB)) {
                                                    return numA - numB;
                                                }

                                                // Se A é número e B não é (NA), A vem primeiro
                                                if (!isNaN(numA) && isNaN(numB)) {
                                                    return -1;
                                                }

                                                // Se B é número e A não é (NA), B vem primeiro
                                                if (isNaN(numA) && !isNaN(numB)) {
                                                    return 1;
                                                }

                                                // Se nenhuma é número, manter ordem original
                                                return 0;
                                            }

                                            // Para outros tipos, ordenar por código
                                            return a.cod - b.cod;
                                        });

                                    return (
                                        <div key={pergunta.cod} className="space-y-2">
                                            <Label htmlFor={`pergunta-${pergunta.cod}`}>
                                                {pergunta.descricao} <span className="text-red-500">*</span>
                                            </Label>
                                            <div className="grid gap-2">
                                                {satisfacoesFiltradas.length === 0 ? (
                                                    <p className="text-sm text-muted-foreground">
                                                        Nenhuma opção disponível
                                                    </p>
                                                ) : (
                                                    <div
                                                        className="flex items-center gap-2 overflow-x-auto whitespace-nowrap p-1 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden"
                                                        role="group"
                                                        aria-label={`Opções de resposta para a pergunta ${pergunta.descricao}`}
                                                    >
                                                        {satisfacoesFiltradas.map((satisfacao) => {
                                                            const checked =
                                                                respostas[pergunta.cod] === satisfacao.cod;
                                                            const checkboxId = `pergunta-${pergunta.cod}-satisf-${satisfacao.cod}`;
                                                            return (
                                                                <label
                                                                    key={satisfacao.cod}
                                                                    htmlFor={checkboxId}
                                                                    className={cn(
                                                                        'inline-flex shrink-0 items-center gap-2 rounded-md border border-input px-3 py-1 text-sm hover:bg-muted/50 transition-colors',
                                                                        checked && 'bg-primary/10 border-primary'
                                                                    )}
                                                                >
                                                                    <Checkbox
                                                                        id={checkboxId}
                                                                        checked={checked}
                                                                        onCheckedChange={(c) =>
                                                                            handleRespostaCheckbox(
                                                                                pergunta.cod,
                                                                                satisfacao.cod,
                                                                                c,
                                                                            )
                                                                        }
                                                                        aria-label={`Selecionar resposta ${satisfacao.descricao}`}
                                                                    />
                                                                    <span className="text-sm leading-none">
                                                                        {satisfacao.descricao}
                                                                    </span>
                                                                </label>
                                                            );
                                                        })}
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    );
                                })
                            )}
                        </CardContent>
                    </Card>

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing || perguntas.length === 0}>
                            {processing ? 'Salvando...' : 'Salvar Questionário'}
                        </Button>
                        <Link href="/questionarios">
                            <Button type="button" variant="outline">
                                Cancelar
                            </Button>
                        </Link>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
