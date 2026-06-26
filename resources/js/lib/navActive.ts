import { MenuItem } from '@/types';

function pathOnly(href: string): string {
    if (href.startsWith('/')) {
        return href.split('?')[0]?.split('#')[0] ?? href;
    }

    try {
        return new URL(href, window.location.origin).pathname;
    } catch {
        return href;
    }
}

function routeNameMatches(menu: MenuItem): boolean {
    const name = menu.route_name;

    if (!name || typeof route !== 'function') {
        return false;
    }

    try {
        if (route().current(name)) {
            return true;
        }

        const wildcard = name.replace(/\.index$/, '.*');

        return wildcard !== name && Boolean(route().current(wildcard));
    } catch {
        return false;
    }
}

export function isSidebarMenuActive(menu: MenuItem, currentUrl: string): boolean {
    if (!menu.url || menu.url === '#') {
        return false;
    }

    if (routeNameMatches(menu)) {
        return true;
    }

    const currentPath = pathOnly(currentUrl);
    const menuPath = pathOnly(menu.url);

    if (currentPath === menuPath) {
        return true;
    }

    return menuPath.length > 1 && currentPath.startsWith(`${menuPath}/`);
}
