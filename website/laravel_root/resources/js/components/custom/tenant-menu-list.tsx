import * as React from "react"
import { Plus, Check } from "lucide-react"
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from "@/components/ui/dropdown-menu"
import { TenantInfo } from '@/components/custom/tenant-info';
import { type Tenant } from '@/types';

interface TenantMenuListProps {
    tenants: Tenant[]
    activeTenant?: Tenant | null
    onSwitch: (uuid: string) => void
    onCreate: () => void
}

export function TenantMenuList({
    tenants,
    activeTenant,
    onSwitch,
    onCreate
}: TenantMenuListProps) {
    return (
        <>
            <DropdownMenuGroup>
                <DropdownMenuLabel className="text-xs text-muted-foreground uppercase px-2 py-1.5 font-semibold tracking-wider">
                    Tenants
                </DropdownMenuLabel>

                {tenants.map((tenant) => (
                    <DropdownMenuItem
                        key={tenant.uuid}
                        onClick={() => onSwitch(tenant.uuid)}
                        className="gap-2 p-2"
                    >
                        <TenantInfo tenant={tenant} roles={tenant.roles} />
                        {activeTenant?.uuid === tenant.uuid && (
                            <Check className="ml-auto size-4 text-primary" />
                        )}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuGroup>

            <DropdownMenuSeparator />

            <DropdownMenuItem
                className="gap-2 p-2"
                onClick={onCreate}
            >
                <div className="flex size-6 items-center justify-center rounded-md border bg-transparent">
                    <Plus className="size-4" />
                </div>
                <div className="font-medium text-muted-foreground">Add tenant</div>
            </DropdownMenuItem>
        </>
    )
}
