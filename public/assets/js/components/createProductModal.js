import { apiFetch } from '../api.js';
import { ApiError } from '../apiError.js';
import { showError, showToast } from './toast.js';
import { initDateInput, toIsoDate, validateDateInput } from '../utils/dates.js';

export function initCreateProductModal(dialog, { trigger, onOpen, onCreated, confirmDiscard }) {
    const form = dialog.querySelector('form');
    const fields = form.querySelectorAll('input, textarea');
    const closeButtons = dialog.querySelectorAll('[data-close-modal]');
    const submitButton = form.querySelector('[type="submit"]');
    const status = dialog.querySelector('#createProductStatus');
    const notifications = dialog.querySelector('[data-modal-notifications]');
    const imageInput = form.elements.namedItem('image');
    const availabilityInput = form.elements.namedItem('availability_date');
    initDateInput(availabilityInput);
    const preview = dialog.querySelector('#createImagePreview');
    const removeImageButton = dialog.querySelector('#removeCreateImage');
    let previewUrl = null;
    let saving = false;
    let confirmingClose = false;
    let initialValues = '';

    function formSnapshot() {
        return JSON.stringify([...fields].map((field) => field.type === 'file'
            ? [...field.files].map((file) => [file.name, file.size, file.type, file.lastModified])
            : field.value));
    }

    function resetPreview() {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
        preview.src = '/assets/images/image.png';
        removeImageButton.hidden = true;
    }

    function clearErrors() {
        notifications.replaceChildren();
        for (const field of fields) {
            field.setCustomValidity('');
            field.removeAttribute('aria-invalid');
        }
    }

    async function close() {
        if (saving || confirmingClose || !dialog.open) return;
        if (formSnapshot() === initialValues) {
            dialog.close();
            return;
        }

        confirmingClose = true;
        try {
            const discard = await confirmDiscard();
            if (discard && dialog.open) dialog.close();
        } finally {
            confirmingClose = false;
        }
    }

    function setSaving(value) {
        saving = value;
        for (const control of [...fields, ...closeButtons, submitButton, removeImageButton]) control.disabled = value;
        form.setAttribute('aria-busy', String(value));
        submitButton.textContent = value ? 'Creating…' : 'Create product';
        status.textContent = value ? 'Saving product…' : '';
    }

    trigger.addEventListener('click', () => {
        onOpen?.();
        clearErrors();
        initialValues = formSnapshot();
        dialog.showModal();
        document.body.classList.add('modal-open');
    });
    for (const button of closeButtons) button.addEventListener('click', close);
    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        close();
    });
    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) return;
        const bounds = dialog.getBoundingClientRect();
        if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) close();
    });
    dialog.addEventListener('close', () => {
        form.reset();
        clearErrors();
        resetPreview();
        document.body.classList.remove('modal-open');
        trigger.focus();
    });

    form.addEventListener('input', (event) => {
        if (typeof event.target.setCustomValidity !== 'function') return;
        event.target.setCustomValidity('');
        if (event.target === availabilityInput) validateDateInput(availabilityInput);
        event.target.removeAttribute('aria-invalid');
    });
    imageInput.addEventListener('change', () => {
        resetPreview();
        imageInput.setCustomValidity('');
        const file = imageInput.files[0];
        if (!file) return;
        removeImageButton.hidden = false;
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size === 0 || file.size > 2 * 1024 * 1024) {
            imageInput.setCustomValidity('Choose a JPG, PNG or WebP image, with a maximum size of 2 MB.');
            imageInput.reportValidity();
            return;
        }
        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
    });
    removeImageButton.addEventListener('click', () => {
        imageInput.value = '';
        imageInput.setCustomValidity('');
        imageInput.removeAttribute('aria-invalid');
        resetPreview();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (saving || confirmingClose) return;
        const name = form.elements.namedItem('name');
        name.value = name.value.trim();
        validateDateInput(availabilityInput);
        if (!form.reportValidity()) return;

        const data = new FormData(form);
        for (const [key, value] of [...data]) {
            if ((typeof value === 'string' && value.trim() === '') || (value instanceof File && value.size === 0)) data.delete(key);
        }
        data.set('price', Number(form.elements.namedItem('price').value).toFixed(2));
        data.set('stock', String(Number(form.elements.namedItem('stock').value)));
        if (availabilityInput.value.trim()) data.set('availability_date', toIsoDate(availabilityInput.value));

        notifications.replaceChildren();
        setSaving(true);
        try {
            const result = await apiFetch('products', { method: 'POST', body: data });
            if (result?.created !== true) throw new ApiError('The server did not confirm product creation.');
        } catch (error) {
            setSaving(false);
            if (error instanceof ApiError && error.details && typeof error.details === 'object') {
                for (const [key, message] of Object.entries(error.details)) {
                    const field = form.elements.namedItem(key);
                    if (field && typeof message === 'string') {
                        field.setCustomValidity(message);
                        field.setAttribute('aria-invalid', 'true');
                    }
                }
                form.querySelector('[aria-invalid="true"]')?.focus();
            }
            showError(error, 'Unable to create product', notifications);
            return;
        }

        setSaving(false);
        dialog.close();
        onCreated();
        showToast({ title: 'Product created', message: 'The product was created successfully.', variant: 'success' });
    });
}
