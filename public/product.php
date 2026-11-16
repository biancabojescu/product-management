<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

use App\Middleware\BasicAuthMiddleware;

$authResponse = BasicAuthMiddleware::handle();
if ($authResponse !== null) {
    $authResponse->send();
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$id = preg_match('~^/products?/([^/]+)/?$~', $path, $matches) ? $matches[1] : ($_GET['id'] ?? null);
$productId = is_string($id) && ctype_digit($id)
    ? filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    : false;
if ($productId === false) {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product | Product Management</title>
    <link rel="icon" type="image/svg+xml" href="/assets/svg/product.svg">
    <link rel="stylesheet" href="/assets/css/products.css">
    <link rel="stylesheet" href="/assets/css/product.css">
    <link rel="stylesheet" href="/assets/css/components/warning-modal.css">
    <script type="module" src="/assets/js/product.js"></script>
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to product</a>
    <header class="site-header">
        <div class="header-content">
            <div class="brand">
                <img class="brand-mark" src="/assets/svg/product.svg" alt="" aria-hidden="true" width="32" height="32">
                <span>Product Management</span>
            </div>
        </div>
    </header>

    <main id="main-content" class="page product-page" data-product-id="<?= $productId === false ? '' : $productId ?>">
        <div class="product-toolbar">
            <div class="product-location">
                <nav class="product-breadcrumb" aria-label="Breadcrumb">
                    <a href="/" data-product-back>Catalog</a>
                    <span aria-hidden="true">/</span>
                    <span aria-current="page">Product details</span>
                </nav>
                <span id="editStatus" class="edit-status" role="status" aria-live="polite"></span>
            </div>
            <div class="product-toolbar-actions">
                <a class="button button-secondary button-back" href="/" data-product-back>Back to products</a>
                <button id="deleteProduct" class="button button-danger" type="button" disabled>Delete product</button>
                <button id="saveProductChanges" class="button button-primary" type="submit" form="editProductForm" disabled>Save changes</button>
            </div>
        </div>
        <h1 id="productTitle" class="visually-hidden">Product details</h1>

        <section id="productState" class="product-state catalog" role="status" aria-live="polite">
            <h2 id="productStateTitle">Loading product…</h2>
            <p id="productStateMessage">Fetching the latest product details.</p>
            <button id="retryProduct" class="button button-secondary" type="button" hidden>Try again</button>
        </section>

        <div id="productWorkspace" class="product-workspace catalog" hidden>
            <?php require __DIR__ . '/components/product-edit-form.php'; ?>
        </div>
        <noscript><p class="noscript-message">Enable JavaScript to view and edit this product.</p></noscript>
    </main>

    <?php require __DIR__ . '/components/warning-modal.php'; ?>
    <div id="toastContainer" class="toast-container" role="region" aria-label="Notifications"></div>
</body>
</html>
