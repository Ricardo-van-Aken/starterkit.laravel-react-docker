import TenantController from '@/actions/App/Http/Controllers/TenantController';
import TenantMemberController from '@/actions/App/Http/Controllers/TenantMemberController';
import HeadingSmall from '@/components/starterkit/heading-small';
import { MembersTable, type Member } from '@/components/custom/tables/members-table';
import { InvitationsTable, type Invitation } from '@/components/custom/tables/invitations-table';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import TenantSettingsLayout from '@/layouts/settings/tenant-layout';
import { type BreadcrumbItem, type SharedData, type Tenant, type User, type Abilities } from '@/types';
import { Head, usePage } from '@inertiajs/react';


interface MembersPageProps {
    members: Member[];
    invitations: Invitation[];
    available_roles: string[];
    available_permissions: string[];
    abilities: Abilities;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tenant settings',
        href: TenantController.edit.url(),
    },
    {
        title: 'Members',
        href: TenantMemberController.index.url(),
    },
];

export default function MembersPage({ members, invitations, available_roles, available_permissions, abilities }: MembersPageProps) {
    const { auth } = usePage<SharedData>().props;
    const tenant = auth.active_tenant!;
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenant Members" />

            <TenantSettingsLayout>
                <div className="space-y-12">
                    {/* Members Table */}
                    <div className="space-y-6">
                        <HeadingSmall
                            title="Members"
                            description="A list of all users that have access to this tenant."
                        />

                        <div className="rounded-md border">
                            <MembersTable
                                members={members}
                                availableRoles={available_roles}
                                availablePermissions={available_permissions}
                            />
                        </div>
                    </div>

                    {/* Invitations Table */}
                    <div className="space-y-6">
                        <HeadingSmall
                            title="Outstanding Invitations"
                            description="Invitations that have been sent but not yet accepted."
                        />

                        <div className="rounded-md border">
                            <InvitationsTable invitations={invitations} />
                        </div>
                    </div>
                </div>
            </TenantSettingsLayout>
        </AppLayout>
    );
}
