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
} from 'lucide-react';
import { Button } from '@/components/ui/button';

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

interface DashboardProps {
    stats?: DashboardStats;
}

export default function Dashboard({ stats }: DashboardProps) {
    const quickActions = [
        {
            title: 'Novo Usuário',
            description: 'Cadastrar um novo usuário no sistema',
            href: '/users/create',
            icon: UserPlus,
            color: 'text-blue-500',
        },
        {
            title: 'Nova Role',
            description: 'Criar um novo grupo de usuários',
            href: '/roles/create',
            icon: ShieldPlus,
            color: 'text-purple-500',
        },
        {
            title: 'Permissões',
            description: 'Gerenciar permissões do sistema',
            href: '/permissions',
            icon: KeyRound,
            color: 'text-green-500',
        },
    ];

    const managementLinks = [
        {
            title: 'Usuários',
            description: 'Gerenciar usuários do sistema',
            href: '/users',
            icon: Users,
            count: stats?.totalUsers || 0,
        },
        {
            title: 'Roles',
            description: 'Gerenciar grupos de usuários',
            href: '/roles',
            icon: Shield,
            count: stats?.totalRoles || 0,
        },
        {
            title: 'Permissões',
            description: 'Visualizar permissões disponíveis',
            href: '/permissions',
            icon: Key,
            count: stats?.totalPermissions || 0,
        },
    ];

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

                {/* Quick Actions */}
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

                {/* Management Links */}
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
            </div>
        </AppLayout>
    );
}
