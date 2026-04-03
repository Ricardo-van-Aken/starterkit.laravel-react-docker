import { AppHeader } from '@/components/custom/app-header';
import { AppSidebar } from '@/components/custom/app-sidebar';
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';
import { TooltipProvider } from '@/components/ui/tooltip';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';

export default function AppCustomLayout({
    children,
    breadcrumbs = [],
}: PropsWithChildren<{ breadcrumbs?: BreadcrumbItem[] }>) {
    const { auth, sidebarOpen } = usePage<SharedData>().props;

    return (
        <TooltipProvider delay={300}>
            <SidebarProvider defaultOpen={sidebarOpen}>
                <AppSidebar />
                <SidebarInset>
                    <AppHeader breadcrumbs={breadcrumbs} user={auth.user} />
                    <div className="flex flex-1 flex-col gap-4 p-4">
                        {children}
                    </div>
                </SidebarInset>
            </SidebarProvider>
        </TooltipProvider>
    );
}
