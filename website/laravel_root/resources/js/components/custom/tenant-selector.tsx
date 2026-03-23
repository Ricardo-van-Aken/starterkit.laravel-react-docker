"use client"

import * as React from "react"
import { Building2, ChevronsUpDown, Plus, Check } from "lucide-react"

import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  useSidebar,
} from "@/components/ui/sidebar"
import { usePage, router } from '@inertiajs/react';
import { type SharedData } from '@/types';
import TenantController from '@/actions/App/Http/Controllers/TenantController';
import { CreateTenantDialog } from './dialogs/create-tenant-dialog';
import { Avatar, AvatarImage, AvatarIconFallback } from './avatar';

export function TenantSelector() {
  const { auth } = usePage<SharedData>().props
  const { isMobile } = useSidebar()
  const [showCreateDialog, setShowCreateDialog] = React.useState(false)

  const activeTenant = auth.tenant
  const tenants = auth.tenants || []

  const handleSwitch = (uuid: string) => {
    router.post(TenantController.switch.url(), { uuid })
  }

  if (!activeTenant) {
      return (
        <>
            <SidebarMenu>
              <SidebarMenuItem>
                <SidebarMenuButton size="lg" onClick={() => setShowCreateDialog(true)}>
                    <div className="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
                        <Plus className="size-4" />
                    </div>
                    <div className="grid flex-1 text-left text-sm leading-tight">
                        <span className="truncate font-semibold">Create Tenant</span>
                        <span className="truncate text-xs">Getting started</span>
                    </div>
                </SidebarMenuButton>
              </SidebarMenuItem>
            </SidebarMenu>
            <CreateTenantDialog 
                open={showCreateDialog} 
                onOpenChange={setShowCreateDialog} 
            />
        </>
      )
  }

  return (
    <>
        <SidebarMenu>
            <SidebarMenuItem>
                <DropdownMenu>

                    {/* Tenant Selector Trigger */}
                    <DropdownMenuTrigger
                        render={
                            <SidebarMenuButton
                                size="lg"
                                className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                            />
                        }
                    >
                        <Avatar className="size-8 rounded-lg">
                            <AvatarIconFallback seed={activeTenant.uuid} />
                        </Avatar>
                        <div className="grid flex-1 text-left text-sm leading-tight">
                            <span className="truncate font-semibold">{activeTenant.name}</span>
                            <span className="truncate text-xs">{auth.roles.join(', ')}</span>
                        </div>
                        <ChevronsUpDown className="ml-auto" />
                    </DropdownMenuTrigger>

                    <DropdownMenuContent
                        className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        align="start"
                        side={isMobile ? "bottom" : "right"}
                        sideOffset={4}
                    >
                        <DropdownMenuGroup>
                            <DropdownMenuLabel className="text-xs text-muted-foreground uppercase px-2 py-1.5 font-semibold tracking-wider">
                                Tenants
                            </DropdownMenuLabel>

                            {/* List of Tenants */}
                            {tenants.map((tenant) => (
                                <DropdownMenuItem
                                    key={tenant.uuid}
                                    onClick={() => handleSwitch(tenant.uuid)}
                                    className="gap-2 p-2"
                                >
                                    <Avatar size="sm" className="size-6 rounded-md">
                                        <AvatarIconFallback seed={tenant.uuid} iconSize="size-3.5" variant="square" />
                                    </Avatar>
                                    <span className="flex-1 truncate">{tenant.name}</span>
                                    {activeTenant.uuid === tenant.uuid && (
                                        <Check className="ml-auto size-4 text-primary" />
                                    )}
                                </DropdownMenuItem>
                            ))}
                        </DropdownMenuGroup>

                        <DropdownMenuSeparator />

                        {/* Add Tenant Button */}
                        <DropdownMenuItem 
                            className="gap-2 p-2"
                            onClick={() => setShowCreateDialog(true)}
                        >
                            <div className="flex size-6 items-center justify-center rounded-md border bg-transparent">
                                <Plus className="size-4" />
                            </div>
                            <div className="font-medium text-muted-foreground">Add tenant</div>
                        </DropdownMenuItem>

                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
        </SidebarMenu>

        <CreateTenantDialog 
            open={showCreateDialog} 
            onOpenChange={setShowCreateDialog} 
        />
    </>
  )
}
