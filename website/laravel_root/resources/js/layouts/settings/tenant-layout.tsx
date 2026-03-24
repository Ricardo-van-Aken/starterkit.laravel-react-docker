import Heading from '@/components/starterkit/heading';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { cn, isSameUrl, resolveUrl } from '@/lib/utils';
import TenantController from '@/actions/App/Http/Controllers/TenantController';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';

const sidebarNavItems: NavItem[] = [
    {
        title: 'General',
        href: TenantController.edit.url(),
        icon: null,
    },
    {
        title: 'Members',
        href: '#',
        icon: null,
    },
    {
        title: 'Roles & Permissions',
        href: '#',
        icon: null,
    },
    {
        title: 'Security',
        href: '#',
        icon: null,
    },
    {
        title: 'Billing & Subscription',
        href: '#',
        icon: null,
    },
];

export default function TenantSettingsLayout({ children }: PropsWithChildren) {
    // When server-side rendering, we only render the layout on the client...
    if (typeof window === 'undefined') {
        return null;
    }

    const currentPath = window.location.pathname;

    return (
        <div className="px-4 py-6">
            <Heading
                title="Tenant Settings"
                description="Manage your tenant's information and members"
            />

            <div className="flex flex-col lg:flex-row lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav className="flex flex-col space-y-1 space-x-0">
                        {sidebarNavItems.map((item, index) => (
                            <Button
                                key={`${resolveUrl(item.href)}-${index}`}
                                size="sm"
                                variant="ghost"
                                render={<Link href={item.href} />}
                                nativeButton={false}
                                className={cn('w-full justify-start', {
                                    'bg-muted': isSameUrl(
                                        currentPath,
                                        item.href,
                                    ),
                                })}
                            >
                                {item.icon && (
                                    <item.icon className="h-4 w-4" />
                                )}
                                {item.title}
                            </Button>
                        ))}
                    </nav>
                </aside>

                <Separator className="my-6 lg:hidden" />

                <div className="flex-1 md:max-w-2xl">
                    <section className="max-w-xl space-y-12">
                        {children}
                    </section>
                </div>
            </div>
        </div>
    );
}
