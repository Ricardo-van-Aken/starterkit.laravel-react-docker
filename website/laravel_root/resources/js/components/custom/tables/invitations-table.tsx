import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { MoreHorizontal, Trash2 } from 'lucide-react';
import { router } from '@inertiajs/react';

export interface Invitation {
    uuid: string;
    email: string;
    roles: string[];
    status: string;
    expires_at: string;
}

interface InvitationsTableProps {
    invitations: Invitation[];
}

export function InvitationsTable({ invitations }: InvitationsTableProps) {
    const handleRevoke = (uuid: string) => {
        if (confirm(`Are you sure you want to revoke this invitation?`)) {
            router.delete(`/tenant/invitations/${uuid}`, {
                preserveScroll: true,
            });
        }
    };

    return (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Email</TableHead>
                    <TableHead>Role(s)</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead className="text-right whitespace-nowrap">Expires</TableHead>
                    <TableHead className="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {invitations.map((invitation, i) => (
                    <TableRow key={i}>
                        <TableCell>{invitation.email}</TableCell>
                        <TableCell>
                            <div className="flex flex-wrap gap-1">
                                {invitation.roles.map((role) => (
                                    <Badge key={role} variant="outline" className="capitalize">{role}</Badge>
                                ))}
                            </div>
                        </TableCell>
                        <TableCell>
                            <span className="text-xs text-amber-600 font-medium">{invitation.status}</span>
                        </TableCell>
                        <TableCell className="text-right text-xs text-muted-foreground">
                            {invitation.expires_at}
                        </TableCell>
                        <TableCell className="text-right">
                            <DropdownMenu>
                                <DropdownMenuTrigger
                                    render={
                                        <Button variant="ghost" className="h-8 w-8 p-0" />
                                    }
                                >
                                    <span className="sr-only">Open menu</span>
                                    <MoreHorizontal className="h-4 w-4" />
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem 
                                        variant="destructive"
                                        onClick={() => handleRevoke(invitation.uuid)}
                                    >
                                        <Trash2 className="mr-2 h-4 w-4" />
                                        <span>Revoke</span>
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
