export function initProductSort(root, { onChange, onOpen }) {
    const toggle = root.querySelector('#toggleSort');
    const dropdown = root.querySelector('#productSort');
    const options = [...root.querySelectorAll('input[name="product_sort"]')];

    function selectedOption() {
        return options.find((option) => option.checked);
    }

    function read() {
        const [sortBy, sortDirection] = selectedOption().value.split(':');
        return new URLSearchParams({ sort_by: sortBy, sort_direction: sortDirection });
    }

    function updateLabel() {
        const label = selectedOption().nextElementSibling.textContent;
        toggle.setAttribute('aria-label', `Sort by: ${label}`);
        toggle.title = label;
    }

    function close(restoreFocus = false) {
        dropdown.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        if (restoreFocus) toggle.focus();
    }

    function open() {
        onOpen?.();
        dropdown.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        selectedOption().focus();
    }

    toggle.addEventListener('click', () => dropdown.hidden ? open() : close());
    for (const option of options) {
        option.addEventListener('change', () => {
            if (!option.checked) return;
            updateLabel();
            close(true);
            onChange(read());
        });
    }
    document.addEventListener('pointerdown', (event) => {
        if (!dropdown.hidden && !root.contains(event.target)) close();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !dropdown.hidden) {
            event.preventDefault();
            close(true);
        }
    });

    updateLabel();
    return { read, close };
}
