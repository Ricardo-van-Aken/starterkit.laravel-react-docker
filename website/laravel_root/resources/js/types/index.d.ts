import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
    active_tenant: Tenant | null;
    tenants: Tenant[];
}

export type Abilities = Record<string, boolean>;

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    abilities?: Abilities | null;
    [key: string]: unknown;
}

export interface User {
    uuid: string;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    tenant_roles?: string[];
    [key: string]: unknown; // This allows for additional properties...
}

export interface Tenant {
    id: number;
    uuid: string;
    name: string;
    roles?: string[];
    created_at: string;
}

export interface TenantIndexItem extends Tenant {
    users_count: number;
    organisation_units_count: number;
    users?: User[];
    abilities?: Abilities;
}

export interface TenantDashboardItem extends Tenant {
    users_count: number;
    organisation_units_count: number;
}