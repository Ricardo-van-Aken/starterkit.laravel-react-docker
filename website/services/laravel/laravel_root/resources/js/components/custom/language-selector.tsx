import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Languages } from 'lucide-react';
import { useState } from 'react';

const languages = [
    { code: 'en', name: 'English' },
    { code: 'nl', name: 'Nederlands' },
] as const;

export function LanguageSelector() {
    const [currentLang, setCurrentLang] = useState<typeof languages[number]>(languages[0]);

    return (
        <DropdownMenu>
            <DropdownMenuTrigger
                render={
                    <Button
                        variant="ghost"
                        size="sm"
                        className="h-8 gap-1.5 px-2 focus-visible:ring-0 focus-visible:ring-offset-0"
                    />
                }
            >
                <Languages className="h-4 w-4" />
                <span className="text-xs font-medium uppercase">
                    {currentLang.code}
                </span>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {languages.map((lang) => (
                    <DropdownMenuItem
                        key={lang.code}
                        onClick={() => setCurrentLang(lang)}
                        className="flex items-center gap-2"
                    >
                        <span className={lang.code === currentLang.code ? "font-bold" : ""}>
                            {lang.name}
                        </span>
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
