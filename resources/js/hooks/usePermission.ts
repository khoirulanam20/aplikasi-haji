import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

export function usePermission(permission: string): boolean {
    const { auth } = usePage<PageProps>().props;

    return auth.permissions.includes(permission);
}

export function useAnyPermission(permissions: string[]): boolean {
    const { auth } = usePage<PageProps>().props;

    return permissions.some((permission) => auth.permissions.includes(permission));
}

export function useAllPermissions(permissions: string[]): boolean {
    const { auth } = usePage<PageProps>().props;

    return permissions.every((permission) => auth.permissions.includes(permission));
}
