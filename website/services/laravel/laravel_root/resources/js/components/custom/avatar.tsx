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

// Define shape type for consistency
export type AvatarShape = "circle" | "square"

/**
 * SquareAvatar component
 * A square alternative to the standard circular avatar.
 */
function SquareAvatar({
  className,
  size = "default",
  ...props
}: AvatarPrimitive.Root.Props & {
  size?: "default" | "sm" | "lg"
}) {
  return (
    <AvatarPrimitive.Root
      data-slot="square-avatar"
      data-size={size}
      className={cn(
        "group/avatar relative flex size-8 shrink-0 select-none after:absolute after:inset-0 after:border after:border-border after:mix-blend-darken data-[size=lg]:size-10 data-[size=sm]:size-6 dark:after:mix-blend-lighten",
        "data-[size=sm]:rounded-md data-[size=sm]:after:rounded-md",
        "data-[size=default]:rounded-lg data-[size=default]:after:rounded-lg",
        "data-[size=lg]:rounded-xl data-[size=lg]:after:rounded-xl",
        className
      )}
      {...props}
    />
  )
}

function SquareAvatarImage({ className, ...props }: AvatarPrimitive.Image.Props) {
  return (
    <AvatarPrimitive.Image
      data-slot="square-avatar-image"
      className={cn(
        "aspect-square size-full object-cover rounded-[inherit]",
        className
      )}
      {...props}
    />
  )
}

function SquareAvatarFallback({
  className,
  ...props
}: AvatarPrimitive.Fallback.Props) {
  return (
    <AvatarPrimitive.Fallback
      data-slot="square-avatar-fallback"
      className={cn(
        "flex size-full items-center justify-center bg-muted text-sm text-muted-foreground group-data-[size=sm]/avatar:text-xs rounded-[inherit]",
        className
      )}
      {...props}
    />
  )
}

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
  iconSize = "size-5",
  ...props
}: Omit<AvatarPrimitive.Fallback.Props, "children"> & {
  seed: string
  iconSize?: string
}) {
  const Icon = React.useMemo(() => getIconForString(seed), [seed])
  
  return (
    <AvatarPrimitive.Fallback
      data-slot="avatar-icon-fallback"
      className={cn(
        "bg-muted flex size-full items-center justify-center text-muted-foreground rounded-[inherit]",
        className
      )}
      {...props}
    >
      <Icon className={iconSize} />
    </AvatarPrimitive.Fallback>
  )
}

/**
 * SquareAvatarIconFallback
 * An alias for AvatarIconFallback for semantic consistency in square contexts.
 */
const SquareAvatarIconFallback = AvatarIconFallback;

export {
  BaseAvatar as Avatar,
  BaseAvatarImage as AvatarImage,
  BaseAvatarFallback as AvatarFallback,
  SquareAvatar,
  SquareAvatarImage,
  SquareAvatarFallback,
  AvatarIconFallback,
  SquareAvatarIconFallback,
  AvatarBadge,
  AvatarGroup,
  AvatarGroupCount,
}
