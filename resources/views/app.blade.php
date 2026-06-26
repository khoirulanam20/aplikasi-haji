<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php($shared = app(\App\Support\Inertia\SharedDataProvider::class))
        @php($theme = $shared->themeColors())
        @php($favicon = $shared->webFaviconUrl())

        <title inertia>{{ $shared->appDisplayName() }}</title>

        @if ($favicon)
            <link rel="icon" href="{{ $favicon }}">
        @endif

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/iconoir-icons/iconoir@main/css/iconoir.css" />

        <style>
            :root {
                --color-primary: {{ $theme['primary'] ?? '#FF5B37' }};
                --color-primary-hover: {{ $theme['primary_hover'] ?? '#E55231' }};
                --color-secondary: {{ $theme['secondary'] ?? '#4B5694' }};
                --color-secondary-hover: {{ $theme['secondary_hover'] ?? '#3D4678' }};
            }
        </style>

        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased text-text-primary bg-background">
        @inertia
    </body>
</html>
