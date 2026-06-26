import { Skeleton } from '@/Components/ui/skeleton';

export default function FormPageSkeleton() {
    return (
        <div className="space-y-6">
            {Array.from({ length: 2 }).map((_, section) => (
                <div key={section} className="card space-y-4">
                    <Skeleton className="h-5 w-40" />
                    {Array.from({ length: 3 }).map((__, field) => (
                        <div key={field} className="space-y-2">
                            <Skeleton className="h-4 w-28" />
                            <Skeleton className="h-10 w-full" />
                        </div>
                    ))}
                </div>
            ))}
            <Skeleton className="h-10 w-36" />
        </div>
    );
}
