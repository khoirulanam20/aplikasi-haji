function csrfToken(): string {
    return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
}

export async function completeTour(routeName: string): Promise<void> {
    const response = await fetch(route('profile.tours.complete'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ route_name: routeName }),
    });

    if (!response.ok) {
        throw new Error(`Gagal menyimpan status tour (${response.status})`);
    }
}
