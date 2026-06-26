<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <?php ($shared = app(\App\Support\Inertia\SharedDataProvider::class)); ?>
        <?php ($theme = $shared->themeColors()); ?>
        <?php ($favicon = $shared->webFaviconUrl()); ?>

        <title inertia><?php echo e($shared->appDisplayName()); ?></title>

        <?php if($favicon): ?>
            <link rel="icon" href="<?php echo e($favicon); ?>">
        <?php endif; ?>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/iconoir-icons/iconoir@main/css/iconoir.css" />

        <style>
            :root {
                --color-primary: <?php echo e($theme['primary'] ?? '#FF5B37'); ?>;
                --color-primary-hover: <?php echo e($theme['primary_hover'] ?? '#E55231'); ?>;
                --color-secondary: <?php echo e($theme['secondary'] ?? '#4B5694'); ?>;
                --color-secondary-hover: <?php echo e($theme['secondary_hover'] ?? '#3D4678'); ?>;
            }
        </style>

        <?php echo app('Tighten\Ziggy\BladeRouteGenerator')->generate(); ?>
        <?php echo app('Illuminate\Foundation\Vite')->reactRefresh(); ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.tsx']); ?>
        <?php if (!isset($__inertiaSsrDispatched)) { $__inertiaSsrDispatched = true; $__inertiaSsrResponse = app(\Inertia\Ssr\Gateway::class)->dispatch($page); }  if ($__inertiaSsrResponse) { echo $__inertiaSsrResponse->head; } ?>
    </head>
    <body class="font-sans antialiased text-text-primary bg-background">
        <?php if (!isset($__inertiaSsrDispatched)) { $__inertiaSsrDispatched = true; $__inertiaSsrResponse = app(\Inertia\Ssr\Gateway::class)->dispatch($page); }  if ($__inertiaSsrResponse) { echo $__inertiaSsrResponse->body; } elseif (config('inertia.use_script_element_for_initial_page')) { ?><script data-page="app" type="application/json"><?php echo json_encode($page); ?></script><div id="app"></div><?php } else { ?><div id="app" data-page="<?php echo e(json_encode($page)); ?>"></div><?php } ?>
    </body>
</html>
<?php /**PATH /Users/khoirulanam/Work/Personal/Project/aplikasi-haji/resources/views/app.blade.php ENDPATH**/ ?>