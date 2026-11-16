import { apiFetch } from './api.js';
import { ApiError } from './apiError.js';
import { initWarningModal } from './components/warningModal.js';
import { showError, showToast } from './components/toast.js';
import { displayDate, displayDateTime, initDateInput, toIsoDate, validateDateInput } from './utils/dates.js';

const page = document.querySelector('[data-product-id]');
const productId = page.dataset.productId;
const form = document.getElementById('editProductForm');
const fieldset = document.getElementById('editProductFields');
const fields = [...form.querySelectorAll('input, textarea')];
const saveButton = document.getElementById('saveProductChanges');
const deleteButton = document.getElementById('deleteProduct');
const backLinks = [...document.querySelectorAll('[data-product-back]')];
const status = document.getElementById('editStatus');
const state = document.getElementById('productState');
const workspace = document.getElementById('productWorkspace');
const retryButton = document.getElementById('retryProduct');
const image = document.getElementById('productImage');
const imageInput = form.elements.namedItem('image');
const availabilityInput = form.elements.namedItem('availability_date');
initDateInput(availabilityInput);
const removeImageButton = document.getElementById('removeProductImage');
const undoImageButton = document.getElementById('undoProductImage');
const warning = initWarningModal(document.getElementById('warningModal'));
const fallbackImage = '/assets/images/image.png';
let product = null;
let savedValues = null;
let savedSnapshot = '';
let imageAction = 'keep';
let previewUrl = null;
let saving = false;
let deleting = false;
let confirming = false;
let leaving = false;

function snapshot() {
    return JSON.stringify([imageAction, ...fields.map((field) => field.type === 'file'
        ? [...field.files].map((file) => [file.name, file.size, file.type, file.lastModified])
        : field.value)]);
}

function isDirty() {
    return product !== null && snapshot() !== savedSnapshot;
}

function values() {
    return {
        name: form.elements.namedItem('name').value.trim(),
        description: form.elements.namedItem('description').value || null,
        price: Number(form.elements.namedItem('price').value).toFixed(2),
        stock: Number(form.elements.namedItem('stock').value),
        availability_date: toIsoDate(availabilityInput.value),
    };
}

function updateControls() {
    const dirty = isDirty();
    const busy = saving || deleting || leaving;
    fieldset.disabled = busy || !product;
    saveButton.disabled = busy || !dirty;
    deleteButton.disabled = busy || !product;
    for (const link of backLinks) link.setAttribute('aria-disabled', String(busy));
    form.setAttribute('aria-busy', String(busy));
    if (product) {
        const stockStatus = document.getElementById('productStockStatus');
        const inStock = Number(form.elements.namedItem('stock').value) > 0;
        stockStatus.textContent = inStock ? 'In stock' : 'Out of stock';
        stockStatus.className = `stock-badge ${inStock ? 'stock-in' : 'stock-out'}`;
    }
}

function returnToCatalog(notice) {
    leaving = true;
    updateControls();
    window.location.assign(`/?notice=${notice}`);
}

function resizeDescription() {
    const description = form.elements.namedItem('description');
    description.style.height = 'auto';
    const borders = description.offsetHeight - description.clientHeight;
    description.style.height = `${Math.max(92, description.scrollHeight + borders)}px`;
}

if (typeof ResizeObserver === 'function') {
    let descriptionWidth = 0;
    const descriptionObserver = new ResizeObserver(([entry]) => {
        const width = entry.contentRect.width;
        if (width > 0 && width !== descriptionWidth) {
            descriptionWidth = width;
            resizeDescription();
        }
    });
    descriptionObserver.observe(form.elements.namedItem('description'));
} else {
    window.addEventListener('resize', () => {
        if (product) resizeDescription();
    });
}

function clearErrors() {
    for (const field of fields) {
        field.setCustomValidity('');
        field.removeAttribute('aria-invalid');
    }
}

function releasePreview() {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = null;
}

