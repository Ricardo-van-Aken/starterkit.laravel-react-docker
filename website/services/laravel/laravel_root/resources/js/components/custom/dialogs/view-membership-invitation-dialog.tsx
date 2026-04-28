import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { MembershipInvitationStatusBadge } from '@/components/custom/badges/membership-invitation-status-badge';
import { type MembershipInvitation } from '@/types';

interface ViewMembershipInvitationDialogProps {
    invitation: MembershipInvitation | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}


export function ViewMembershipInvitationDialog({ invitation, open, onOpenChange }: ViewMembershipInvitationDialogProps) {
    if (!invitation) return null;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>Invitation Details</DialogTitle>
                    <DialogDescription>
                        Details for invitation sent to {invitation.email}.
                    </DialogDescription>
                </DialogHeader>
                
                <div className="grid gap-6 py-4">
                    <div className="flex justify-between items-center">
                        <span className="text-xs font-bold text-muted-foreground uppercase tracking-wider">Status</span>
                        <MembershipInvitationStatusBadge status={invitation.status as any} />
                    </div>

                    <div className="space-y-2">
                        <h4 className="text-xs font-bold text-muted-foreground uppercase tracking-wider">
                            Requested Roles
                        </h4>
                        <div className="flex flex-wrap gap-2">
                            {invitation.roles.length > 0 ? (
                                invitation.roles.map((role: string) => (
                                    <Badge key={role} variant="outline" className="capitalize">
                                        {role}
                                    </Badge>
                                ))
                            ) : (
                                <span className="text-sm text-muted-foreground italic">No roles requested</span>
                            )}
                        </div>
                    </div>

                    <div className="space-y-2">
                        <h4 className="text-xs font-bold text-muted-foreground uppercase tracking-wider">
                            Requested Permissions
                        </h4>
                        <div className="flex flex-wrap gap-2">
                            {invitation.permissions.length > 0 ? (
                                invitation.permissions.map((perm: string) => (
                                    <Badge key={perm} variant="outline" className="text-[10px]">
                                        {perm}
                                    </Badge>
                                ))
                            ) : (
                                <span className="text-sm text-muted-foreground italic">No extra permissions requested</span>
                            )}
                        </div>
                    </div>

                    <div className="flex justify-between items-center bg-muted/50 p-2 rounded-md">
                        <span className="text-xs font-bold text-muted-foreground uppercase tracking-wider">Expires</span>
                        <span className="text-sm font-mono text-muted-foreground">{invitation.expires_at || 'Never'}</span>
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" onClick={() => onOpenChange(false)}>
                        Close
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
