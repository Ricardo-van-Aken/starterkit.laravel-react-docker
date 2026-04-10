import TenantController from '@/actions/App/Http/Controllers/TenantController';
import TenantMemberController from '@/actions/App/Http/Controllers/TenantMemberController';
import HeadingSmall from '@/components/starterkit/heading-small';
import { MembersTable, type Member } from '@/components/custom/tables/members-table';
import { OutgoingMembershipInvitationsTable } from '@/components/custom/datatables/outgoing-membership-invitations-table';
import AppLayout from '@/layouts/app-layout';
import TenantSettingsLayout from '@/layouts/settings/tenant-layout';
import { type BreadcrumbItem, type SharedData, type Abilities, type PaginatedResponse, type MembershipInvitation } from '@/types';
import { Head, usePage } from '@inertiajs/react';

interface MembersPageProps {
    members: PaginatedResponse<Member>;
    invitations: PaginatedResponse<MembershipInvitation>;
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

export default function MembersPage({ 
    members, 
    invitations, 
    available_roles, 
    available_permissions, 
    abilities 
}: MembersPageProps) {
    const { auth } = usePage<SharedData>().props;
    
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

                        <div className="rounded-md border overflow-x-auto">
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

                        <OutgoingMembershipInvitationsTable 
                            invitations={invitations}
                            availableRoles={available_roles}
                            availablePermissions={available_permissions}
                        />
                    </div>
                </div>
            </TenantSettingsLayout>
        </AppLayout>
    );
}
