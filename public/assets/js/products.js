import { apiFetch } from './api.js';
import { ApiError } from './apiError.js';
import { initProductFilters } from './components/productFilters.js';
import { initProductSort } from './components/productSort.js';
import { initProductPagination } from './components/productPagination.js';
import { initCreateProductModal } from './components/createProductModal.js';
import { initWarningModal } from './components/warningModal.js';
import { initProductActions } from './components/productActions.js';
import { displayDate } from './utils/dates.js';
import { showError, showToast } from './components/toast.js';

const searchForm = document.getElementById('productSearch');
const searchInput = document.getElementById('search');
const tbody = document.getElementById('productsBody');
const tableContainer = document.getElementById('tableContainer');
const resultStatus = document.getElementById('resultStatus');
const productCount = document.getElementById('productCount');
const perPage = document.getElementById('perPage');
const toastContainer = document.getElementById('toastContainer');
const numberFormatter = new Intl.NumberFormat('en-GB');
const priceFormatter = new Intl.NumberFormat('en-GB', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const timeFormatter = new Intl.DateTimeFormat('en-GB', { hour: '2-digit', minute: '2-digit' });

let query = new URLSearchParams();
let pagination = null;
let pendingRequest = null;
let searchTimeout = null;
let deletingProduct = false;

function element(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
}

function showState(title, description, { loading = false, action, actionLabel } = {}) {
    const row = element('tr');
    const cell = element('td', 'state-cell');
    cell.colSpan = 9;

    if (loading) {
        const indicator = element('span', 'loading-dot');
        indicator.setAttribute('aria-hidden', 'true');
        cell.append(indicator, document.createTextNode(title));
    } else {
        cell.append(element('strong', 'state-title', title), element('p', 'state-description', description));
        if (action) {
            const button = element('button', 'button button-secondary', actionLabel);
            button.type = 'button';
            button.addEventListener('click', action);
            cell.append(button);
        }
    }

    row.append(cell);
    tbody.replaceChildren(row);
}

function imageCell(product) {
    const cell = element('td');
    const container = element('div', 'product-image');
    const placeholder = () => {
        const image = element('img');
        image.src = '/assets/images/image.png';
        image.alt = 'No product image';
        image.loading = 'lazy';
        image.decoding = 'async';
        image.width = 58;
        image.height = 58;
        container.replaceChildren(image);
    };

    placeholder();

    if (typeof product.image === 'string' && /^\/uploads\/products\/[a-zA-Z0-9_-]+\.(?:jpe?g|png|webp)$/i.test(product.image)) {
        const image = element('img');
        image.alt = product.name;
        image.loading = 'lazy';
        image.decoding = 'async';
        image.width = 58;
        image.height = 58;
        image.addEventListener('error', placeholder, { once: true });
        image.src = product.image;
        container.removeAttribute('role');
        container.removeAttribute('aria-label');
        container.replaceChildren(image);
    }

    cell.append(container);
    return cell;
}

function dateCell(value, { timestamp = false } = {}) {
    const cell = element('td', 'date-cell');

    if (!value) {
        cell.textContent = timestamp ? '—' : 'Anytime';
        return cell;
    }

    const normalized = value.replace(' ', 'T');
    const date = new Date(timestamp ? normalized : `${normalized}T00:00:00`);

    if (Number.isNaN(date.getTime())) {
        cell.textContent = value;
        return cell;
    }

    const time = element('time', null, displayDate(value));
    time.dateTime = normalized;
    cell.append(time);

    if (timestamp && normalized.includes('T')) {
        cell.append(element('span', 'date-clock', timeFormatter.format(date)));
    }

    return cell;
}

function renderProducts(products) {
    const rows = document.createDocumentFragment();

    for (const product of products) {
        const row = element('tr');
        row.dataset.productId = String(product.id);
        row.title = 'Double-click a detail to edit this product';
        const details = element('td');
        details.append(element('div', 'product-name', product.name));
        details.append(element('p', 'product-description', product.description || 'No description provided.'));

        const stock = element('td');
        stock.append(element(
            'span',
            `stock-badge ${product.stock > 0 ? 'stock-in' : 'stock-out'}`,
            product.stock > 0 ? `In stock` : 'Out of stock',
        ));

        row.append(
            productActions.createCell(product),
            element('td', 'product-id', `#${product.id}`),
            imageCell(product),
            details,
            element('td', 'product-price numeric-column', priceFormatter.format(Number(product.price))),
            stock,
            dateCell(product.availability_date),
            dateCell(product.created_at, { timestamp: true }),
            dateCell(product.updated_at, { timestamp: true }),
        );
        rows.append(row);
    }

    tbody.replaceChildren(rows);
}

async function loadProducts(page = 1) {
    productActions.close();
    pendingRequest?.abort();
    const request = new AbortController();
    pendingRequest = request;

    const parameters = new URLSearchParams(query);
    parameters.set('page', String(page));
    tableContainer.setAttribute('aria-busy', 'true');
    resultStatus.textContent = 'Loading products…';
    toastContainer.replaceChildren();
    productCount.hidden = true;
    paginationControls.update(pagination, { loading: true });
    showState('Loading products…', '', { loading: true });

    try {
        const data = await apiFetch('products', { query: parameters, signal: request.signal, cache: 'no-store' });

        if (request.signal.aborted) return;

        if (!Array.isArray(data?.items) || !data.pagination) {
            throw new ApiError('The server returned an unexpected product list. Please try again.');
        }

        pagination = data.pagination;

        if (pagination.current_page > pagination.last_page) {
            return loadProducts(pagination.last_page);
        }

        productCount.textContent = numberFormatter.format(pagination.total);
        productCount.hidden = false;

        if (data.items.length === 0) {
            const filtered = ['search', 'min_price', 'max_price', 'in_stock', 'available_on'].some((key) => query.has(key));
            resultStatus.textContent = filtered ? 'No matching products' : '0 products';
            showState(
                filtered ? 'No products match your filters' : 'Your catalog is empty',
                filtered ? 'Try a different name or adjust your filters.' : 'Products will appear here when they are added to the catalog.',
                filtered ? {
                    action: () => {
                        searchInput.value = '';
                        resetFilters();
                    },
                    actionLabel: 'Clear search and filters',
                } : {},
            );
        } else {
            renderProducts(data.items);
            const start = (pagination.current_page - 1) * pagination.per_page + 1;
            const end = start + data.items.length - 1;
            resultStatus.textContent = `Showing ${numberFormatter.format(end)} of ${numberFormatter.format(pagination.total)} products`;
        }
    } catch (error) {
        if (error.name === 'AbortError' || request.signal.aborted) return;

        pagination = null;
        resultStatus.textContent = 'Products could not be loaded';
        showState('Could not load products', 'Please try again. If the problem continues, check your connection.', {
            action: () => loadProducts(page),
            actionLabel: 'Try again',
        });
        showError(error);
    } finally {
        if (pendingRequest === request) {
            tableContainer.setAttribute('aria-busy', 'false');
            paginationControls.update(pagination);
            pendingRequest = null;
        }
    }
}

function setSearchQuery(parameters) {
    const search = searchInput.value.trim();
    if (search) parameters.set('search', search);
    else parameters.delete('search');
}

function applySearch() {
    clearTimeout(searchTimeout);
    if (searchInput.value.trim() === (query.get('search') ?? '')) return;
    setSearchQuery(query);
    loadProducts();
}

function scheduleSearch() {
    clearTimeout(searchTimeout);
    if (searchInput.value === '') applySearch();
    else searchTimeout = setTimeout(applySearch, 350);
}

function resetFilters() {
    filters.reset();
}

searchInput.addEventListener('input', (event) => {
    clearTimeout(searchTimeout);
    if (!event.isComposing) scheduleSearch();
});
searchInput.addEventListener('compositionend', scheduleSearch);
searchForm.addEventListener('submit', (event) => {
    event.preventDefault();
    applySearch();
});

perPage.addEventListener('change', () => {
    clearTimeout(searchTimeout);
    setSearchQuery(query);
    query.set('per_page', perPage.value);
    loadProducts();
});

const paginationControls = initProductPagination(document.getElementById('productPagination'), {
    onPageChange: loadProducts,
});

const filters = initProductFilters(document.getElementById('filterMenu'), {
    onOpen: () => sorting.close(),
    onApply(parameters) {
        clearTimeout(searchTimeout);
        query = parameters;
        sorting.read().forEach((value, key) => query.set(key, value));
        query.set('per_page', perPage.value);
        setSearchQuery(query);
        loadProducts();
    },
});

const sorting = initProductSort(document.getElementById('sortMenu'), {
    onOpen: () => filters.close(),
    onChange(parameters) {
        clearTimeout(searchTimeout);
        parameters.forEach((value, key) => query.set(key, value));
        setSearchQuery(query);
        loadProducts();
    },
});

const warningModal = initWarningModal(document.getElementById('warningModal'));
const productActions = initProductActions(tbody, {
    async onDelete(product) {
        if (deletingProduct) return;
        deletingProduct = true;
        try {
            const approved = await warningModal.confirm({
                title: 'Are you sure?',
                message: `Delete "${product.name}"? The product and its image will be permanently deleted.`,
                confirmLabel: 'Delete product',
                cancelLabel: 'Cancel',
                variant: 'danger',
            });
            if (!approved) return;
            const result = await apiFetch(`products/${product.id}`, { method: 'DELETE' });
            if (result?.deleted !== true) throw new ApiError('The server did not confirm product deletion.');
            await loadProducts(pagination?.current_page || 1);
            showToast({ title: 'Product deleted', message: 'The product was deleted successfully.', variant: 'success' });
        } catch (error) {
            showError(error, 'Unable to delete product');
        } finally {
            deletingProduct = false;
        }
    },
});

initCreateProductModal(document.getElementById('createProductModal'), {
    trigger: document.getElementById('createProductButton'),
    confirmDiscard: () => warningModal.confirm(),
    onOpen: () => {
        filters.close();
        sorting.close();
    },
    onCreated: () => {
        clearTimeout(searchTimeout);
        setSearchQuery(query);
        return loadProducts();
    },
});

query = filters.read();
sorting.read().forEach((value, key) => query.set(key, value));
query.set('per_page', perPage.value);
setSearchQuery(query);
loadProducts();

const noticeUrl = new URL(window.location.href);
const notices = new Map([
    ['product-updated', { title: 'Product updated', message: 'The product details were saved successfully.' }],
    ['product-deleted', { title: 'Product deleted', message: 'The product was deleted successfully.' }],
]);
const notice = notices.get(noticeUrl.searchParams.get('notice'));
if (notice) {
    showToast({ ...notice, variant: 'success' });
    noticeUrl.searchParams.delete('notice');
    window.history.replaceState(null, '', noticeUrl.pathname + noticeUrl.search + noticeUrl.hash);
}
