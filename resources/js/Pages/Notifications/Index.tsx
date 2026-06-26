import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PaginationLinks from '@/Components/PaginationLinks';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { NotificationItem, Paginated } from '@/types';

interface NotificationsIndexProps {
    notifications: Paginated<NotificationItem>;
}

export default function Index({ notifications }: NotificationsIndexProps) {
    return (
        <AppLayout header="Notifications">
            <Head title="Notifications" />

            <div className="space-y-4" data-tour="notifications-list">
                {notifications.data.map((notification) => (
                    <Card key={notification.id}>
                        <CardContent className="flex items-start justify-between gap-4 pt-6">
                            <div>
                                <p className="font-medium">
                                    {notification.data.title ?? 'Notification'}
                                </p>
                                <p className="text-sm text-text-secondary">
                                    {notification.data.message ?? notification.data.body}
                                </p>
                                <p className="mt-1 text-xs text-text-secondary">
                                    {new Date(notification.created_at).toLocaleString()}
                                </p>
                            </div>
                            <div data-tour={notification.id === notifications.data[0]?.id ? 'notifications-actions' : undefined}>
                            {!notification.read_at && (
                                <Button variant="secondary" size="sm" asChild>
                                    <Link
                                        href={route('notifications.read', notification.id)}
                                        method="post"
                                        as="button"
                                    >
                                        Mark as read
                                    </Link>
                                </Button>
                            )}
                            </div>
                        </CardContent>
                    </Card>
                ))}
                <PaginationLinks paginator={notifications} routeName="notifications.index" />
            </div>
        </AppLayout>
    );
}
