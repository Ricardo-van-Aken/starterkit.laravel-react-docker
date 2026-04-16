import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Tooltip, TooltipContent, TooltipTrigger, TooltipProvider } from '@/components/ui/tooltip';
import { cn } from '@/lib/utils';

interface RoleEditorProps {
    selectedRoles: string[];
    onChange: (roles: string[]) => void;
    availableRoles: string[];
    manageableRoles: string[];
}

export function RoleEditor({
    selectedRoles,
    onChange,
    availableRoles,
    manageableRoles,
}: RoleEditorProps) {
    const toggleRole = (role: string, checked: boolean) => {
        if (checked) {
            onChange([...selectedRoles, role]);
        } else {
            onChange(selectedRoles.filter((r) => r !== role));
        }
    };

    return (
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 py-4">
            {availableRoles.map((role) => {
                const isRestrictedRole = !manageableRoles.includes(role);
                
                return (
                    <Tooltip key={role}>
                        <TooltipTrigger 
                            render={
                                <div className={cn(
                                    "flex items-center space-x-2 border rounded-md p-3 transition-colors",
                                    isRestrictedRole ? "bg-muted/50 opacity-70 cursor-not-allowed" : "hover:bg-accent/50 cursor-pointer"
                                )}>
                                    <Checkbox 
                                        id={role} 
                                        checked={selectedRoles.includes(role)}
                                        onCheckedChange={(checked) => !isRestrictedRole && toggleRole(role, checked as boolean)}
                                        disabled={isRestrictedRole}
                                    />
                                    <Label 
                                        htmlFor={role}
                                        className={cn(
                                            "capitalize font-medium leading-none",
                                            isRestrictedRole ? "cursor-not-allowed text-muted-foreground" : "cursor-pointer"
                                        )}
                                    >
                                        {role.replace(/_/g, ' ')}
                                    </Label>
                                </div>
                            }
                        />
                        {isRestrictedRole && (
                            <TooltipContent side="bottom">
                                You don't have permission to manage the {role} role
                            </TooltipContent>
                        )}
                    </Tooltip>
                );
            })}
            {availableRoles.length === 0 && (
                <div className="col-span-full py-8 text-center border border-dashed rounded-lg">
                    <p className="text-sm text-muted-foreground">No roles available to assign.</p>
                </div>
            )}
        </div>
    );
}
