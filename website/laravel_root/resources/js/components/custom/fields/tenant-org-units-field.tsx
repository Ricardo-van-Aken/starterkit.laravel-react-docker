import { Building2 } from 'lucide-react';
import { Field, FieldLabel, FieldContent } from '@/components/ui/field';

interface TenantOrgUnitsFieldProps {
    count: number;
}

export function TenantOrgUnitsField({ count }: TenantOrgUnitsFieldProps) {
    return (
        <Field>
            <FieldLabel className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground/70">
                Org Units
            </FieldLabel>
            <FieldContent className="flex-row items-center gap-2 text-foreground text-sm font-semibold">
                <Building2 className="size-4 shrink-0 text-primary/60" />
                <span>{count}</span>
            </FieldContent>
        </Field>
    );
}
