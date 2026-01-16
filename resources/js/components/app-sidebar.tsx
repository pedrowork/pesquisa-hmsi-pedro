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
    UserCheck,
} from 'lucide-react';
import AppLogo from './app-logo';

export function AppSidebar() {
    const isAdmin = useIsAdmin();
    const hasDashboardView = useHasPermission('dashboard.view');
    const hasUsersView = useHasPermission('users.view');
    const hasUsersApprove = useHasPermission('users.approve');
    const hasRolesView = useHasPermission('roles.view');
    const hasPermissionsView = useHasPermission('permissions.view');
    const hasQuestionariosView = useHasPermission('questionarios.view');
    const hasQuestionariosCreate = useHasPermission('questionarios.create');
    // Usuário pode ver Questionários se tiver view OU create (para fazer pesquisa)
    // Sempre exibir se tiver create, mesmo sem view
    const hasQuestionarios = hasQuestionariosView || hasQuestionariosCreate;
    // Verificar permissões granulares (view OU create para cada módulo)
    const hasLeitosView = useHasPermission('leitos.view');
    const hasLeitosCreate = useHasPermission('leitos.create');
    const hasLeitosManage = hasLeitosView || hasLeitosCreate;
    
    const hasSetoresView = useHasPermission('setores.view');
    const hasSetoresCreate = useHasPermission('setores.create');
    const hasSetoresManage = hasSetoresView || hasSetoresCreate;
    
    const hasTiposConvenioView = useHasPermission('tipos-convenio.view');
    const hasTiposConvenioCreate = useHasPermission('tipos-convenio.create');
    const hasTiposConvenioManage = hasTiposConvenioView || hasTiposConvenioCreate;
    
    const hasSetoresPesquisaView = useHasPermission('setores-pesquisa.view');
    const hasSetoresPesquisaCreate = useHasPermission('setores-pesquisa.create');
    const hasSetoresPesquisaManage = hasSetoresPesquisaView || hasSetoresPesquisaCreate;
    
    const hasPerguntasView = useHasPermission('perguntas.view');
    const hasPerguntasCreate = useHasPermission('perguntas.create');
    const hasPerguntasManage = hasPerguntasView || hasPerguntasCreate;
    
    const hasSatisfacaoView = useHasPermission('satisfacao.view');
    const hasSatisfacaoCreate = useHasPermission('satisfacao.create');
    const hasSatisfacaoManage = hasSatisfacaoView || hasSatisfacaoCreate;
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
            title: 'Aprovações Pendentes',
            href: '/admin/users/pending-approval',
            icon: UserCheck,
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
    // Dashboard sempre deve aparecer para usuários autenticados (rota protegida por auth/verified)
    // Se chegou aqui, o usuário está autenticado e pode ver o Dashboard
    const mainNavItems = allMainNavItems.filter(() => {
        // Dashboard sempre aparece para usuários autenticados
        return true;
    });

    const managementNavItems = allManagementNavItems.filter((item) => {
        if (isAdmin) return true;
        if (item.href === '/users') return hasUsersView;
        if (item.href === '/admin/users/pending-approval') return hasUsersApprove || hasUsersView; // Quem pode aprovar ou ver usuários
        if (item.href === '/roles') return hasRolesView;
        if (item.href === '/permissions') return hasPermissionsView;
        return false;
    });

    const researchNavItems = allResearchNavItems.filter((item) => {
        if (isAdmin) return true;
        // Verificar permissões específicas para cada item
        if (item.href === '/questionarios') {
            // SEMPRE permitir se tiver create (para fazer pesquisa) OU view (para visualizar)
            // Isso garante que usuários com questionarios.create vejam o link no sidebar
            return hasQuestionariosCreate || hasQuestionariosView;
        }
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
                {/* Dashboard sempre aparece para usuários autenticados */}
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