function refreshImage() {
    const storedImage = typeof product?.image === 'string' && /^\/uploads\/products\/[a-zA-Z0-9_-]+\.(?:jpe?g|png|webp)$/i.test(product.image)
        ? product.image : fallbackImage;
    image.src = imageAction === 'remove' ? fallbackImage : previewUrl || storedImage;
    image.alt = product?.name || 'Product image';
    removeImageButton.hidden = imageAction === 'remove' || (!product?.image && !imageInput.files.length);
    undoImageButton.hidden = imageAction === 'keep';
    document.getElementById('imagePreviewLabel').hidden = imageAction === 'keep';
}

function validateProduct(data) {
    if (!data || String(data.id) !== productId || typeof data.name !== 'string' || typeof data.price !== 'string' || !Number.isInteger(data.stock) || data.stock < 0) {
        throw new ApiError('The server returned unexpected product details. Please try again.');
    }
}

function fillProduct(data) {
    product = data;
    releasePreview();
    imageInput.value = '';
    imageAction = 'keep';
    clearErrors();
    for (const key of ['name', 'description', 'price', 'stock', 'availability_date']) {
        form.elements.namedItem(key).value = key === 'availability_date' ? displayDate(product[key]) : product[key] ?? '';
    }
    document.title = `${product.name} | Product Management`;
    document.getElementById('productTitle').textContent = product.name;
    const badge = document.getElementById('productId');
    badge.textContent = `#${product.id}`;
    badge.hidden = false;
    document.getElementById('productCreated').textContent = displayDateTime(product.created_at);
    document.getElementById('productUpdated').textContent = displayDateTime(product.updated_at);
    refreshImage();
    savedValues = values();
    savedSnapshot = snapshot();
    state.hidden = true;
    workspace.hidden = false;
    resizeDescription();
    updateControls();
}

async function loadProduct() {
    state.hidden = false;
    workspace.hidden = true;
    retryButton.hidden = true;
    page.setAttribute('aria-busy', 'true');
    document.getElementById('productStateTitle').textContent = 'Loading product…';
    document.getElementById('productStateMessage').textContent = 'Fetching the latest product details.';
    try {
        if (!/^[1-9]\d*$/.test(productId)) throw new ApiError('This product link is invalid.', { status: 404 });
        const data = await apiFetch(`products/${productId}`, { cache: 'no-store' });
        validateProduct(data);
        fillProduct(data);
    } catch (error) {
        const missing = error instanceof ApiError && [404, 422].includes(error.status);
        document.getElementById('productStateTitle').textContent = missing ? 'Product not found' : 'Could not load product';
        document.getElementById('productStateMessage').textContent = missing
            ? 'This product does not exist. Return to the catalog to choose another product.' : error.message;
        retryButton.hidden = missing;
        showError(error, missing ? 'Product not found' : 'Unable to load product');
    } finally {
        page.setAttribute('aria-busy', 'false');
    }
}

async function discardChanges() {
    if (!isDirty()) return true;
    if (confirming || saving || deleting || leaving) return false;
    confirming = true;
    try {
        return await warning.confirm({ message: 'You have unsaved product changes. Discard them and continue?' });
    } finally {
        confirming = false;
    }
}

