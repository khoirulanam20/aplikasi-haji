import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { moduleActionLabel } from '@/lib/moduleActions';
import { ModuleRecord } from '@/types';

interface ModulesShowProps {
    module: ModuleRecord;
}

export default function Show({ module }: ModulesShowProps) {
    return (
        <AppLayout header={`Modul: ${module.title}`}>
            <Head title={module.title} />

            <div className="card space-y-4" data-tour="module-detail">
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-xl font-semibold">{module.title}</h2>
                        <p className="text-sm text-text-secondary">{module.code}</p>
                    </div>
                    <Button variant="secondary" asChild>
                        <Link href={route('app.modules.index')}>Kembali</Link>
                    </Button>
                </div>

                <dl className="grid gap-3 sm:grid-cols-2">
                    <div>
                        <dt className="text-sm text-text-secondary">Grup</dt>
                        <dd className="font-medium">{module.group?.name ?? '-'}</dd>
                    </div>
                    <div>
                        <dt className="text-sm text-text-secondary">Layout</dt>
                        <dd className="font-medium">{module.layout_type}</dd>
                    </div>
                    <div>
                        <dt className="text-sm text-text-secondary">Route</dt>
                        <dd className="font-medium">{module.route_name ?? '-'}</dd>
                    </div>
                    <div>
                        <dt className="text-sm text-text-secondary">Status</dt>
                        <dd>
                            <Badge variant={module.is_active ? 'success' : 'warning'}>
                                {module.is_active ? 'Aktif' : 'Nonaktif'}
                            </Badge>
                        </dd>
                    </div>
                </dl>

                {module.description && <p className="text-sm text-text-secondary">{module.description}</p>}

                <div data-tour="module-actions">
                    <h3 className="mb-2 font-medium">Actions</h3>
                    <div className="flex flex-wrap gap-2">
                        {module.actions?.map((action) => (
                            <Badge key={action.id} variant={action.is_enabled ? 'success' : 'default'}>
                                {moduleActionLabel(action.action, action.label)}
                            </Badge>
                        ))}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
