import { Skeleton } from '@/Components/ui/skeleton';

export default function TablePageSkeleton() {
    return (
        <div className="space-y-6">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <Skeleton className="h-10 w-64" />
                <Skeleton className="h-10 w-32" />
            </div>
            <div className="card overflow-hidden p-0">
                <div className="space-y-3 p-4">
                    <Skeleton className="h-10 w-full" />
                    {Array.from({ length: 6 }).map((_, index) => (
                        <Skeleton key={index} className="h-12 w-full" />
                    ))}
                </div>
                <div className="flex flex-col gap-3 border-t border-border bg-gray-50/80 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <Skeleton className="h-5 w-16" />
                    <div className="flex flex-wrap items-center gap-4">
                        <Skeleton className="h-9 w-40" />
                        <Skeleton className="h-5 w-28" />
                        <div className="flex gap-1">
                            <Skeleton className="h-9 w-9" />
                            <Skeleton className="h-9 w-9" />
                            <Skeleton className="h-9 w-9" />
                            <Skeleton className="h-9 w-9" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