form.addEventListener('input', (event) => {
    if (typeof event.target.setCustomValidity === 'function') {
        event.target.setCustomValidity('');
        if (event.target === availabilityInput) validateDateInput(availabilityInput);
        event.target.removeAttribute('aria-invalid');
    }
    if (event.target === form.elements.namedItem('description')) resizeDescription();
    updateControls();
});
form.addEventListener('change', updateControls);
image.addEventListener('error', () => {
    if (image.getAttribute('src') !== fallbackImage) image.src = fallbackImage;
});
imageInput.addEventListener('change', () => {
    releasePreview();
    imageInput.setCustomValidity('');
    imageInput.removeAttribute('aria-invalid');
    const file = imageInput.files[0];
    imageAction = file ? 'replace' : 'keep';
    if (file) {
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size === 0 || file.size > 2 * 1024 * 1024) {
            imageInput.setCustomValidity('Choose a JPG, PNG or WebP image, with a maximum size of 2 MB.');
            imageInput.reportValidity();
        } else {
            previewUrl = URL.createObjectURL(file);
        }
    }
    refreshImage();
    updateControls();
});
removeImageButton.addEventListener('click', () => {
    releasePreview();
    imageInput.value = '';
    imageInput.setCustomValidity('');
    imageInput.removeAttribute('aria-invalid');
    imageAction = product.image ? 'remove' : 'keep';
    refreshImage();
    updateControls();
});
undoImageButton.addEventListener('click', () => {
    releasePreview();
    imageInput.value = '';
    imageInput.setCustomValidity('');
    imageInput.removeAttribute('aria-invalid');
    imageAction = 'keep';
    refreshImage();
    updateControls();
});
for (const link of backLinks) {
    link.addEventListener('click', async (event) => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        const href = event.currentTarget.href;
        if (saving || deleting || leaving || !await discardChanges()) return;
        leaving = true;
        updateControls();
        window.location.assign(href);
    });
}
window.addEventListener('beforeunload', (event) => {
    if (!leaving && (saving || deleting || isDirty())) {
        event.preventDefault();
        event.returnValue = '';
    }
});
retryButton.addEventListener('click', loadProduct);

deleteButton.addEventListener('click', async () => {
    if (!product || saving || deleting || confirming || leaving) return;
    confirming = true;
    let approved;
    try {
        approved = await warning.confirm({
            title: 'Are you sure?',
            message: `Delete "${product.name}"? The product and its image will be permanently deleted. Any unsaved changes will also be discarded.`,
            confirmLabel: 'Delete product',
            cancelLabel: 'Cancel',
            variant: 'danger',
        });
    } finally {
        confirming = false;
    }
    if (!approved) return;

    deleting = true;
    updateControls();
    try {
        const result = await apiFetch(`products/${productId}`, { method: 'DELETE' });
        if (result?.deleted !== true) throw new ApiError('The server did not confirm product deletion.');
        showToast({ title: 'Product deleted', message: 'The product was deleted successfully.', variant: 'success' });
        returnToCatalog('product-deleted');
    } catch (error) {
        showError(error, 'Unable to delete product');
    } finally {
        deleting = false;
        updateControls();
    }
});

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!product || saving || deleting || confirming || leaving) return;
    const name = form.elements.namedItem('name');
    name.value = name.value.trim();
    validateDateInput(availabilityInput);
    if (!form.reportValidity()) return;
    const changes = Object.fromEntries(Object.entries(values()).filter(([key, value]) => value !== savedValues[key]));
    if (!availabilityInput.value.trim()) changes.availability_date = null;
    if (imageAction === 'remove') changes.image = null;
    let options;
    if (imageAction === 'replace') {
        const data = new FormData();
        for (const [key, value] of Object.entries(changes)) data.set(key, value === null ? '' : String(value));
        data.set('image', imageInput.files[0]);
        options = { method: 'POST', body: data };
    } else {
        if (Object.keys(changes).length === 0) {
            fillProduct(product);
            returnToCatalog('product-updated');
            return;
        }
        options = { method: 'PATCH', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(changes) };
    }

    saving = true;
    updateControls();
    try {
        const data = await apiFetch(`products/${productId}`, options);
        validateProduct(data);
        fillProduct(data);
        showToast({ title: 'Product updated', message: 'The product details were saved successfully.', variant: 'success' });
        returnToCatalog('product-updated');
    } catch (error) {
        if (error instanceof ApiError && error.details && typeof error.details === 'object') {
            for (const [key, message] of Object.entries(error.details)) {
                const field = form.elements.namedItem(key);
                if (field && typeof message === 'string') {
                    field.setCustomValidity(message);
                    field.setAttribute('aria-invalid', 'true');
                }
            }
        }
        showError(error, 'Unable to save product');
    } finally {
        saving = false;
        updateControls();
        form.querySelector('[aria-invalid="true"]')?.focus();
    }
});

loadProduct();
