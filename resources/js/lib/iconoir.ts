const ICONOIR_CLASS_PATTERN = /^iconoir-[a-z0-9-]+$/;
const ICON_HTML_PATTERN = /^<i\s+class="(iconoir-[a-z0-9-]+)"\s*><\/i>$/i;

export function iconoirClass(name: string): string {
    const normalized = name.replace(/^iconoir-/, '');
    return `iconoir-${normalized}`;
}

export function iconoirHtml(name: string): string {
    return `<i class="${iconoirClass(name)}"></i>`;
}

export function parseIconoirName(html: string | null | undefined): string | null {
    if (!html?.trim()) {
        return null;
    }

    const match = html.trim().match(ICON_HTML_PATTERN);
    if (match) {
        return match[1].replace(/^iconoir-/, '');
    }

    const classMatch = html.match(/iconoir-[a-z0-9-]+/);
    return classMatch ? classMatch[0].replace(/^iconoir-/, '') : null;
}

export function sanitizeIconoirHtml(html: string | null | undefined): string | null {
    if (!html?.trim()) {
        return null;
    }

    const trimmed = html.trim();
    const match = trimmed.match(ICON_HTML_PATTERN);
    if (!match || !ICONOIR_CLASS_PATTERN.test(match[1])) {
        return null;
    }

    return `<i class="${match[1]}"></i>`;
}
