import { PlaceholderPattern } from '@/components/starterkit/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { index as tenantsIndex } from '@/routes/tenants'
import { dashboard as tenantDashboard} from '@/routes/tenant';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { SubscriptionCard } from '@/components/custom/cards/subscription-card';

import { usePage } from '@inertiajs/react';

export default function Dashboard() {
    const { auth } = usePage().props as any;

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Tenants',
            href: tenantsIndex().url,
        },
        {
            title: auth.active_tenant.name,
            href: tenantDashboard().url,
        },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                    <SubscriptionCard className="shadow-none border-sidebar-border/70 dark:border-sidebar-border" />
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                </div>
                <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                </div>
            </div>
        </AppLayout>
    );
}
