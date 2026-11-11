const form = document.getElementById('createProductForm');

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const formData = new FormData(form);

    for (const [key, value] of formData.entries()) {
        if (value === '' || (value instanceof File && value.size === 0)) {
            formData.delete(key);
        }
    }

    try {
        const response = await fetch('/api/products/', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
            credentials: 'same-origin',
        });

        const result = await response.json();

        if (!response.ok) {
            alert(result.error?.message ?? 'Product creation failed.');
            return;
        }

        alert('Product created successfully!');
        form.reset();

    } catch (error) {
        alert('Could not connect to the server.');
    }
});
