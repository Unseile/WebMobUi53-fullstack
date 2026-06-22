export function formatLocalDateTime(value) {
    if (!value) return '';
    return new Date(value).toLocaleString('fr-CH', {
        timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        dateStyle: 'short',
        timeStyle: 'short',
    });
}