"use client"

import * as React from "react"
import { Building2, ChevronsUpDown, Plus, Check, Settings } from "lucide-react"

import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
  DropdownMenuSub,
  DropdownMenuSubTrigger,
  DropdownMenuSubContent,
} from "@/components/ui/dropdown-menu"
import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  useSidebar,
} from "@/components/ui/sidebar"
import { usePage, router, Link } from '@inertiajs/react';
import { type SharedData } from '@/types';
import TenantController from '@/actions/App/Http/Controllers/TenantController';
import { CreateTenantDialog } from './dialogs/create-tenant-dialog';
import { TenantInfo } from '@/components/custom/tenant-info';

export function TenantMenuDropdown() {
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

                    {/* Tenant Menu Trigger */}
                    <DropdownMenuTrigger
                        render={
                            <SidebarMenuButton
                                size="lg"
                                className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                            />
                        }
                    >
                        <TenantInfo tenant={activeTenant} roles={auth.roles} />
                        <ChevronsUpDown className="ml-auto" />
                    </DropdownMenuTrigger>

                    <DropdownMenuContent
                        className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        align="start"
                        side={isMobile ? "bottom" : "right"}
                        sideOffset={4}
                    >
                        {/* Settings Button */}
                        <DropdownMenuGroup>
                            <DropdownMenuItem
                                render={
                                    <Link
                                        href={TenantController.edit.url()}
                                        className="flex items-center gap-2"
                                    />
                                }
                                nativeButton={false}
                            >
                                <Settings className="size-4" />
                                <span>Settings</span>
                            </DropdownMenuItem>
                        </DropdownMenuGroup>

                        <DropdownMenuSeparator />

                        {/* Switch Tenant Menu */}
                        <DropdownMenuGroup>
                            <DropdownMenuSub>
                                <DropdownMenuSubTrigger className="gap-2">
                                    <Building2 className="size-4" />
                                    <span>Switch team</span>
                                </DropdownMenuSubTrigger>
                                <DropdownMenuSubContent className="min-w-48">
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
                                            <TenantInfo tenant={tenant} roles={auth.roles} />
                                            {activeTenant.uuid === tenant.uuid && (
                                                <Check className="ml-auto size-4 text-primary" />
                                            )}
                                        </DropdownMenuItem>
                                    ))}

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
                                </DropdownMenuSubContent>
                            </DropdownMenuSub>
                        </DropdownMenuGroup>

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
