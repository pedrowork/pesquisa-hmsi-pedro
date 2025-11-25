import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavManagement } from '@/components/nav-management';
import { NavResearch } from '@/components/nav-research';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { useHasPermission, useIsAdmin } from '@/hooks/usePermissions';
import { Link } from '@inertiajs/react';
import {
    BookOpen,
    Folder,
    LayoutGrid,
    Users,
    Shield,
    Key,
    BedDouble,
    Building,
    CreditCard,
    ClipboardList,
    Heart,
    BarChart3,
    HelpCircle,
    FileText,
} from 'lucide-react';
import AppLogo from './app-logo';

export function AppSidebar() {
    const isAdmin = useIsAdmin();
    const hasDashboardView = useHasPermission('dashboard.view');
    const hasUsersView = useHasPermission('users.view');
    const hasRolesView = useHasPermission('roles.view');
    const hasPermissionsView = useHasPermission('permissions.view');
    const hasQuestionariosView = useHasPermission('questionarios.view');
    const hasLeitosManage = useHasPermission('leitos.manage');
    const hasSetoresManage = useHasPermission('setores.manage');
    const hasTiposConvenioManage = useHasPermission('tipos-convenio.manage');
    const hasSetoresPesquisaManage = useHasPermission('setores-pesquisa.manage');
    const hasPerguntasManage = useHasPermission('perguntas.manage');
    const hasSatisfacaoManage = useHasPermission('satisfacao.manage');
    const hasMetricasView = useHasPermission('metricas.view');

    const allMainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    const allManagementNavItems: NavItem[] = [
        {
            title: 'Usuários',
            href: '/users',
            icon: Users,
        },
        {
            title: 'Roles',
            href: '/roles',
            icon: Shield,
        },
        {
            title: 'Permissões',
            href: '/permissions',
            icon: Key,
        },
    ];

    const allResearchNavItems: NavItem[] = [
        {
            title: 'Questionários',
            href: '/questionarios',
            icon: FileText,
        },
        {
            title: 'Leitos',
            href: '/leitos',
            icon: BedDouble,
        },
        {
            title: 'Setores',
            href: '/setores',
            icon: Building,
        },
        {
            title: 'Tipo de Convênio',
            href: '/tipos-convenio',
            icon: CreditCard,
        },
        {
            title: 'Setor de Pesquisa',
            href: '/setores-pesquisa',
            icon: ClipboardList,
        },
        {
            title: 'Perguntas',
            href: '/perguntas',
            icon: HelpCircle,
        },
        {
            title: 'Satisfação',
            href: '/satisfacao',
            icon: Heart,
        },
        {
            title: 'Métricas',
            href: '/metricas',
            icon: BarChart3,
        },
    ];

    // Filtrar itens baseado em permissões
    const mainNavItems = allMainNavItems.filter(() => {
        if (isAdmin) return true;
        return hasDashboardView;
    });

    const managementNavItems = allManagementNavItems.filter((item) => {
        if (isAdmin) return true;
        if (item.href === '/users') return hasUsersView;
        if (item.href === '/roles') return hasRolesView;
        if (item.href === '/permissions') return hasPermissionsView;
        return false;
    });

    const researchNavItems = allResearchNavItems.filter((item) => {
        if (isAdmin) return true;
        if (item.href === '/questionarios') return hasQuestionariosView;
        if (item.href === '/leitos') return hasLeitosManage;
        if (item.href === '/setores') return hasSetoresManage;
        if (item.href === '/tipos-convenio') return hasTiposConvenioManage;
        if (item.href === '/setores-pesquisa') return hasSetoresPesquisaManage;
        if (item.href === '/perguntas') return hasPerguntasManage;
        if (item.href === '/satisfacao') return hasSatisfacaoManage;
        if (item.href === '/metricas') return hasMetricasView;
        return false;
    });

    const footerNavItems: NavItem[] = [
        {
            title: 'Repository',
            href: 'https://github.com/laravel/react-starter-kit',
            icon: Folder,
        },
        {
            title: 'Documentation',
            href: 'https://laravel.com/docs/starter-kits#react',
            icon: BookOpen,
        },
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                {mainNavItems.length > 0 && <NavMain items={mainNavItems} />}
                {managementNavItems.length > 0 && (
                    <NavManagement items={managementNavItems} />
                )}
                {researchNavItems.length > 0 && (
                    <NavResearch items={researchNavItems} />
                )}
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
