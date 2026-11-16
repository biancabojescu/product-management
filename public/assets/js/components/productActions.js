export function initProductActions(root, { onDelete }) {
    const menu = document.createElement('div');
    menu.id = 'productActionsMenu';
    menu.className = 'product-actions-menu';
    menu.setAttribute('role', 'menu');
    menu.setAttribute('aria-label', 'Product actions');
    menu.hidden = true;
    const edit = document.createElement('a');
    edit.className = 'product-action-item';
    edit.setAttribute('role', 'menuitem');
    edit.textContent = 'Edit product';
    const remove = document.createElement('button');
    remove.className = 'product-action-item product-action-delete';
    remove.type = 'button';
    remove.setAttribute('role', 'menuitem');
    remove.textContent = 'Delete product';
    menu.append(edit, remove);
    document.body.append(menu);
    let currentProduct = null;
    let currentButton = null;

    function close(restoreFocus = false) {
        menu.hidden = true;
        currentButton?.setAttribute('aria-expanded', 'false');
        if (restoreFocus && currentButton?.isConnected) currentButton.focus();
        currentProduct = null;
        currentButton = null;
    }

    function open(product, button) {
        if (currentButton === button && !menu.hidden) return close();
        close();
        currentProduct = product;
        currentButton = button;
        edit.href = `/products/${product.id}`;
        button.setAttribute('aria-expanded', 'true');
        menu.hidden = false;
        const bounds = button.getBoundingClientRect();
        const menuBounds = menu.getBoundingClientRect();
        menu.style.left = `${Math.max(12, Math.min(bounds.left, window.innerWidth - menuBounds.width - 12))}px`;
        menu.style.top = `${bounds.bottom + menuBounds.height + 8 > window.innerHeight ? Math.max(12, bounds.top - menuBounds.height - 8) : bounds.bottom + 8}px`;
        edit.focus();
    }

    function createCell(product) {
        const cell = document.createElement('td');
        cell.className = 'actions-cell';
        const button = document.createElement('button');
        button.className = 'product-actions-toggle';
        button.type = 'button';
        button.setAttribute('aria-label', `Actions for ${product.name}, ID ${product.id}`);
        button.setAttribute('aria-haspopup', 'menu');
        button.setAttribute('aria-controls', menu.id);
        button.setAttribute('aria-expanded', 'false');
        const icon = document.createElement('img');
        icon.src = '/assets/svg/actions.svg';
        icon.alt = '';
        icon.width = 18;
        icon.height = 18;
        icon.setAttribute('aria-hidden', 'true');
        button.append(icon);
        button.addEventListener('click', () => open(product, button));
        button.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                open(product, button);
            }
        });
        cell.append(button);
        return cell;
    }

    remove.addEventListener('click', () => {
        const product = currentProduct;
        close(true);
        if (product) onDelete(product);
    });
    document.addEventListener('pointerdown', (event) => {
        if (!menu.hidden && !menu.contains(event.target) && !currentButton?.contains(event.target)) close();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !menu.hidden) {
            event.preventDefault();
            close(true);
        }
    });
    menu.addEventListener('keydown', (event) => {
        if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
            event.preventDefault();
            const next = event.key === 'Home' ? edit : event.key === 'End' ? remove : document.activeElement === edit ? remove : edit;
            next.focus();
        }
    });
    menu.addEventListener('focusout', (event) => {
        if (event.relatedTarget && !menu.contains(event.relatedTarget)) close();
    });
    document.addEventListener('scroll', () => close(), true);
    window.addEventListener('resize', () => close());
    root.addEventListener('dblclick', (event) => {
        const cell = event.target.closest('td');
        const row = cell?.closest('tr[data-product-id]');
        if (!row || cell.classList.contains('actions-cell') || event.target.closest('a, button, input, select, textarea')) return;
        window.location.assign(`/products/${row.dataset.productId}`);
    });

    return { createCell, close };
}
