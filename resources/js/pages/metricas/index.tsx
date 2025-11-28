import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Can from '@/components/Can';
import {
    DropdownMenu,
    DropdownMenuTrigger,
    DropdownMenuContent,
} from '@/components/ui/dropdown-menu';
import {
    ClipboardList,
    Users,
    TrendingUp,
    BarChart3,
    Filter,
    ArrowRight,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import {
    ResponsiveContainer,
    BarChart,
    Bar as RechartsBar,
    XAxis,
    YAxis,
    Tooltip as RechartsTooltip,
    LineChart,
    Line,
} from 'recharts';
import { ChartContainer, ChartTooltipContent } from '@/components/ui/chart';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Métricas', href: '/metricas' },
];

type KeyValue = { [key: string]: unknown };

interface SerieTemporalItem {
    ano: number;
    mes: number;
    media: number;
}

interface MediasItem {
    setor: string;
    media: number;
}

interface RankingItem {
    setor: string;
    total: number;
}

interface DimensaoItem {
    cod: number;
    descricao: string;
    media: number;
}

type DistribItem = { total: number } & KeyValue;

interface MetricasIndexProps {
    filters: {
        from?: string;
        to?: string;
        setor?: string;
        tipo_paciente?: string;
        convenio?: string;
    };
    filterOptions: {
        setores: string[];
        convenios: Array<{ cod: number; tipo_descricao: string | null }>;
        tiposPaciente: string[];
    };
    overview?: {
        totalQuestionarios: number;
        totalRespostas: number;
        satisfacaoMedia: number;
    };
    nps?: number;
    npsMean?: number | null;
    npsDetail?: {
        total: number;
        promotores: number;
        neutros: number;
        detratores: number;
        percPromotores: number;
        percDetratores: number;
        bySetor: Array<{ setor: string; promotores: number; neutros: number; detratores: number; total: number; nps: number }>;
    };
    setores?: {
        medias: MediasItem[];
        ranking: RankingItem[];
    };
    dimensoes?: DimensaoItem[];
    // Guard contra nome com acento usado por engano
    'distribuições'?: never; // Typo guard; real prop abaixo
    distribuicoes?: {
        tipoPaciente: DistribItem[];
        sexo: DistribItem[];
        renda: DistribItem[];
        faixaEtaria: DistribItem[];
        convenio: DistribItem[];
    };
    temporal?: SerieTemporalItem[];
}

function formatMonth(ano: number, mes: number) {
    return `${String(mes).padStart(2, '0')}/${ano}`;
}

function Bar({ label, value, max }: { label: string; value: number; max: number }) {
    const width = max > 0 ? `${(value / max) * 100}%` : '0%';
    return (
        <div className="space-y-1">
            <div className="flex items-center justify-between text-xs">
                <span className="truncate">{label}</span>
                <span className="text-muted-foreground">{value}</span>
            </div>
            <div className="h-2 w-full rounded bg-muted">
                <div className="h-2 rounded bg-primary" style={{ width }} />
            </div>
        </div>
    );
}

function BarNote({ label, value, max }: { label: string; value: number; max: number }) {
    const width = max > 0 ? `${(value / max) * 100}%` : '0%';
    return (
        <div className="space-y-1">
            <div className="flex items-center justify-between text-xs">
                <span className="truncate">{label}</span>
                <span className="text-muted-foreground">{value.toFixed(2)}</span>
            </div>
            <div className="h-2 w-full rounded bg-muted">
                <div className="h-2 rounded bg-blue-500" style={{ width }} />
            </div>
        </div>
    );
}

