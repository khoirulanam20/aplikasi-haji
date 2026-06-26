import { Skeleton } from '@/Components/ui/skeleton';

export default function DashboardPageSkeleton() {
    return (
        <div className="space-y-8">
            <Skeleton className="h-28 w-full rounded-lg" />

            <section className="space-y-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <Skeleton className="h-7 w-32" />
                    <div className="flex gap-2">
                        <Skeleton className="h-9 w-44" />
                        <Skeleton className="h-9 w-24" />
                    </div>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {Array.from({ length: 4 }).map((_, index) => (
                        <div key={index} className="card flex items-start gap-4">
                            <Skeleton className="h-14 w-14 shrink-0 rounded-full" />
                            <div className="flex-1 space-y-2">
                                <Skeleton className="h-4 w-24" />
                                <Skeleton className="h-8 w-16" />
                                <Skeleton className="h-3 w-28" />
                            </div>
                        </div>
                    ))}
                </div>

                <div className="grid gap-6 lg:grid-cols-5">
                    <div className="card space-y-4 lg:col-span-3">
                        <div className="flex items-start justify-between gap-3">
                            <div className="space-y-2">
                                <Skeleton className="h-4 w-36" />
                                <Skeleton className="h-3 w-48" />
                            </div>
                            <Skeleton className="h-8 w-28" />
                        </div>
                        <Skeleton className="h-[280px] w-full" />
                    </div>
                    <div className="card space-y-4 lg:col-span-2">
                        <div className="flex items-start justify-between gap-3">
                            <div className="space-y-2">
                                <Skeleton className="h-4 w-36" />
                                <Skeleton className="h-3 w-40" />
                            </div>
                            <Skeleton className="h-8 w-28" />
                        </div>
                        <Skeleton className="h-[280px] w-full" />
                    </div>
                </div>
            </section>

            <section className="space-y-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <Skeleton className="h-7 w-40" />
                    <div className="flex gap-2">
                        <Skeleton className="h-8 w-24" />
                        <Skeleton className="h-9 w-28" />
                        <Skeleton className="h-9 w-24" />
                    </div>
                </div>

                <div className="card space-y-4 p-4">
                    <div className="flex gap-4 border-b border-border pb-3">
                        <Skeleton className="h-8 w-16" />
                        <Skeleton className="h-8 w-16" />
                        <Skeleton className="h-8 w-20" />
                    </div>
                    <Skeleton className="h-10 w-full" />
                    {Array.from({ length: 6 }).map((_, index) => (
                        <Skeleton key={index} className="h-12 w-full" />
                    ))}
                    <div className="flex justify-end gap-2 border-t border-border pt-3">
                        <Skeleton className="h-8 w-24" />
                        <Skeleton className="h-8 w-8" />
                        <Skeleton className="h-8 w-8" />
                        <Skeleton className="h-8 w-24" />
                    </div>
                </div>
            </section>
        </div>
    );
}
