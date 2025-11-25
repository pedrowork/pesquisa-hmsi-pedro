import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import {
    Users,
    Shield,
    Key,
    UserPlus,
    ShieldPlus,
    KeyRound,
    ArrowRight,
    ClipboardList,
    UserCheck,
    TrendingUp,
    Calendar,
    FileText,
    BarChart3,
    Users2,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import Can from '@/components/Can';
import { useHasPermission, useIsAdmin } from '@/hooks/usePermissions';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface DashboardStats {
    totalUsers?: number;
    totalRoles?: number;
    totalPermissions?: number;
    activeUsers?: number;
}

interface ResearchStats {
    totalQuestionarios?: number;
    totalPacientes?: number;
    questionariosMes?: number;
    totalRespostas?: number;
    pacientesMes?: number;
    satisfacaoMedia?: number;
    topSetores?: Array<{
        setor: string;
        total: number;
    }>;
    tipoPaciente?: Array<{
        tipo_paciente: string;
        total: number;
    }>;
}

interface DashboardProps {
    stats?: DashboardStats;
    researchStats?: ResearchStats;
}

export default function Dashboard({ stats, researchStats }: DashboardProps) {
    const isAdmin = useIsAdmin();
    const hasUsersView = useHasPermission('users.view');
    const hasUsersCreate = useHasPermission('users.create');
    const hasRolesView = useHasPermission('roles.view');
    const hasRolesCreate = useHasPermission('roles.create');
    const hasPermissionsView = useHasPermission('permissions.view');

    const quickActions = [
        {
            title: 'Novo Usuário',
            description: 'Cadastrar um novo usuário no sistema',
            href: '/users/create',
            icon: UserPlus,
            color: 'text-blue-500',
            show: isAdmin || hasUsersCreate,
        },
        {
            title: 'Nova Role',
            description: 'Criar um novo grupo de usuários',
            href: '/roles/create',
            icon: ShieldPlus,
            color: 'text-purple-500',
            show: isAdmin || hasRolesCreate,
        },
        {
            title: 'Permissões',
            description: 'Gerenciar permissões do sistema',
            href: '/permissions',
            icon: KeyRound,
            color: 'text-green-500',
            show: isAdmin || hasPermissionsView,
        },
    ].filter((action) => action.show);

    const managementLinks = [
        {
            title: 'Usuários',
            description: 'Gerenciar usuários do sistema',
            href: '/users',
            icon: Users,
            count: stats?.totalUsers || 0,
            show: isAdmin || hasUsersView,
        },
        {
            title: 'Roles',
            description: 'Gerenciar grupos de usuários',
            href: '/roles',
            icon: Shield,
            count: stats?.totalRoles || 0,
            show: isAdmin || hasRolesView,
        },
        {
            title: 'Permissões',
            description: 'Visualizar permissões disponíveis',
            href: '/permissions',
            icon: Key,
            count: stats?.totalPermissions || 0,
            show: isAdmin || hasPermissionsView,
        },
    ].filter((link) => link.show);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold">Dashboard</h1>
                    <p className="text-muted-foreground mt-1">
                        Visão geral e estatísticas do sistema.
                    </p>
                </div>

                {/* Statistics Cards */}
                <Can permission="dashboard.stats.management">
                    <div className="grid gap-4 md:grid-cols-3">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Total de Usuários
                                </CardTitle>
                                <Users className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {stats?.totalUsers ?? '—'}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    {stats?.activeUsers
                                        ? `${stats.activeUsers} ativos`
                                        : 'Usuários cadastrados'}
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Total de Roles
                                </CardTitle>
                                <Shield className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {stats?.totalRoles ?? '—'}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Roles cadastradas
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Total de Permissões
                                </CardTitle>
                                <Key className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {stats?.totalPermissions ?? '—'}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Permissões cadastradas
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </Can>

                {/* Quick Actions */}
                <Can permission="dashboard.quick-actions">
                    {quickActions.length > 0 && (
                        <div>
                            <h2 className="text-xl font-semibold mb-4">Ações Rápidas</h2>
                            <div className="grid gap-4 md:grid-cols-3">
                                {quickActions.map((action) => {
                                    const Icon = action.icon;
                                    return (
                                        <Card
                                            key={action.href}
                                            className="hover:border-primary/50 transition-colors cursor-pointer"
                                        >
                                            <Link href={action.href}>
                                                <CardHeader>
                                                    <div className="flex items-center justify-between">
                                                        <Icon
                                                            className={`h-5 w-5 ${action.color}`}
                                                        />
                                                        <ArrowRight className="h-4 w-4 text-muted-foreground" />
                                                    </div>
                                                    <CardTitle className="mt-4">
                                                        {action.title}
                                                    </CardTitle>
                                                    <CardDescription>
                                                        {action.description}
                                                    </CardDescription>
                                                </CardHeader>
                                            </Link>
                                        </Card>
                                    );
                                })}
                            </div>
                        </div>
                    )}
                </Can>

                {/* Management Links */}
                <Can permission="dashboard.management-links">
                    {managementLinks.length > 0 && (
                        <div>
                            <h2 className="text-xl font-semibold mb-4">
                                Gerenciamento
                            </h2>
                            <div className="grid gap-4 md:grid-cols-3">
                                {managementLinks.map((link) => {
                                    const Icon = link.icon;
                                    return (
                                        <Card
                                            key={link.href}
                                            className="hover:border-primary/50 transition-colors"
                                        >
                                            <CardHeader>
                                                <div className="flex items-center justify-between">
                                                    <div className="flex items-center gap-3">
                                                        <Icon className="h-5 w-5 text-muted-foreground" />
                                                        <CardTitle>{link.title}</CardTitle>
                                                    </div>
                                                    <span className="text-2xl font-bold text-muted-foreground">
                                                        {link.count}
                                                    </span>
                                                </div>
                                                <CardDescription className="mt-2">
                                                    {link.title === 'Usuários'
                                                        ? 'Usuários cadastrados'
                                                        : link.title === 'Roles'
                                                        ? 'Roles cadastradas'
                                                        : 'Permissões cadastradas'}
                                                </CardDescription>
                                            </CardHeader>
                                            <CardContent>
                                                <Button
                                                    variant="outline"
                                                    className="w-full"
                                                    asChild
                                                >
                                                    <Link href={link.href}>
                                                        Acessar
                                                        <ArrowRight className="ml-2 h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            </CardContent>
                                        </Card>
                                    );
                                })}
                            </div>
                        </div>
                    )}
                </Can>

                {/* Métricas de Pesquisa */}
                <Can permission="dashboard.research.metrics">
                    <div>
                        <h2 className="text-xl font-semibold mb-4">
                            Métricas de Pesquisa
                        </h2>
                        
                        {/* Cards principais de métricas */}
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-6">
                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">
                                        Total de Questionários
                                    </CardTitle>
                                    <ClipboardList className="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">
                                        {researchStats?.totalQuestionarios ?? '—'}
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        Questionários respondidos
                                    </p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">
                                        Total de Pacientes
                                    </CardTitle>
                                    <UserCheck className="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">
                                        {researchStats?.totalPacientes ?? '—'}
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        Pacientes pesquisados
                                    </p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">
                                        Questionários do Mês
                                    </CardTitle>
                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">
                                        {researchStats?.questionariosMes ?? '—'}
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        Este mês ({new Date().toLocaleString('pt-BR', { month: 'long' })})
                                    </p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">
                                        Taxa de Satisfação
                                    </CardTitle>
                                    <TrendingUp className="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">
                                        {researchStats?.satisfacaoMedia ?? '—'}
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        Média de satisfação
                                    </p>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Cards secundários */}
                        <Can permission="dashboard.research.secondary">
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3 mb-6">
                                <Card>
                                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                        <CardTitle className="text-sm font-medium">
                                            Total de Respostas
                                        </CardTitle>
                                        <FileText className="h-4 w-4 text-muted-foreground" />
                                    </CardHeader>
                                    <CardContent>
                                        <div className="text-2xl font-bold">
                                            {researchStats?.totalRespostas ?? '—'}
                                        </div>
                                        <p className="text-xs text-muted-foreground">
                                            Respostas coletadas
                                        </p>
                                    </CardContent>
                                </Card>

                                <Card>
                                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                        <CardTitle className="text-sm font-medium">
                                            Pacientes do Mês
                                        </CardTitle>
                                        <Users2 className="h-4 w-4 text-muted-foreground" />
                                    </CardHeader>
                                    <CardContent>
                                        <div className="text-2xl font-bold">
                                            {researchStats?.pacientesMes ?? '—'}
                                        </div>
                                        <p className="text-xs text-muted-foreground">
                                            Novos pacientes este mês
                                        </p>
                                    </CardContent>
                                </Card>

                                <Card>
                                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                        <CardTitle className="text-sm font-medium">
                                            Análise de Dados
                                        </CardTitle>
                                        <BarChart3 className="h-4 w-4 text-muted-foreground" />
                                    </CardHeader>
                                    <CardContent>
                                        <Link href="/metricas">
                                            <Button variant="outline" className="w-full">
                                                Ver Métricas Detalhadas
                                                <ArrowRight className="ml-2 h-4 w-4" />
                                            </Button>
                                        </Link>
                                    </CardContent>
                                </Card>
                            </div>
                        </Can>

                        {/* Top Setores e Distribuição */}
                        <Can permission="dashboard.research.analysis">
                            <div className="grid gap-4 md:grid-cols-2">
                                {/* Top Setores */}
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Top 5 Setores Pesquisados</CardTitle>
                                        <CardDescription>
                                            Setores com mais questionários respondidos
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        {researchStats?.topSetores &&
                                        researchStats.topSetores.length > 0 ? (
                                            <div className="space-y-3">
                                                {researchStats.topSetores.map(
                                                    (setor, index) => (
                                                        <div
                                                            key={index}
                                                            className="flex items-center justify-between"
                                                        >
                                                            <div className="flex items-center gap-2">
                                                                <span className="flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary">
                                                                    {index + 1}
                                                                </span>
                                                                <span className="text-sm font-medium">
                                                                    {setor.setor}
                                                                </span>
                                                            </div>
                                                            <span className="text-sm font-bold text-muted-foreground">
                                                                {setor.total}
                                                            </span>
                                                        </div>
                                                    )
                                                )}
                                            </div>
                                        ) : (
                                            <p className="text-sm text-muted-foreground">
                                                Nenhum dado disponível
                                            </p>
                                        )}
                                    </CardContent>
                                </Card>

                                {/* Distribuição por Tipo de Paciente */}
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Distribuição por Tipo</CardTitle>
                                        <CardDescription>
                                            Pacientes vs Acompanhantes
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        {researchStats?.tipoPaciente &&
                                        researchStats.tipoPaciente.length > 0 ? (
                                            <div className="space-y-3">
                                                {researchStats.tipoPaciente.map(
                                                    (tipo, index) => (
                                                        <div
                                                            key={index}
                                                            className="flex items-center justify-between"
                                                        >
                                                            <span className="text-sm font-medium">
                                                                {tipo.tipo_paciente ===
                                                                'Paciente'
                                                                    ? '👤 Paciente'
                                                                    : tipo.tipo_paciente ===
                                                                      'Acompanhante'
                                                                    ? '👥 Acompanhante'
                                                                    : tipo.tipo_paciente}
                                                            </span>
                                                            <span className="text-sm font-bold text-muted-foreground">
                                                                {tipo.total}
                                                            </span>
                                                        </div>
                                                    )
                                                )}
                                            </div>
                                        ) : (
                                            <p className="text-sm text-muted-foreground">
                                                Nenhum dado disponível
                                            </p>
                                        )}
                                    </CardContent>
                                </Card>
                            </div>
                        </Can>
                    </div>
                </Can>
            </div>
        </AppLayout>
    );
}
