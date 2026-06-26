import { Link } from '@inertiajs/react';
import IconoirIcon from '@/Components/IconoirIcon';

export default function ImpersonationBanner() {
    return (
        <div className="flex items-center justify-center gap-2 border-b border-warning/20 bg-yellow-50 px-6 py-3 text-sm text-warning">
            <IconoirIcon name="user" className="text-lg" />
            <span className="font-medium">Impersonation Mode Active.</span>
            <Link
                href={route('impersonate.stop')}
                method="post"
                as="button"
                className="font-semibold underline hover:text-yellow-700"
            >
                Exit Impersonation
            </Link>
        </div>
    );
}
