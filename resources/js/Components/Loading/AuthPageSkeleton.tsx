import { Skeleton } from '@/Components/ui/skeleton';

export default function AuthPageSkeleton() {
    return (
        <div className="space-y-5">
            <Skeleton className="mx-auto h-8 w-48" />
            <Skeleton className="mx-auto h-4 w-64" />
            {Array.from({ length: 2 }).map((_, index) => (
                <div key={index} className="space-y-2">
                    <Skeleton className="h-4 w-28" />
                    <Skeleton className="h-10 w-full" />
                </div>
            ))}
            <Skeleton className="h-10 w-full" />
        </div>
    );
}
