import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { resolveUrl } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';

export function NavResearch({ items = [] }: { items: NavItem[] }) {
    const page = usePage();
    const currentUrl = page.url;

    // Verifica se algum item está ativo para deixar o grupo expandido por padrão
    const hasActiveItem = items.some(
        (item) =>
            currentUrl &&
            typeof currentUrl === 'string' &&
            currentUrl.startsWith(resolveUrl(item.href)),
    );

    // Se houver itens, deixar aberto por padrão
    // Isso garante que usuários vejam as opções de pesquisa imediatamente
    const shouldBeOpen = hasActiveItem || items.length > 0;

    return (
        <Collapsible defaultOpen={shouldBeOpen} className="group/collapsible">
            <SidebarGroup>
                <SidebarGroupLabel asChild>
                    <CollapsibleTrigger className="flex w-full cursor-pointer items-center justify-between">
                        <span>Pesquisa</span>
                        <ChevronDown className="ml-auto h-4 w-4 transition-transform group-data-[state=open]/collapsible:rotate-180" />
                    </CollapsibleTrigger>
                </SidebarGroupLabel>
                <CollapsibleContent>
                    <SidebarGroupContent>
                        <SidebarMenu>
                            {items.map((item) => {
                                const isActive =
                                    currentUrl &&
                                    typeof currentUrl === 'string' &&
                                    currentUrl.startsWith(
                                        resolveUrl(item.href),
                                    );
                                return (
                                    <SidebarMenuItem key={item.title}>
                                        <SidebarMenuButton
                                            asChild
                                            isActive={isActive}
                                            tooltip={{ children: item.title }}
                                        >
                                            <Link href={item.href} prefetch>
                                                {item.icon && <item.icon />}
                                                <span>{item.title}</span>
                                            </Link>
                                        </SidebarMenuButton>
                                    </SidebarMenuItem>
                                );
                            })}
                        </SidebarMenu>
                    </SidebarGroupContent>
                </CollapsibleContent>
            </SidebarGroup>
        </Collapsible>
    );
}
