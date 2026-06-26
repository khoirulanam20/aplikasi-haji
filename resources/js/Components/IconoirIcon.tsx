import { cn } from '@/lib/utils';
import { iconoirClass } from '@/lib/iconoir';

interface IconoirIconProps {
    name: string;
    className?: string;
}

export default function IconoirIcon({ name, className }: IconoirIconProps) {
    return <i className={cn(iconoirClass(name), className)} aria-hidden />;
}
