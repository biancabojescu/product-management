import { initDateInput, toIsoDate, validateDateInput } from '../utils/dates.js';

export function initProductFilters(root, { onApply, onOpen }) {
    const form = root.querySelector('form');
    const toggle = root.querySelector('[aria-controls="productFilters"]');
    const closeButton = root.querySelector('#closeFilters');
    const countBadge = root.querySelector('#activeFilterCount');
    const minPrice = form.elements.namedItem('min_price');
    const maxPrice = form.elements.namedItem('max_price');
    const availableOn = form.elements.namedItem('available_on');
    initDateInput(availableOn);

    function close(restoreFocus = false) {
        form.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        if (restoreFocus) toggle.focus();
    }

    function open() {
        onOpen?.();
        form.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        form.elements.namedItem('in_stock').focus();
    }

    function read() {
        const parameters = new URLSearchParams();
        for (const [key, value] of new FormData(form)) {
            const trimmed = value.trim();
            if (trimmed === '') continue;
            parameters.set(key, key === 'available_on' ? toIsoDate(trimmed) : ['min_price', 'max_price'].includes(key) ? Number(trimmed).toFixed(2) : trimmed);
        }
        return parameters;
    }

    function apply() {
        const parameters = read();
        const count = ['min_price', 'max_price', 'in_stock', 'available_on'].filter((key) => parameters.has(key)).length;
        countBadge.textContent = String(count);
        countBadge.hidden = count === 0;
        close(!form.hidden);
        onApply(parameters);
    }

    function reset() {
        form.reset();
        maxPrice.setCustomValidity('');
        availableOn.setCustomValidity('');
        apply();
    }

    function validatePriceRange() {
        const invalid = minPrice.value !== '' && maxPrice.value !== '' && Number(minPrice.value) > Number(maxPrice.value);
        maxPrice.setCustomValidity(invalid ? 'Maximum price must be greater than or equal to minimum price.' : '');
    }

    minPrice.addEventListener('input', validatePriceRange);
    maxPrice.addEventListener('input', validatePriceRange);
    toggle.addEventListener('click', () => form.hidden ? open() : close());
    closeButton.addEventListener('click', () => close(true));
    root.querySelector('#resetFilters').addEventListener('click', reset);
    document.addEventListener('pointerdown', (event) => {
        if (!form.hidden && !root.contains(event.target)) close();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !form.hidden) {
            event.preventDefault();
            close(true);
        }
    });
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        validatePriceRange();
        validateDateInput(availableOn);
        if (form.reportValidity()) apply();
    });

    return { read, reset, close };
}
