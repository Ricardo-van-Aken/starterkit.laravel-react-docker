"use client"

import * as React from "react"
import { Building2, ChevronsUpDown, Plus, Check, Settings, LayoutGrid } from "lucide-react"

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
import { cn } from "@/lib/utils";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { type SharedData } from '@/types';
import { TenantPermissionName } from '@/types/enums';
import TenantController from '@/actions/App/Http/Controllers/TenantController';
import { CreateTenantDialog } from './dialogs/create-tenant-dialog';
import { TenantInfo } from '@/components/custom/tenant-info';

import { TenantMenuList } from '@/components/custom/tenant-menu-list';
import { dashboard } from '@/routes/tenant';

export function TenantMenuDropdown() {
  const { auth } = usePage<SharedData>().props
  const { isMobile } = useSidebar()
  const [showCreateDialog, setShowCreateDialog] = React.useState(false)

  const activeTenant = auth.active_tenant
  const tenants = auth.tenants || []
  const permissions = activeTenant?.permissions ?? []

  const canViewSettings = [
    TenantPermissionName.UpdateTenantDetails,
    TenantPermissionName.ViewTenantMembers,
    TenantPermissionName.ViewTenantRoles,
    TenantPermissionName.ViewBillingInformation,
  ].some(permission => permissions.includes(permission))

  const handleSwitch = (uuid: string) => {
    router.post(TenantController.switch({ tenant: uuid }).url)
  }

  // Case: No tenants at all
  if (tenants.length === 0) {
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

  // Case: Tenants exist, but none is active
  if (!activeTenant) {
      return (
        <>
            <SidebarMenu>
              <SidebarMenuItem>
                <DropdownMenu>
                    <DropdownMenuTrigger
                        render={
                            <SidebarMenuButton
                                size="lg"
                                className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                            />
                        }
                    >
                        <div className="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
                            <Building2 className="size-4" />
                        </div>
                        <div className="grid flex-1 text-left text-sm leading-tight">
                            <span className="truncate font-semibold">Select Tenant</span>
                            <span className="truncate text-xs">{tenants.length} tenants available</span>
                        </div>
                        <ChevronsUpDown className="ml-auto" />
                    </DropdownMenuTrigger>

                    <DropdownMenuContent
                        className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        align="start"
                        side={isMobile ? "bottom" : "right"}
                        sideOffset={4}
                    >
                        <TenantMenuList 
                            tenants={tenants}
                            onSwitch={handleSwitch}
                            onCreate={() => setShowCreateDialog(true)}
                        />
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
                        <TenantInfo tenant={activeTenant} roles={activeTenant?.roles} />
                        <ChevronsUpDown className="ml-auto" />
                    </DropdownMenuTrigger>

                    <DropdownMenuContent
                        className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        align="start"
                        side={isMobile ? "bottom" : "right"}
                        sideOffset={4}
                    >
                        {/* Navigation Links */}
                        <DropdownMenuGroup>
                            <DropdownMenuItem
                                render={
                                    <Link
                                        href={dashboard().url}
                                        className="flex items-center gap-2"
                                    />
                                }
                                nativeButton={false}
                            >
                                <LayoutGrid className="size-4" />
                                <span>Dashboard</span>
                            </DropdownMenuItem>
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger>
                                        <div className={cn("w-full", !canViewSettings && "cursor-not-allowed")}>
                                            <DropdownMenuItem
                                                render={
                                                    canViewSettings ? (
                                                        <Link
                                                            href={TenantController.edit.url()}
                                                            className="flex items-center gap-2"
                                                        />
                                                    ) : undefined
                                                }
                                                nativeButton={!canViewSettings}
                                                disabled={!canViewSettings}
                                                className={cn("flex items-center gap-2 w-full", !canViewSettings && "opacity-50 pointer-events-none")}
                                            >
                                                <Settings className="size-4" />
                                                <span>Settings</span>
                                            </DropdownMenuItem>
                                        </div>
                                    </TooltipTrigger>
                                    {!canViewSettings && (
                                        <TooltipContent side="right">
                                            You don't have permission to access settings
                                        </TooltipContent>
                                    )}
                                </Tooltip>
                            </TooltipProvider>
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
                                    <TenantMenuList 
                                        tenants={tenants}
                                        activeTenant={activeTenant}
                                        onSwitch={handleSwitch}
                                        onCreate={() => setShowCreateDialog(true)}
                                    />
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
