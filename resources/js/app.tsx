import '../css/app.css';
import 'driver.js/dist/driver.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { AlertProvider } from '@/Components/Alert/AlertContext';
import ErrorBoundary from '@/Components/ErrorBoundary';
import { Toaster } from 'sonner';

const appName = import.meta.env.VITE_APP_NAME || 'Starterkit';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.tsx`,
            import.meta.glob('./Pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(
            <ErrorBoundary>
                <AlertProvider>
                    <App {...props} />
                    <Toaster position="top-right" richColors closeButton />
                </AlertProvider>
            </ErrorBoundary>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});
