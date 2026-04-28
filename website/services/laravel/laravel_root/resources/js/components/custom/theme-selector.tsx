import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';
import { Monitor, Moon, Sun } from 'lucide-react';

export function ThemeSelector() {
    const { appearance, updateAppearance } = useAppearance();

    const getCurrentIcon = () => {
        const iconClasses = 'h-5 w-5';
        switch (appearance) {
            case 'dark':
                return <Moon className={iconClasses} />;
            case 'light':
                return <Sun className={iconClasses} />;
            default:
                return <Monitor className={iconClasses} />;
        }
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger
                render={
                    <Button
                        variant="ghost"
                        size="icon"
                        className="h-8 w-8 rounded-md focus-visible:ring-0 focus-visible:ring-offset-0"
                    />
                }
            >
                {getCurrentIcon()}
                <span className="sr-only">Toggle theme</span>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuItem onClick={() => updateAppearance('light')}>
                    <Sun className="mr-2 h-4 w-4" />
                    <span>Light</span>
                </DropdownMenuItem>
                <DropdownMenuItem onClick={() => updateAppearance('dark')}>
                    <Moon className="mr-2 h-4 w-4" />
                    <span>Dark</span>
                </DropdownMenuItem>
                <DropdownMenuItem onClick={() => updateAppearance('system')}>
                    <Monitor className="mr-2 h-4 w-4" />
                    <span>System</span>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
