import { Skeleton } from '@/Components/ui/skeleton';

export default function ModulesPageSkeleton() {
    return (
        <div className="space-y-6">
            <div className="flex justify-end gap-2">
                <Skeleton className="h-10 w-32" />
                <Skeleton className="h-10 w-28" />
            </div>
            {Array.from({ length: 2 }).map((_, group) => (
                <div key={group} className="card space-y-4">
                    <Skeleton className="h-6 w-48" />
                    <div className="space-y-3">
                        {Array.from({ length: 3 }).map((__, module) => (
                            <div key={module} className="flex items-center gap-3 rounded-md border border-border p-4">
                                <Skeleton className="h-10 w-10 shrink-0" />
                                <div className="flex-1 space-y-2">
                                    <Skeleton className="h-4 w-40" />
                                    <Skeleton className="h-3 w-24" />
                                </div>
                                <Skeleton className="h-8 w-20" />
                            </div>
                        ))}
                    </div>
                </div>
            ))}
        </div>
    );
}
