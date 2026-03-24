import TenantController from '@/actions/App/Http/Controllers/TenantController';
import TenantMemberController from '@/actions/App/Http/Controllers/TenantMemberController';
import HeadingSmall from '@/components/starterkit/heading-small';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import TenantSettingsLayout from '@/layouts/settings/tenant-layout';
import { type BreadcrumbItem, type Tenant, type User } from '@/types';
import { useInitials } from '@/hooks/use-initials';
import { Head } from '@inertiajs/react';

interface Member extends User {
    roles: string[];
    permissions: string[];
}

interface Invitation {
    email: string;
    role: string;
    status: string;
    expires_at: string;
}

interface MembersPageProps {
    tenant: Tenant;
    members: Member[];
    invitations: Invitation[];
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

export default function MembersPage({ tenant, members, invitations }: MembersPageProps) {
    const getInitials = useInitials();

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
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Member</TableHead>
                                        <TableHead>Role</TableHead>
                                        <TableHead className="hidden md:table-cell">Permissions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {members.map((member) => (
                                        <TableRow key={member.id}>
                                            <TableCell className="font-medium">
                                                <div className="flex items-center gap-3">
                                                    <Avatar className="h-8 w-8">
                                                        <AvatarImage src={member.avatar} alt={member.name} />
                                                        <AvatarFallback>
                                                            {getInitials(member.name)}
                                                        </AvatarFallback>
                                                    </Avatar>
                                                    <div className="flex flex-col">
                                                        <span className="text-sm font-medium">{member.name}</span>
                                                        <span className="text-xs text-muted-foreground">{member.email}</span>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex flex-wrap gap-1">
                                                    {member.roles.map((role) => (
                                                        <Badge key={role} variant="secondary" className="capitalize">
                                                            {role}
                                                        </Badge>
                                                    ))}
                                                </div>
                                            </TableCell>
                                            <TableCell className="hidden md:table-cell">
                                                <div className="flex flex-wrap gap-1 max-w-[200px]">
                                                    {member.permissions.slice(0, 3).map((permission) => (
                                                        <span key={permission} className="text-[10px] text-muted-foreground bg-secondary/50 px-1 rounded">
                                                            {permission}
                                                        </span>
                                                    ))}
                                                    {member.permissions.length > 3 && (
                                                        <span className="text-[10px] text-muted-foreground">
                                                            +{member.permissions.length - 3} more
                                                        </span>
                                                    )}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    </div>

                    {/* Invitations Table (Placeholder) */}
                    <div className="space-y-6">
                        <HeadingSmall
                            title="Outstanding Invitations"
                            description="Invitations that have been sent but not yet accepted."
                        />

                        <div className="rounded-md border opacity-60">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Email</TableHead>
                                        <TableHead>Role</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead className="text-right whitespace-nowrap">Expires</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {invitations.map((invitation, i) => (
                                        <TableRow key={i}>
                                            <TableCell>{invitation.email}</TableCell>
                                            <TableCell>
                                                <Badge variant="outline">{invitation.role}</Badge>
                                            </TableCell>
                                            <TableCell>
                                                <span className="text-xs text-amber-600 font-medium">{invitation.status}</span>
                                            </TableCell>
                                            <TableCell className="text-right text-xs text-muted-foreground">
                                                {invitation.expires_at}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                        <p className="text-xs text-muted-foreground italic">
                            Invitation management is coming soon.
                        </p>
                    </div>
                </div>
            </TenantSettingsLayout>
        </AppLayout>
    );
}
