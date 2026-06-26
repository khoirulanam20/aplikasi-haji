import type { ReactNode } from 'react';
import { sanitizeIconoirHtml } from '@/lib/iconoir';
import { cn } from '@/lib/utils';

interface IconoirHtmlProps {
    html: string | null | undefined;
    className?: string;
    fallback?: ReactNode;
}

export default function IconoirHtml({ html, className, fallback = null }: IconoirHtmlProps) {
    const safe = sanitizeIconoirHtml(html);

    if (!safe) {
        return <>{fallback}</>;
    }

    return (
        <span
            className={cn('inline-flex items-center justify-center', className)}
            dangerouslySetInnerHTML={{ __html: safe }}
        />
    );
}
