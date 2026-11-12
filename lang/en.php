
<?php

return [
    'auth.config_error' => 'Server configuration error.',
    'auth.unauthorized' => 'Invalid username or password.',

    'product.not_found' => 'Product not found.',
    'validation.product.id' => 'Product ID must be a positive integer within the supported range.',
    'product.created' => 'Product created successfully.',
    'product.updated' => 'Product updated successfully.',
    'product.deleted' => 'Product deleted successfully.',

    'validation.product.name' => 'Name is required and must have at most 255 characters.',
    'validation.product.description' => 'Description must be valid text.',
    'validation.product.price' => 'Price must be a valid non-negative amount with at most two decimals.',
    'validation.product.image' => 'Image must be a valid JPG, PNG or WebP file, with a maximum size of 2 MB.',
    'validation.product.availability_date' => 'Availability date must be a valid date in YYYY-MM-DD format.',
    'validation.product.stock' => 'Stock must be a non-negative integer.',
    'validation.product.status' => 'Status must be active or inactive.',

    'general.server_error' => 'An unexpected error occurred.',
    'validation.products.page' => 'Page must be a positive integer within the supported range.',
    'validation.products.per_page' => 'Per page must be an integer between 1 and 100.',
    'validation.products.search' => 'Search must be text with at most 255 characters.',
    'validation.products.price' => 'Price must be between 0 and 99999999.99 with at most two decimals.',
    'validation.products.price_range' => 'Maximum price must be greater than or equal to minimum price.',
    'validation.products.in_stock' => 'In stock must be true or false.',
    'validation.products.available_on' => 'Available on must be a valid date in YYYY-MM-DD format between 1000-01-01 and 9999-12-31.',
    'validation.products.sort_by' => 'Sort by must be created_at, name or price.',
    'validation.products.sort_direction' => 'Sort direction must be asc or desc.',
    'general.validation_error' => 'Invalid input data.',
    'general.invalid_json' => 'Invalid JSON request body.',
    'general.invalid_content_type' => 'Unsupported request content type.',
    'general.forbidden_origin' => 'Request origin or headers are not allowed.',
    'general.method_not_allowed' => 'HTTP method not allowed.',
];
