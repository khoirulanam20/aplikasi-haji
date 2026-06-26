const MODULE_ACTION_LABELS: Record<string, string> = {
    read: 'Read',
    list: 'List',
    delete: 'Delete',
    assign: 'Assign',
    show: 'Show',
    create: 'Create',
    edit: 'Edit',
    update: 'Update',
    notify: 'Notification',
};



export interface CustomModuleAction {
    action: string;
    label: string;
}

export function slugifyRbacAction(title: string): string {
    return title
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_|_$/g, '')
        .slice(0, 50);
}

export function moduleActionLabel(action: string, customLabel?: string | null): string {
    if (customLabel) {
        return customLabel;
    }

    return MODULE_ACTION_LABELS[action] ?? action.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

export function moduleActionDescription(_action: string): string | undefined {
    return undefined;
}

export function permissionActionLabel(
    permissionName: string,
    displayLabel?: string | null,
): string {
    if (displayLabel) {
        return displayLabel;
    }

    const action = permissionName.includes('.') ? permissionName.split('.').pop() : permissionName;

    return action ? moduleActionLabel(action) : permissionName;
}

export function isBuiltInModuleAction(action: string, builtInActions: string[]): boolean {
    return builtInActions.includes(action);
}
