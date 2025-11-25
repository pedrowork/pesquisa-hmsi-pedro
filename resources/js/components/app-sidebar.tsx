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

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const managementNavItems: NavItem[] = [
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

const researchNavItems: NavItem[] = [
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

export function AppSidebar() {
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
                <NavMain items={mainNavItems} />
                <NavManagement items={managementNavItems} />
                <NavResearch items={researchNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
