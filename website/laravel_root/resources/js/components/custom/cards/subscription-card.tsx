"use client"

import * as React from "react"
import { usePage } from "@inertiajs/react"
import { 
    Card, 
    CardContent, 
    CardDescription, 
    CardHeader, 
    CardTitle 
} from "@/components/ui/card"
import { 
    Progress, 
    ProgressLabel, 
    ProgressValue 
} from "@/components/ui/progress"
import { type SharedData } from "@/types"
import { Users2, Network, Cpu } from "lucide-react"

export function SubscriptionCard({ className }: { className?: string }) {
    const { auth } = usePage<SharedData>().props
    const tenant = auth.active_tenant

    // Placeholder limits (for demo purposes)
    const limits = {
        users: 5,
        orgUnits: 3,
        devices: 10
    }

    const stats = [
        {
            label: "Users",
            icon: Users2,
            value: tenant?.users_count ?? 0,
            max: limits.users,
            color: "bg-blue-500"
        },
        {
            label: "Org Units",
            icon: Network,
            value: tenant?.organization_units_count ?? 0,
            max: limits.orgUnits,
            color: "bg-emerald-500"
        },
        {
            label: "IoT Devices",
            icon: Cpu,
            value: 0, // Placeholder
            max: limits.devices,
            color: "bg-amber-500"
        }
    ]

    return (
        <Card className={className}>
            <CardHeader className="pb-4">
                <CardTitle className="text-lg">Subscription</CardTitle>
                <CardDescription>Free Plan (Placeholder)</CardDescription>
            </CardHeader>
            <CardContent className="grid gap-6">
                {stats.map((stat) => {
                    const percentage = Math.min((stat.value / stat.max) * 100, 100)
                    return (
                        <div key={stat.label} className="grid gap-2">
                            <Progress value={percentage} className="flex-col items-start gap-2">
                                <div className="flex w-full items-center gap-2">
                                    <stat.icon className="size-4 text-muted-foreground" />
                                    <ProgressLabel className="text-xs font-normal text-muted-foreground uppercase tracking-wider leading-none">
                                        {stat.label}
                                    </ProgressLabel>
                                    <span className="text-xs font-medium tabular-nums ml-auto leading-none text-muted-foreground">
                                        {stat.value} / {stat.max}
                                    </span>
                                </div>
                            </Progress>
                        </div>
                    )
                })}
            </CardContent>
        </Card>
    )
}
