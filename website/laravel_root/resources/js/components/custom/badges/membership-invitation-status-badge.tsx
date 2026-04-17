import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import { CircleDashed, CheckCircle2, Clock, Ban, XCircle } from 'lucide-react';
import React from 'react';

export const INVITATION_STATUSES = {
    pending: { 
        label: 'Pending', 
        icon: CircleDashed, 
        colorClass: 'text-amber-600 border-amber-200 bg-amber-50',
        iconColor: 'text-amber-500'
    },
    accepted: { 
        label: 'Accepted', 
        icon: CheckCircle2, 
        colorClass: 'text-emerald-600 border-emerald-200 bg-emerald-50',
        iconColor: 'text-emerald-600'
    },
    declined: { 
        label: 'Declined', 
        icon: XCircle, 
        colorClass: 'text-rose-600 border-rose-200 bg-rose-50',
        iconColor: 'text-rose-600'
    },
    expired: { 
        label: 'Expired', 
        icon: Clock, 
        colorClass: 'text-muted-foreground border-border bg-muted/50',
        iconColor: 'text-muted-foreground'
    },
    revoked: { 
        label: 'Revoked', 
        icon: Ban, 
        colorClass: 'text-slate-600 border-slate-200 bg-slate-50',
        iconColor: 'text-slate-600'
    },
} as const;

export type MembershipInvitationStatus = keyof typeof INVITATION_STATUSES;

interface MembershipInvitationStatusBadgeProps {
    status: MembershipInvitationStatus;
    className?: string;
}

export function MembershipInvitationStatusBadge({ status, className }: MembershipInvitationStatusBadgeProps) {
    const metadata = INVITATION_STATUSES[status];

    if (!metadata) {
        return null; // Strict typing means this should theoretically be unreachable
    }

    const Icon = metadata.icon;

    return (
        <Badge 
            variant="outline" 
            className={cn(
                'gap-1.5 px-2 py-0.5 font-medium capitalize',
                metadata.colorClass,
                className
            )}
        >
            <Icon className={cn('h-3.5 w-3.5', metadata.iconColor)} />
            {metadata.label}
        </Badge>
    );
}
