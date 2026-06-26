import { Skeleton } from '@/Components/ui/skeleton';

export default function DefaultPageSkeleton() {
    return (
        <div className="space-y-6">
            <Skeleton className="h-8 w-56" />
            <div className="card space-y-4">
                <Skeleton className="h-4 w-full" />
                <Skeleton className="h-4 w-5/6" />
                <Skeleton className="h-4 w-4/6" />
                <Skeleton className="h-32 w-full" />
            </div>
        </div>
    );
}
