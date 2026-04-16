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
import { type MemberWithAbilities } from '@/components/custom/tables/members-table';

interface ViewMemberDialogProps {
    member: MemberWithAbilities | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

export function ViewMemberDialog({ member, open, onOpenChange }: ViewMemberDialogProps) {
    if (!member) return null;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>Member Details</DialogTitle>
                    <DialogDescription>
                        Full access details for {member.name}.
                    </DialogDescription>
                </DialogHeader>
                
                <div className="grid gap-6 py-4">
                    <div className="space-y-2">
                        <h4 className="text-xs font-bold text-muted-foreground uppercase tracking-wider">
                            Roles
                        </h4>
                        <div className="flex flex-wrap gap-2">
                            {member.roles.length > 0 ? (
                                member.roles.map((role) => (
                                    <Badge key={role} variant="secondary" className="capitalize">
                                        {role}
                                    </Badge>
                                ))
                            ) : (
                                <span className="text-sm text-muted-foreground italic">No roles assigned</span>
                            )}
                        </div>
                    </div>

                    <div className="space-y-2">
                        <h4 className="text-xs font-bold text-muted-foreground uppercase tracking-wider">
                            Permissions
                        </h4>
                        <div className="flex flex-wrap gap-2">
                            {member.permissions.length > 0 ? (
                                member.permissions.map((perm) => (
                                    <Badge key={perm} variant="outline" className="text-[10px]">
                                        {perm}
                                    </Badge>
                                ))
                            ) : (
                                <span className="text-sm text-muted-foreground italic">No extra permissions</span>
                            )}
                        </div>
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
