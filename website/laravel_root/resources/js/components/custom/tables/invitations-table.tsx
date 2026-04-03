import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

export interface Invitation {
    email: string;
    role: string;
    status: string;
    expires_at: string;
}

interface InvitationsTableProps {
    invitations: Invitation[];
}

export function InvitationsTable({ invitations }: InvitationsTableProps) {
    return (
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
    );
}
