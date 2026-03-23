"use client"

import * as React from "react"
import { Avatar as AvatarPrimitive } from "@base-ui/react/avatar"
import { 
    Building2, Briefcase, Factory, Store, School, Hospital, Hotel, Plane, Ship, Car, Truck, Zap, Leaf, Heart,
    Shield, Target, Rocket, Globe, MapPin, Users, Network, Box, Package, ShoppingBag, Coffee, Utensils, Wrench,
    Hammer, Palette, Music, Film, Gamepad2, BookOpen, GraduationCap, Stethoscope, Scale, Gavel, TrendingUp,
    DollarSign, type LucideIcon 
} from "lucide-react"

import { cn } from "@/lib/utils"
import { 
    Avatar as BaseAvatar, 
    AvatarImage as BaseAvatarImage,
    AvatarFallback as BaseAvatarFallback,
    AvatarBadge,
    AvatarGroup,
    AvatarGroupCount
} from "@/components/ui/avatar"

// Icon list for icon-based fallbacks
const AVATAR_ICONS: LucideIcon[] = [
    Building2, Briefcase, Factory, Store, School, Hospital, Hotel, Plane, Ship, Car, Truck, Zap, Leaf, Heart,
    Shield, Target, Rocket, Globe, MapPin, Users, Network, Box, Package, ShoppingBag, Coffee, Wrench,
    Hammer, Palette, Music, Film, Gamepad2, BookOpen, GraduationCap, Stethoscope, Scale, Gavel, TrendingUp,
    DollarSign,
]

function getIconForString(seed: string): LucideIcon {
    let hash = 0
    for (let i = 0; i < seed.length; i++) {
        hash = seed.charCodeAt(i) + ((hash << 5) - hash)
    }
    const index = Math.abs(hash) % AVATAR_ICONS.length
    return AVATAR_ICONS[index]
}

function AvatarIconFallback({
  seed,
  className,
  variant = "circle",
  iconSize = "size-5",
  ...props
}: Omit<AvatarPrimitive.Fallback.Props, "children"> & {
  seed: string
  variant?: "circle" | "square"
  iconSize?: string
}) {
  const Icon = React.useMemo(() => getIconForString(seed), [seed])
  
  return (
    <AvatarPrimitive.Fallback
      data-slot="avatar-icon-fallback"
      className={cn(
        "bg-muted flex size-full items-center justify-center text-muted-foreground",
        variant === "circle" ? "rounded-full" : "rounded-md",
        className
      )}
      {...props}
    >
      <Icon className={iconSize} />
    </AvatarPrimitive.Fallback>
  )
}

export {
  BaseAvatar as Avatar,
  BaseAvatarImage as AvatarImage,
  BaseAvatarFallback as AvatarFallback,
  AvatarIconFallback,
  AvatarBadge,
  AvatarGroup,
  AvatarGroupCount,
}