export default function MetricasIndex(props: MetricasIndexProps) {
    const { filters, filterOptions } = props;
    const [from, setFrom] = useState(filters.from ?? '');
    const [to, setTo] = useState(filters.to ?? '');
    const [setor, setSetor] = useState(filters.setor ?? '');
    const [tipoPaciente, setTipoPaciente] = useState(filters.tipo_paciente ?? '');
    const [convenio, setConvenio] = useState(filters.convenio ?? '');
    const [showNpsDetails, setShowNpsDetails] = useState(false);

    const format2 = (n?: number | null) =>
        typeof n === 'number' && !Number.isNaN(n) ? n.toFixed(2) : '—';

    const handleApplyFilters = () => {
        router.get('/metricas', { from, to, setor, tipo_paciente: tipoPaciente, convenio }, { preserveState: true });
    };

    const maxMediaSetor = useMemo(
        () => Math.max(10, ...(props.setores?.medias?.map((i) => i.media) ?? [])),
        [props.setores],
    );
    const maxRankingSetor = useMemo(
        () => Math.max(1, ...(props.setores?.ranking?.map((i) => i.total) ?? [])),
        [props.setores],
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Métricas" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">Métricas de Pesquisa</h1>
                        <p className="text-muted-foreground mt-1">
                            Visualize estatísticas detalhadas com filtros.
                        </p>
                    </div>
                </div>

                {/* Filtros */}
                <Card>
                    <CardHeader className="flex items-center justify-between space-y-0">
                        <div className="flex items-center gap-2">
                            <Filter className="h-4 w-4 text-muted-foreground" />
                            <CardTitle>Filtros</CardTitle>
                        </div>
                        <CardDescription>Economize espaço utilizando o dropdown</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="outline">
                                    <Filter className="mr-2 h-4 w-4" />
                                    Abrir filtros
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent className="w-[360px] p-3">
                                <div className="grid gap-3">
                                    <div className="grid gap-2">
                                        <Label htmlFor="from">De</Label>
                                        <Input id="from" type="date" value={from} onChange={(e) => setFrom(e.target.value)} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="to">Até</Label>
                                        <Input id="to" type="date" value={to} onChange={(e) => setTo(e.target.value)} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="setor">Setor</Label>
                                        <select
                                            id="setor"
                                            value={setor}
                                            onChange={(e) => setSetor(e.target.value)}
                                            className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                        >
                                            <option value="">Todos</option>
                                            {filterOptions.setores.map((s) => (
                                                <option key={s} value={s}>
                                                    {s}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="tipo_paciente">Tipo de Paciente</Label>
                                        <select
                                            id="tipo_paciente"
                                            value={tipoPaciente}
                                            onChange={(e) => setTipoPaciente(e.target.value)}
                                            className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                        >
                                            <option value="">Todos</option>
                                            {filterOptions.tiposPaciente.map((t) => (
                                                <option key={t} value={t}>
                                                    {t}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="convenio">Convênio</Label>
                                        <select
                                            id="convenio"
                                            value={convenio}
                                            onChange={(e) => setConvenio(e.target.value)}
                                            className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                        >
                                            <option value="">Todos</option>
                                            {filterOptions.convenios.map((c) => (
                                                <option key={c.cod} value={String(c.cod)}>
                                                    {c.tipo_descricao ?? `Convênio ${c.cod}`}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                    <div className="flex items-center justify-end gap-2 pt-1">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => {
                                                setFrom(''); setTo(''); setSetor(''); setTipoPaciente(''); setConvenio('');
                                                router.get('/metricas', {}, { preserveState: true });
                                            }}
                                        >
                                            Limpar
                                        </Button>
                                        <Button onClick={handleApplyFilters}>Aplicar</Button>
                                    </div>
                                </div>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </CardContent>
                </Card>

                {/* Visão Geral */}
                <Can permission="metricas.overview">
                    {props.overview && (
                        <div className="grid gap-4 md:grid-cols-3">
                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">Total de Questionários</CardTitle>
                                    <ClipboardList className="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">{props.overview.totalQuestionarios}</div>
                                    <p className="text-xs text-muted-foreground">Pacientes distintos</p>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">Total de Respostas</CardTitle>
                                    <Users className="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">{props.overview.totalRespostas}</div>
                                    <p className="text-xs text-muted-foreground">Respostas coletadas</p>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">Taxa de Satisfação (0–10)</CardTitle>
                                    <TrendingUp className="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">{props.overview.satisfacaoMedia}</div>
                                    <p className="text-xs text-muted-foreground">Média das notas tipo 3</p>
                                </CardContent>
                            </Card>
                        </div>
                    )}
                </Can>

                {/* NPS */}
                <Can permission="metricas.nps">
                    {typeof props.nps === 'number' && (
                        <Card id="nps-details">
                            <CardHeader>
                                <CardTitle>NPS</CardTitle>
                                <CardDescription>Pergunta de recomendação (0–10)</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="text-3xl font-bold">{format2(props.nps)}</div>
                                <p className="text-xs text-muted-foreground">
                                    100*(Promotores/Total) − 100*(Detratores/Total)
                                </p>

                                {/* Card da média 0–10 da pergunta de NPS */}
                                {typeof props.npsMean === 'number' && (
                                    <div className="grid gap-3 md:grid-cols-4">
                                        <div className="rounded-md border p-3">
                                            <div className="text-xs text-muted-foreground">Média (0–10)</div>
                                            <div className="text-xl font-semibold">{format2(props.npsMean)}</div>
                                        </div>
                                    </div>
                                )}

                                {/* Botão Ver Métricas Detalhadas */}
                                <Button
                                    variant="outline"
                                    className="w-full"
                                    onClick={() => setShowNpsDetails((v) => !v)}
                                >
                                    Ver Métricas Detalhadas
                                    <ArrowRight className="ml-2 h-4 w-4" />
                                </Button>

                                {/* Detalhes do NPS */}
                                {showNpsDetails && props.npsDetail && (
                                    <div className="space-y-4">
                                        <div className="grid gap-3 md:grid-cols-4">
                                            <div className="rounded-md border p-3">
                                                <div className="text-xs text-muted-foreground">Total</div>
                                                <div className="text-xl font-semibold">{props.npsDetail.total}</div>
                                            </div>
                                            <div className="rounded-md border p-3">
                                                <div className="text-xs text-muted-foreground">Promotores</div>
                                                <div className="text-xl font-semibold">
                                                    {props.npsDetail.promotores} ({props.npsDetail.percPromotores}%)
                                                </div>
                                            </div>
                                            <div className="rounded-md border p-3">
                                                <div className="text-xs text-muted-foreground">Neutros</div>
                                                <div className="text-xl font-semibold">{props.npsDetail.neutros}</div>
                                            </div>
                                            <div className="rounded-md border p-3">
                                                <div className="text-xs text-muted-foreground">Detratores</div>
                                                <div className="text-xl font-semibold">
                                                    {props.npsDetail.detratores} ({props.npsDetail.percDetratores}%)
                                                </div>
                                            </div>
                                        </div>

                                        {/* NPS por setor */}
                                        <div>
                                            <div className="mb-2 text-sm font-medium">NPS por Setor</div>
                                            {props.npsDetail.bySetor.length === 0 ? (
                                                <p className="text-sm text-muted-foreground">Sem dados.</p>
                                            ) : (
                                                <ChartContainer>
                                                    <ResponsiveContainer width="100%" height="100%">
                                                        <BarChart data={props.npsDetail.bySetor}>
                                                            <XAxis
                                                                dataKey="setor"
                                                                tick={{ fontSize: 10 }}
                                                                interval={0}
                                                                height={60}
                                                                angle={-35}
                                                                textAnchor="end"
                                                            />
                                                            <YAxis domain={[-100, 100]} />
                                                            <RechartsTooltip content={<ChartTooltipContent />} />
                                                            <RechartsBar dataKey="nps" name="NPS" fill="hsl(var(--primary))" radius={[4, 4, 0, 0]} />
                                                        </BarChart>
                                                    </ResponsiveContainer>
                                                </ChartContainer>
                                            )}
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    )}
                </Can>

                {/* Setores */}
                <Can permission="metricas.setores">
                    {props.setores && (
                        <div className="grid gap-4 md:grid-cols-2">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Média por Setor (0–10)</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-2">
                                    {props.setores.medias.length === 0 ? (
                                        <p className="text-sm text-muted-foreground">Sem dados.</p>
                                    ) : (
                                        <ChartContainer>
                                            <ResponsiveContainer width="100%" height="100%">
                                                <BarChart data={props.setores.medias}>
                                                    <XAxis dataKey="setor" tick={{ fontSize: 10 }} interval={0} height={50} angle={-35} textAnchor="end" />
                                                    <YAxis domain={[0, 10]} />
                                                    <RechartsTooltip content={<ChartTooltipContent />} />
                                                <RechartsBar dataKey="media" name="Média" fill="hsl(var(--primary))" radius={[4, 4, 0, 0]} />
                                                </BarChart>
                                            </ResponsiveContainer>
                                        </ChartContainer>
                                    )}
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader>
                                    <CardTitle>Ranking por Volume (Top 10)</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-2">
                                    {props.setores.ranking.length === 0 ? (
                                        <p className="text-sm text-muted-foreground">Sem dados.</p>
                                    ) : (
                                        <ChartContainer>
                                            <ResponsiveContainer width="100%" height="100%">
                                                <BarChart data={props.setores.ranking}>
                                                    <XAxis dataKey="setor" tick={{ fontSize: 10 }} interval={0} height={50} angle={-35} textAnchor="end" />
                                                    <YAxis allowDecimals={false} />
                                                    <RechartsTooltip content={<ChartTooltipContent />} />
                                                    <RechartsBar dataKey="total" name="Respostas" fill="hsl(var(--primary))" radius={[4, 4, 0, 0]} />
                                                </BarChart>
                                            </ResponsiveContainer>
                                        </ChartContainer>
                                    )}
                                </CardContent>
                            </Card>
                        </div>
                    )}
                </Can>

                {/* Dimensões */}
                <Can permission="metricas.dimensoes">
                    {props.dimensoes && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Média por Dimensão (pergunta tipo 3)</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                {props.dimensoes.length === 0 ? (
                                    <p className="text-sm text-muted-foreground">Sem dados.</p>
                                ) : (
                                    <ChartContainer>
                                        <ResponsiveContainer width="100%" height="100%">
                                            <BarChart data={props.dimensoes}>
                                                <XAxis dataKey="descricao" tick={{ fontSize: 10 }} interval={0} height={70} angle={-35} textAnchor="end" />
                                                <YAxis domain={[0, 10]} />
                                                <RechartsTooltip content={<ChartTooltipContent />} />
                                                <RechartsBar dataKey="media" name="Média" fill="hsl(var(--primary))" radius={[4, 4, 0, 0]} />
                                            </BarChart>
                                        </ResponsiveContainer>
                                    </ChartContainer>
                                )}
                            </CardContent>
                        </Card>
                    )}
                </Can>

                {/* Distribuições */}
                <Can permission="metricas.distribuicoes">
                    {props.distribuicoes && (
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <Card>
                                <CardHeader><CardTitle>Tipo de Paciente</CardTitle></CardHeader>
                                <CardContent className="space-y-2">
                                    {props.distribuicoes.tipoPaciente.map((i, idx) => (
                                        <Bar key={idx} label={(i as any).tipo_paciente ?? '—'} value={i.total} max={Math.max(...props.distribuicoes!.tipoPaciente.map(x => x.total))} />
                                    ))}
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader><CardTitle>Sexo</CardTitle></CardHeader>
                                <CardContent className="space-y-2">
                                    {props.distribuicoes.sexo.map((i, idx) => (
                                        <Bar key={idx} label={(i as any).sexo ?? '—'} value={i.total} max={Math.max(...props.distribuicoes!.sexo.map(x => x.total))} />
                                    ))}
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader><CardTitle>Renda</CardTitle></CardHeader>
                                <CardContent className="space-y-2">
                                    {props.distribuicoes.renda.map((i, idx) => (
                                        <Bar key={idx} label={(i as any).renda ?? '—'} value={i.total} max={Math.max(...props.distribuicoes!.renda.map(x => x.total))} />
                                    ))}
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader><CardTitle>Faixa Etária</CardTitle></CardHeader>
                                <CardContent className="space-y-2">
                                    {props.distribuicoes.faixaEtaria.map((i, idx) => (
                                        <Bar key={idx} label={(i as any).faixa ?? '—'} value={i.total} max={Math.max(...props.distribuicoes!.faixaEtaria.map(x => x.total))} />
                                    ))}
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader><CardTitle>Convênio</CardTitle></CardHeader>
                                <CardContent className="space-y-2">
                                    {props.distribuicoes.convenio.map((i, idx) => (
                                        <Bar key={idx} label={(i as any).convenio ?? '—'} value={i.total} max={Math.max(...props.distribuicoes!.convenio.map(x => x.total))} />
                                    ))}
                                </CardContent>
                            </Card>
                        </div>
                    )}
                </Can>

                {/* Série Temporal */}
                <Can permission="metricas.temporal">
                    {props.temporal && (
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <CardTitle>Avaliação Mensal (média 0–10)</CardTitle>
                                    <BarChart3 className="h-4 w-4 text-muted-foreground" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                {props.temporal.length === 0 ? (
                                    <p className="text-sm text-muted-foreground">Sem dados.</p>
                                ) : (
                                    <ChartContainer>
                                        <ResponsiveContainer width="100%" height="100%">
                                            <LineChart
                                                data={props.temporal.map((p) => ({
                                                    label: formatMonth(p.ano, p.mes),
                                                    media: p.media,
                                                }))}
                                            >
                                                <XAxis dataKey="label" tick={{ fontSize: 10 }} interval={0} />
                                                <YAxis domain={[0, 10]} />
                                                <RechartsTooltip content={<ChartTooltipContent />} />
                                                <Line type="monotone" dataKey="media" name="Média" stroke="hsl(var(--primary))" strokeWidth={2} dot={false} />
                                            </LineChart>
                                        </ResponsiveContainer>
                                    </ChartContainer>
                                )}
                            </CardContent>
                        </Card>
                    )}
                </Can>
            </div>
        </AppLayout>
    );
}

