const clp = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' });
const number = new Intl.NumberFormat('es-CL');
const dateTime = new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium', timeStyle: 'short' });

export function formatCLP(amount: number | null | undefined): string {
    return clp.format(amount ?? 0);
}

export function formatNumber(value: number): string {
    return number.format(value);
}

export function formatDate(iso: string | null | undefined): string {
    return iso ? dateTime.format(new Date(iso)) : '—';
}
