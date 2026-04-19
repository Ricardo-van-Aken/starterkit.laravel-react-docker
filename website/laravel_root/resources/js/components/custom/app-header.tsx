import { Breadcrumbs } from '@/components/starterkit/breadcrumbs';
import { UserMenuDropdown } from '@/components/custom/user-menu-dropdown';
import { ThemeSelector } from '@/components/custom/theme-selector';
import { LanguageSelector } from '@/components/custom/language-selector';
import { Separator } from '@/components/ui/separator';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { type BreadcrumbItem, type User } from '@/types';

interface AppHeaderProps {
    breadcrumbs: BreadcrumbItem[];
    user: User;
}

export function AppHeader({ breadcrumbs, user }: AppHeaderProps) {
    return (
        <header className="flex h-16 shrink-0 items-center gap-2 justify-between transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 border-b">
            <div className="flex items-center gap-2 px-4">
                <SidebarTrigger className="-ml-1" />

                <Separator orientation="vertical" />

                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>

            <div className="flex items-center gap-2 px-4">
                <LanguageSelector />
                <ThemeSelector />
                <div className="ml-2">
                    <UserMenuDropdown user={user} />
                </div>
            </div>
        </header>
    );
}
