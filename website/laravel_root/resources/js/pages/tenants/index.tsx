import { TenantCard } from '@/components/custom/cards/tenant-card';
import AppLayout from '@/layouts/app-layout';
import tenantRoutes from '@/routes/tenants';
import { type BreadcrumbItem, type TenantIndexItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Building2 } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tenants',
        href: tenantRoutes.index.url(),
    },
];

export default function Index({ tenants = [] }: { tenants: TenantIndexItem[] }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenants" />

            <div className="flex flex-1 flex-col gap-8 p-6">
                <div>
                    <h2 className="text-3xl font-bold tracking-tight">Your Tenants</h2>
                    <p className="text-muted-foreground mt-1 text-base">
                        Select a tenant to manage its organisation units and users.
                    </p>
                </div>

                {tenants.length > 0 ? (
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {tenants.map((tenant) => (
                            <Link 
                                key={tenant.uuid}
                                href={tenantRoutes.switch.url(tenant, { query: { redirect_to: '/tenant/dashboard' } })}
                                method="post"
                                className="block h-full outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 rounded-xl"
                            >
                                <TenantCard 
                                    tenant={tenant} 
                                    users={tenant.users}
                                    abilities={tenant.abilities}
                                    className="h-full focus-visible:ring-0" 
                                />
                            </Link>
                        ))}
                    </div>
                ) : (
                    <div className="flex flex-col items-center justify-center rounded-xl border-2 border-dashed p-24 text-center">
                        <div className="rounded-full bg-muted p-6 mb-6">
                            <Building2 className="h-10 w-10 text-muted-foreground" />
                        </div>
                        <h3 className="text-2xl font-semibold">No tenants found</h3>
                        <p className="text-muted-foreground mt-2 max-w-sm text-lg leading-relaxed">
                            It looks like you don't have any tenants yet. Contact your administrator to get started.
                        </p>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
