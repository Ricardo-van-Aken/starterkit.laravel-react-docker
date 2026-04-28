import AppLogo from '@/components/starterkit/app-logo';
import { NavFooter } from '@/components/starterkit/nav-footer';
import { NavMain } from '@/components/starterkit/nav-main';
import { TenantMenuDropdown } from '@/components/custom/tenant-menu-dropdown';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarRail,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarGroupAction,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as tenantsIndex } from '@/routes/tenants';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { ArrowUpRight, BookOpen, Building, Folder, LayoutGrid } from 'lucide-react';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Repository',
    //     href: 'https://github.com/laravel/react-starter-kit',
    //     icon: Folder,
    // },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits#react',
    //     icon: BookOpen,
    // },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="sidebar">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            size="lg"
                            render={<Link href={dashboard()} prefetch />}
                        >
                            <AppLogo />
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <SidebarGroup className="py-0">
                    <SidebarGroupLabel>Tenants</SidebarGroupLabel>
                    <SidebarGroupAction
                        className="right-2.5 top-1.5 h-5 w-auto aspect-auto px-1.5 gap-1 border border-sidebar-border bg-background text-sidebar-foreground shadow-xs opacity-100! hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                        render={
                            <Link href={tenantsIndex().url} title="View all tenants" />
                        }
                    >
                        <span className="text-[10px] font-medium">View all</span>
                        <ArrowUpRight className="size-3" />
                    </SidebarGroupAction>
                    <TenantMenuDropdown />
                </SidebarGroup>
            </SidebarFooter>
            <SidebarRail />
        </Sidebar>
    );
}
