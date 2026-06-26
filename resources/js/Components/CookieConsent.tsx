import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';

const STORAGE_KEY = 'cookie_consent_accepted';

export default function CookieConsent() {
    const [visible, setVisible] = useState(
        () => typeof window !== 'undefined' && localStorage.getItem(STORAGE_KEY) !== 'true',
    );

    if (!visible) {
        return null;
    }

    return (
        <div className="fixed bottom-4 left-4 right-4 z-50 mx-auto max-w-3xl rounded-lg border border-border bg-white p-4 shadow-lg sm:left-auto sm:right-6">
            <p className="text-sm text-text-secondary">
                Situs ini menggunakan cookie sesi untuk autentikasi. Dengan melanjutkan, Anda menyetujui{' '}
                <Link href={route('privacy')} className="font-medium text-primary hover:underline">
                    Kebijakan Privasi
                </Link>
                .
            </p>
            <div className="mt-3 flex justify-end gap-2">
                <Button
                    type="button"
                    variant="secondary"
                    size="sm"
                    onClick={() => {
                        localStorage.setItem(STORAGE_KEY, 'true');
                        setVisible(false);
                    }}
                >
                    Mengerti
                </Button>
            </div>
        </div>
    );
}
