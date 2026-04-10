import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

interface RoleEditorProps {
    selectedRoles: string[];
    onChange: (roles: string[]) => void;
    availableRoles: string[];
}

export function RoleEditor({
    selectedRoles,
    onChange,
    availableRoles,
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
            {availableRoles.map((role) => (
                <div key={role} className="flex items-center space-x-3 p-3 rounded-lg border bg-card hover:bg-accent/50 transition-colors">
                    <Checkbox
                        id={`role-${role}`}
                        checked={selectedRoles.includes(role)}
                        onCheckedChange={(checked) => toggleRole(role, checked === true)}
                    />
                    <Label htmlFor={`role-${role}`} className="flex-1 capitalize cursor-pointer font-medium leading-none">
                        {role.replace(/_/g, ' ')}
                    </Label>
                </div>
            ))}
            {availableRoles.length === 0 && (
                <div className="col-span-full py-8 text-center text-muted-foreground italic">
                    No roles available to assign.
                </div>
            )}
        </div>
    );
}
