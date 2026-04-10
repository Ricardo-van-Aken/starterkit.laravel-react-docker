import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { IncomingMembershipInvitationsTable } from '@/components/custom/tables/incoming-membership-invitations-table';

interface IncomingInvitationsProps {
    invitations: any[];
}

export function IncomingInvitationsCard({ invitations }: IncomingInvitationsProps) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Pending Invitations</CardTitle>
                <CardDescription>You have been invited to join these organizations.</CardDescription>
            </CardHeader>
            <CardContent>
                <IncomingMembershipInvitationsTable invitations={invitations} />
            </CardContent>
        </Card>
    );
}
