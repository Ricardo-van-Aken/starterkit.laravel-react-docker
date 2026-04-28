import TenantController from '@/actions/App/Http/Controllers/TenantController';
import TenantMemberController from '@/actions/App/Http/Controllers/TenantMemberController';
import HeadingSmall from '@/components/starterkit/heading-small';
import { MembersTable, type MemberWithAbilities } from '@/components/custom/datatables/members-table';
import { OutgoingMembershipInvitationsTable, type InvitationWithAbilities } from '@/components/custom/datatables/outgoing-membership-invitations-table';
import AppLayout from '@/layouts/app-layout';
import TenantSettingsLayout from '@/layouts/settings/tenant-layout';
import { type BreadcrumbItem, type SharedData, type Abilities, type PaginatedResponse, type MembershipInvitation } from '@/types';
import { Head, usePage } from '@inertiajs/react';

interface MembersPageProps {
    members: PaginatedResponse<MemberWithAbilities>;
    invitations: PaginatedResponse<InvitationWithAbilities>;
    available_roles: string[];
    assignable_roles: string[];
    available_permissions: string[];
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

export default function MembersPage({ 
    members, 
    invitations, 
    available_roles, 
    assignable_roles,
    available_permissions, 
}: MembersPageProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenant Members" />

            <TenantSettingsLayout maxWidth="5xl">
                <div className="space-y-12">
                    {/* Members Table */}
                    <div className="space-y-6">
                        <HeadingSmall
                            title="Members"
                            description="A list of all users that have access to this tenant."
                        />

                        <MembersTable
                            members={members}
                            availableRoles={available_roles}
                            assignableRoles={assignable_roles}
                            availablePermissions={available_permissions}
                        />
                    </div>

                    {/* Invitations Table */}
                    <div className="space-y-6">
                        <HeadingSmall
                            title="Outstanding Invitations"
                            description="Invitations that have been sent but not yet accepted."
                        />

                        <OutgoingMembershipInvitationsTable 
                            invitations={invitations}
                            availableRoles={available_roles}
                            assignableRoles={assignable_roles}
                            availablePermissions={available_permissions}
                        />
                    </div>
                </div>
            </TenantSettingsLayout>
        </AppLayout>
    );
}
