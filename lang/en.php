
<?php

return [
    'auth.config_error' => 'Server configuration error.',
    'auth.unauthorized' => 'Invalid username or password.',

    'product.not_found' => 'Product not found.',
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
    'general.validation_error' => 'Invalid input data.',
    'general.invalid_json' => 'Invalid JSON request body.',
    'general.invalid_content_type' => 'Unsupported request content type.',
    'general.forbidden_origin' => 'Request origin or headers are not allowed.',
    'general.method_not_allowed' => 'HTTP method not allowed.',
];
