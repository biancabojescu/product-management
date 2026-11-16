export function initProductPagination(root, { onPageChange }) {
    const pageStatus = root.querySelector('#pageStatus');
    const previousButton = root.querySelector('#previousPage');
    const nextButton = root.querySelector('#nextPage');
    const numberFormatter = new Intl.NumberFormat('en-GB');
    let pagination = null;
    let loading = false;

    function update(value, { loading: isLoading = false } = {}) {
        pagination = value;
        loading = isLoading;
        previousButton.disabled = loading || !pagination?.has_previous_page;
        nextButton.disabled = loading || !pagination?.has_next_page;
        pageStatus.textContent = pagination
            ? `Page ${numberFormatter.format(pagination.current_page)} of ${numberFormatter.format(pagination.last_page)}`
            : 'Page - of -';
    }

    previousButton.addEventListener('click', () => {
        if (!loading && pagination?.has_previous_page) onPageChange(pagination.current_page - 1);
    });
    nextButton.addEventListener('click', () => {
        if (!loading && pagination?.has_next_page) onPageChange(pagination.current_page + 1);
    });

    update(null);
    return { update };
}
