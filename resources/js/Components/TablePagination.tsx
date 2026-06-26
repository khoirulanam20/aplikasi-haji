import { router } from '@inertiajs/react';
import IconoirIcon from '@/Components/IconoirIcon';
import { Button } from '@/Components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { Paginated } from '@/types';

const DEFAULT_PER_PAGE_OPTIONS = [10, 25, 50, 100];

interface TablePaginationProps<T> {
    paginator: Paginated<T>;
    routeName: string;
    query?: Record<string, string | number | undefined | null>;
    perPageOptions?: number[];
}

export default function TablePagination<T>({
    paginator,
    routeName,
    query = {},
    perPageOptions = DEFAULT_PER_PAGE_OPTIONS,
}: TablePaginationProps<T>) {
    const { current_page, last_page, per_page, total } = paginator;
    const canGoPrev = current_page > 1;
    const canGoNext = current_page < last_page;

    const navigate = (overrides: { page?: number; per_page?: number }) => {
        router.get(
            route(routeName),
            {
                ...query,
                page: overrides.page ?? current_page,
                per_page: overrides.per_page ?? per_page,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    return (
        <div className="flex flex-col gap-3 border-t border-border bg-gray-50/80 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <p className="text-sm text-text-secondary">
                <span className="font-semibold text-text-primary">{total}</span> baris
            </p>

            <div className="flex flex-wrap items-center gap-4">
                <label className="flex items-center gap-2 text-sm text-text-secondary">
                    Baris per halaman
                    <Select
                        value={String(per_page)}
                        onValueChange={(value) => navigate({ page: 1, per_page: Number(value) })}
                    >
                        <SelectTrigger className="h-9 w-[4.75rem] gap-1 px-3">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {perPageOptions.map((option) => (
                                <SelectItem key={option} value={String(option)}>
                                    {option}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </label>

                <span className="text-sm text-text-secondary">
                    Halaman {current_page} dari {Math.max(last_page, 1)}
                </span>

                <div className="flex items-center gap-1">
                    <Button
                        type="button"
                        variant="secondary"
                        size="icon"
                        className="h-9 w-9"
                        disabled={!canGoPrev}
                        onClick={() => navigate({ page: 1 })}
                        aria-label="Halaman pertama"
                    >
                        <span className="text-base leading-none">«</span>
                    </Button>
                    <Button
                        type="button"
                        variant="secondary"
                        size="icon"
                        className="h-9 w-9"
                        disabled={!canGoPrev}
                        onClick={() => navigate({ page: current_page - 1 })}
                        aria-label="Halaman sebelumnya"
                    >
                        <IconoirIcon name="nav-arrow-left" className="text-base" />
                    </Button>
                    <Button
                        type="button"
                        variant="secondary"
                        size="icon"
                        className="h-9 w-9"
                        disabled={!canGoNext}
                        onClick={() => navigate({ page: current_page + 1 })}
                        aria-label="Halaman berikutnya"
                    >
                        <IconoirIcon name="nav-arrow-right" className="text-base" />
                    </Button>
                    <Button
                        type="button"
                        variant="secondary"
                        size="icon"
                        className="h-9 w-9"
                        disabled={!canGoNext}
                        onClick={() => navigate({ page: last_page })}
                        aria-label="Halaman terakhir"
                    >
                        <span className="text-base leading-none">»</span>
                    </Button>
                </div>
            </div>
        </div>
    );
}
