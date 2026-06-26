import { useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

export default function ThemeSync() {
    const { app } = usePage<PageProps>().props;

    useEffect(() => {
        const theme = app.theme;
        if (!theme) {
            return;
        }

        const root = document.documentElement;
        root.style.setProperty('--color-primary', theme.primary);
        root.style.setProperty('--color-primary-hover', theme.primary_hover);
        root.style.setProperty('--color-secondary', theme.secondary);
        root.style.setProperty('--color-secondary-hover', theme.secondary_hover);
    }, [app.theme]);

    useEffect(() => {
        if (!app.faviconUrl) {
            return;
        }

        let link = document.querySelector<HTMLLinkElement>("link[rel='icon']");

        if (!link) {
            link = document.createElement('link');
            link.rel = 'icon';
            document.head.appendChild(link);
        }

        link.href = app.faviconUrl;
    }, [app.faviconUrl]);

    return null;
}
