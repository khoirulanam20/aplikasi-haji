import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{js,ts,jsx,tsx}',
    ],
    theme: {
        fontFamily: {
            sans: ['Inter', ...defaultTheme.fontFamily.sans],
        },
        extend: {
            colors: {
                primary: {
                    DEFAULT: 'var(--color-primary)',
                    hover: 'var(--color-primary-hover)',
                },
                accent: {
                    DEFAULT: 'var(--color-secondary)',
                    hover: 'var(--color-secondary-hover)',
                },
                secondary: {
                    DEFAULT: 'var(--color-secondary)',
                    hover: 'var(--color-secondary-hover)',
                },
                background: '#F7F8FA',
                surface: {
                    DEFAULT: '#FFFFFF',
                    dark: '#1A1D1F',
                },
                text: {
                    primary: '#1A1D1F',
                    secondary: '#6F767E',
                },
                border: '#EFEFEF',
                success: '#10B981',
                danger: '#EF4444',
                warning: '#F59E0B',
            },
            borderRadius: {
                sm: '8px',
                md: '12px',
                lg: '24px',
                xl: '32px',
            },
            boxShadow: {
                card: '0 4px 20px rgba(0,0,0, 0.03)',
            },
        },
    },
    plugins: [forms],
};
