<?php
    $path = '/'.ltrim(request()->path(), '/');
    $isAuth = request()->is('login', 'register', 'forgot-password', 'reset-password*', 'verify-email', 'confirm-password');
    $isAdmin = request()->is('app', 'app/*', 'profile', 'profile/*', 'notifications', 'notifications/*');
    $isTable = (bool) preg_match('#^/app/(users|master-data|role-permission|notifications)(/|$)#', $path);
    $isDashboard = (bool) preg_match('#^/app/dashboard(/|$)#', $path);
    $isModules = (bool) preg_match('#^/app/modules(/|$)#', $path);
    $isForm = (bool) preg_match('#^/(app/(web-setting|config)|profile)(/|$)#', $path);
?>

<div id="initial-app-skeleton" class="fixed inset-0 z-[9999] bg-background">
    <?php if($isAuth): ?>
        <div class="flex min-h-screen flex-col items-center justify-center p-4">
            <div class="mb-8 flex items-center gap-3">
                <div class="h-10 w-10 animate-pulse rounded-md bg-gray-200"></div>
                <div class="h-8 w-40 animate-pulse rounded-md bg-gray-200"></div>
            </div>
            <div class="card w-full max-w-md space-y-5">
                <div class="mx-auto h-8 w-48 animate-pulse rounded-md bg-gray-200"></div>
                <div class="mx-auto h-4 w-64 animate-pulse rounded-md bg-gray-200"></div>
                <?php for($i = 0; $i < 2; $i++): ?>
                    <div class="space-y-2">
                        <div class="h-4 w-28 animate-pulse rounded-md bg-gray-200"></div>
                        <div class="h-10 w-full animate-pulse rounded-md bg-gray-200"></div>
                    </div>
                <?php endfor; ?>
                <div class="h-10 w-full animate-pulse rounded-md bg-gray-200"></div>
            </div>
        </div>
    <?php elseif($isAdmin): ?>
        <div class="flex h-screen overflow-hidden bg-background">
            <aside class="hidden w-64 shrink-0 flex-col border-r border-border bg-surface md:flex">
                <div class="flex h-20 items-center justify-between border-b border-border px-4">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 animate-pulse rounded-md bg-gray-200"></div>
                        <div class="h-5 w-32 animate-pulse rounded-md bg-gray-200"></div>
                    </div>
                    <div class="h-8 w-8 animate-pulse rounded-md bg-gray-200"></div>
                </div>
                <nav class="flex-1 space-y-2 px-4 py-6">
                    <?php for($i = 0; $i < 6; $i++): ?>
                        <div class="h-10 w-full animate-pulse rounded-md bg-gray-200"></div>
                    <?php endfor; ?>
                </nav>
                <div class="border-t border-border p-4">
                    <div class="h-10 w-full animate-pulse rounded-md bg-gray-200"></div>
                </div>
            </aside>
            <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
                <header class="flex h-20 shrink-0 items-center justify-between border-b border-border bg-surface px-4 sm:px-6 lg:px-8">
                    <div class="hidden h-7 w-48 animate-pulse rounded-md bg-gray-200 sm:block"></div>
                    <div class="flex items-center gap-4">
                        <div class="hidden h-10 w-64 animate-pulse rounded-md bg-gray-200 md:block"></div>
                        <div class="h-10 w-10 animate-pulse rounded-full bg-gray-200"></div>
                        <div class="h-10 w-36 animate-pulse rounded-full bg-gray-200"></div>
                    </div>
                </header>
                <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 lg:p-8">
                    <?php if($isDashboard): ?>
                        <div class="space-y-8">
                            <div class="card h-28 animate-pulse bg-gray-200"></div>
                            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                <?php for($i = 0; $i < 4; $i++): ?>
                                    <div class="card h-24 animate-pulse bg-gray-200"></div>
                                <?php endfor; ?>
                            </div>
                            <div class="grid gap-6 lg:grid-cols-5">
                                <div class="card h-[320px] animate-pulse bg-gray-200 lg:col-span-3"></div>
                                <div class="card h-[320px] animate-pulse bg-gray-200 lg:col-span-2"></div>
                            </div>
                        </div>
                    <?php elseif($isTable): ?>
                        <div class="space-y-6">
                            <div class="flex justify-between gap-3">
                                <div class="h-10 w-64 animate-pulse rounded-md bg-gray-200"></div>
                                <div class="h-10 w-32 animate-pulse rounded-md bg-gray-200"></div>
                            </div>
                            <div class="card overflow-hidden p-0">
                                <div class="space-y-3 p-4">
                                    <div class="h-10 w-full animate-pulse rounded-md bg-gray-200"></div>
                                    <?php for($i = 0; $i < 6; $i++): ?>
                                        <div class="h-12 w-full animate-pulse rounded-md bg-gray-200"></div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    <?php elseif($isModules): ?>
                        <div class="space-y-6">
                            <div class="flex justify-end gap-2">
                                <div class="h-10 w-32 animate-pulse rounded-md bg-gray-200"></div>
                                <div class="h-10 w-28 animate-pulse rounded-md bg-gray-200"></div>
                            </div>
                            <?php for($i = 0; $i < 2; $i++): ?>
                                <div class="card space-y-3">
                                    <div class="h-6 w-40 animate-pulse rounded-md bg-gray-200"></div>
                                    <?php for($j = 0; $j < 3; $j++): ?>
                                        <div class="h-12 w-full animate-pulse rounded-md bg-gray-200"></div>
                                    <?php endfor; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php elseif($isForm): ?>
                        <div class="space-y-6">
                            <div class="h-8 w-56 animate-pulse rounded-md bg-gray-200"></div>
                            <?php for($i = 0; $i < 2; $i++): ?>
                                <div class="card space-y-4">
                                    <div class="h-5 w-32 animate-pulse rounded-md bg-gray-200"></div>
                                    <div class="h-10 w-full animate-pulse rounded-md bg-gray-200"></div>
                                    <div class="h-10 w-full animate-pulse rounded-md bg-gray-200"></div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php else: ?>
                        <div class="space-y-6">
                            <div class="h-8 w-56 animate-pulse rounded-md bg-gray-200"></div>
                            <div class="card h-48 animate-pulse bg-gray-200"></div>
                        </div>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    <?php else: ?>
        <div class="flex min-h-screen items-center justify-center p-8">
            <div class="h-8 w-56 animate-pulse rounded-md bg-gray-200"></div>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH /Users/khoirulanam/Work/inertia_starterkit/resources/views/partials/initial-skeleton.blade.php ENDPATH**/ ?>