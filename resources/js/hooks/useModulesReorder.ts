import { type RefObject, useEffect, useRef } from 'react';
import Sortable, { type SortableEvent } from 'sortablejs';
import { router } from '@inertiajs/react';
import { useAlert } from '@/Components/Alert/AlertContext';

interface ReorderUrls {
    groups?: string;
    modules?: string;
}

function csrfToken(): string {
    return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
}

async function putJson(url: string, body: Record<string, unknown>) {
    const response = await fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body),
    });

    if (!response.ok) {
        const payload = await response.json().catch(() => ({}));
        const message = (payload as { message?: string }).message || `Reorder gagal (${response.status})`;
        throw new Error(message);
    }

    return response.json() as Promise<{ message?: string }>;
}

function orderChanged(event: SortableEvent): boolean {
    return event.oldIndex !== event.newIndex;
}

function refreshSidebarNavigation() {
    router.reload({
        only: ['sidebarNavigation', 'dynamicMenus'],
    });
}

export function useModulesReorder(
    rootRef: RefObject<HTMLElement | null>,
    reorderUrls: ReorderUrls,
    canReorderGroups: boolean,
    canReorderModules: boolean,
) {
    const { alert } = useAlert();
    const sortablesRef = useRef<Sortable[]>([]);

    useEffect(() => {
        const root = rootRef.current;
        if (!root) {
            return;
        }

        sortablesRef.current.forEach((instance) => instance.destroy());
        sortablesRef.current = [];

        const groupList = root.querySelector<HTMLElement>('#module-group-list');

        if (groupList && reorderUrls.groups && canReorderGroups) {
            sortablesRef.current.push(
                Sortable.create(groupList, {
                    handle: '.group-drag-handle',
                    animation: 150,
                    draggable: '.module-group-card',
                    onEnd: async (event: SortableEvent) => {
                        if (!orderChanged(event)) {
                            return;
                        }

                        const ids = [...groupList.querySelectorAll(':scope > .module-group-card[data-id]')].map(
                            (element) => parseInt((element as HTMLElement).dataset.id ?? '0', 10),
                        );

                        try {
                            const result = await putJson(reorderUrls.groups!, { ids });
                            await alert({
                                type: 'success',
                                title: 'Berhasil',
                                message:
                                    result.message ??
                                    'Urutan grup modul berhasil disimpan.',
                            });
                            refreshSidebarNavigation();
                        } catch (error) {
                            await alert({
                                type: 'error',
                                title: 'Gagal',
                                message:
                                    error instanceof Error
                                        ? error.message
                                        : 'Gagal menyimpan urutan grup.',
                            });
                            window.location.reload();
                        }
                    },
                }),
            );
        }

        if (reorderUrls.modules && canReorderModules) {
            root.querySelectorAll<HTMLElement>('[data-module-list]').forEach((listElement) => {
                sortablesRef.current.push(
                    Sortable.create(listElement, {
                        handle: '.module-drag-handle',
                        animation: 150,
                        draggable: '.module-row',
                        group: { name: 'modules', pull: false, put: false },
                        onEnd: async (event: SortableEvent) => {
                            if (!orderChanged(event)) {
                                return;
                            }

                            const groupId = parseInt(listElement.dataset.moduleList ?? '0', 10);
                            const ids = [...listElement.querySelectorAll(':scope > .module-row[data-id]')].map(
                                (element) => parseInt((element as HTMLElement).dataset.id ?? '0', 10),
                            );

                            try {
                                const result = await putJson(reorderUrls.modules!, {
                                    module_group_id: groupId,
                                    ids,
                                });
                                await alert({
                                    type: 'success',
                                    title: 'Berhasil',
                                    message:
                                        result.message ?? 'Urutan modul berhasil disimpan.',
                                });
                                refreshSidebarNavigation();
                            } catch (error) {
                                await alert({
                                    type: 'error',
                                    title: 'Gagal',
                                    message:
                                        error instanceof Error
                                            ? error.message
                                            : 'Gagal menyimpan urutan modul.',
                                });
                                window.location.reload();
                            }
                        },
                    }),
                );
            });
        }

        return () => {
            sortablesRef.current.forEach((instance) => instance.destroy());
            sortablesRef.current = [];
        };
    }, [rootRef, reorderUrls, canReorderGroups, canReorderModules, alert]);
}
