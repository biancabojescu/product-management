import { ApiError } from '../apiError.js';

export function showToast({ title, message, variant = 'error', container = document.getElementById('toastContainer') }) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${variant}`;
    toast.setAttribute('role', variant === 'error' ? 'alert' : 'status');
    toast.setAttribute('aria-atomic', 'true');

    const icon = document.createElement('span');
    icon.className = 'toast-icon';
    icon.textContent = variant === 'success' ? '✓' : '!';
    icon.setAttribute('aria-hidden', 'true');

    const content = document.createElement('div');
    const heading = document.createElement('p');
    heading.className = 'toast-title';
    heading.textContent = title;
    const body = document.createElement('p');
    body.className = 'toast-message';
    body.textContent = message;
    content.append(heading, body);

    const close = document.createElement('button');
    close.className = 'toast-close';
    close.type = 'button';
    close.textContent = '×';
    close.setAttribute('aria-label', 'Dismiss notification');
    close.addEventListener('click', () => toast.remove());
    toast.append(icon, content, close);
    container.replaceChildren(toast);

    if (variant === 'success') setTimeout(() => toast.remove(), 6000);
}

export function showError(error, title = 'Unable to load products', container) {
    const details = error instanceof ApiError && error.details && typeof error.details === 'object'
        ? Object.values(error.details).filter((message) => typeof message === 'string')
        : [];
    showToast({ title, message: [error.message, ...details].join(' '), container });
}
