export function initWarningModal(dialog) {
    const title = dialog.querySelector('[data-warning-title]');
    const message = dialog.querySelector('[data-warning-message]');
    const confirmButton = dialog.querySelector('[data-confirm-warning]');
    const cancelButtons = [...dialog.querySelectorAll('[data-cancel-warning]')];
    const cancelButton = cancelButtons.find((button) => button.hasAttribute('autofocus'));
    const defaults = { title: title.textContent, message: message.textContent, confirmLabel: confirmButton.textContent, cancelLabel: cancelButton.textContent, variant: 'warning' };
    let pending = null;

    function confirm(options = {}) {
        if (pending) return pending.promise;

        const content = { ...defaults, ...options };
        title.textContent = content.title;
        message.textContent = content.message;
        confirmButton.textContent = content.confirmLabel;
        confirmButton.dataset.variant = content.variant;
        cancelButton.textContent = content.cancelLabel;
        const restoreFocus = document.activeElement;
        const promise = new Promise((resolve) => {
            pending = { resolve, restoreFocus };
        });
        pending.promise = promise;
        dialog.returnValue = 'cancel';
        dialog.showModal();
        cancelButton.focus();
        return promise;
    }

    confirmButton.addEventListener('click', () => dialog.close('confirm'));
    for (const button of cancelButtons) {
        button.addEventListener('click', () => dialog.close('cancel'));
    }
    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        dialog.close('cancel');
    });
    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) return;
        const bounds = dialog.getBoundingClientRect();
        if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close('cancel');
    });
    dialog.addEventListener('close', () => {
        const confirmation = pending;
        pending = null;
        if (!confirmation) return;
        if (confirmation.restoreFocus?.isConnected) confirmation.restoreFocus.focus();
        confirmation.resolve(dialog.returnValue === 'confirm');
    });

    return { confirm };
}
