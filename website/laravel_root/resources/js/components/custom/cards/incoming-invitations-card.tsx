import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { TenantInfo } from '@/components/custom/tenant-info';
import { router } from '@inertiajs/react';

interface IncomingInvitationsProps {
    invitations: any[];
}

export function IncomingInvitationsCard({ invitations }: IncomingInvitationsProps) {
    const acceptInvitation = (uuid: string) => {
        router.post(`/tenant-invitations/${uuid}/accept`);
    };

    const declineInvitation = (uuid: string) => {
        if (confirm('Are you sure you want to decline this invitation?')) {
            router.post(`/tenant-invitations/${uuid}/decline`);
        }
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>Pending Invitations</CardTitle>
                <CardDescription>You have been invited to join these organizations.</CardDescription>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Organization</TableHead>
                            <TableHead>Message</TableHead>
                            <TableHead className="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {invitations.map((invitation) => (
                            <TableRow key={invitation.uuid}>
                                <TableCell className="font-medium py-3">
                                    <div className="flex items-center gap-3">
                                        <TenantInfo tenant={invitation.tenant} roles={invitation.roles} />
                                    </div>
                                </TableCell>
                                <TableCell>
                                    You have been invited to join this tenant as a member.
                                </TableCell>
                                <TableCell className="text-right">
                                    <div className="flex justify-end gap-2">
                                        <Button 
                                            variant="outline" 
                                            size="sm" 
                                            onClick={() => declineInvitation(invitation.uuid)}
                                        >
                                            Decline
                                        </Button>
                                        <Button 
                                            size="sm" 
                                            onClick={() => acceptInvitation(invitation.uuid)}
                                        >
                                            Accept
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    );
}
