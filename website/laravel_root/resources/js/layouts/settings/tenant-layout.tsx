import * as React from 'react';
import Heading from '@/components/starterkit/heading';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { cn, isSameUrl, resolveUrl } from '@/lib/utils';
import TenantController from '@/actions/App/Http/Controllers/TenantController';
import TenantMemberController from '@/actions/App/Http/Controllers/TenantMemberController';
import { type NavItem, SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

interface TenantNavItem extends NavItem {
    ability?: string;
}

const sidebarNavItems: TenantNavItem[] = [
    {
        title: 'General',
        href: TenantController.edit.url(),
        icon: null,
    },
    {
        title: 'Members',
        href: TenantMemberController.index.url(),
        icon: null,
        ability: 'view_members',
    },
    {
        title: 'Roles & Permissions',
        href: '#',
        icon: null,
        ability: 'view_roles',
    },
    {
        title: 'Security',
        href: '#',
        icon: null,
        ability: 'update',
    },
    {
        title: 'Billing & Subscription',
        href: '#',
        icon: null,
        ability: 'view_billing',
    },
    {
        title: 'Advanced Settings',
        href: '#',
        icon: null,
    },
];

interface TenantSettingsLayoutProps {
    children: React.ReactNode;
    maxWidth?: 'xl' | '2xl' | '3xl' | '4xl' | '5xl' | '6xl' | 'none';
}

export default function TenantSettingsLayout({ children, maxWidth = 'xl' }: TenantSettingsLayoutProps) {
    const { abilities } = usePage<SharedData & { abilities?: Record<string, boolean> }>().props;

    // When server-side rendering, we only render the layout on the client...
    if (typeof window === 'undefined') {
        return null;
    }

    const currentPath = window.location.pathname;

    const outerMaxWidthClass = {
        'xl': 'md:max-w-2xl',
        '2xl': 'md:max-w-3xl',
        '3xl': 'md:max-w-4xl',
        '4xl': 'md:max-w-5xl',
        '5xl': 'md:max-w-6xl',
        '6xl': 'md:max-w-7xl',
        none: '',
    }[maxWidth];

    const innerMaxWidthClass = {
        'xl': 'max-w-xl',
        '2xl': 'max-w-2xl',
        '3xl': 'max-w-3xl',
        '4xl': 'max-w-4xl',
        '5xl': 'max-w-5xl',
        '6xl': 'max-w-6xl',
        none: '',
    }[maxWidth];

    return (
        <div className="px-4 py-6">
            <Heading
                title="Tenant Settings"
                description="Manage your tenant's information and members"
            />

            <div className="flex flex-col lg:flex-row lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <TooltipProvider>
                        <nav className="flex flex-col space-y-1 space-x-0">
                            
                            {sidebarNavItems.map((item, index) => {
                                const enabled = !item.ability || abilities?.[item.ability] === true;
                                const isActive = enabled && isSameUrl(currentPath, item.href);
                                
                                const button = (
                                    <Button
                                        key={`${resolveUrl(item.href)}-${index}`}
                                        size="sm"
                                        variant="ghost"
                                        render={enabled ? <Link href={item.href} /> : undefined}
                                        nativeButton={!enabled}
                                        disabled={!enabled}
                                        className={cn('w-full justify-start', {
                                            'bg-muted': isActive,
                                        })}
                                    >
                                        {item.icon && (
                                            <item.icon className="h-4 w-4" />
                                        )}
                                        {item.title}
                                    </Button>
                                );

                                if (!enabled) {
                                    return (
                                        <Tooltip key={index}>
                                            <TooltipTrigger
                                                render={
                                                    <div className="w-full cursor-not-allowed">
                                                        {button}
                                                    </div>
                                                }
                                            />
                                            <TooltipContent>
                                                You don't have permission to access {item.title}
                                            </TooltipContent>
                                        </Tooltip>
                                    );
                                }

                                return button;
                            })}
                        </nav>
                    </TooltipProvider>
                </aside>

                <div className="flex-1 lg:hidden">
                    <Separator className="my-6" />
                </div>

                <div className={cn('flex-1', outerMaxWidthClass)}>
                    <section className={cn('space-y-12', innerMaxWidthClass)}>
                        {children}
                    </section>
                </div>
            </div>
        </div>
    );
}
