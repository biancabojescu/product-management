<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

use App\Middleware\BasicAuthMiddleware;

$authResponse = BasicAuthMiddleware::handle();

if ($authResponse !== null) {
    $authResponse->send();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse, search and filter your product inventory.">
    <title>Products </title>
    <link rel="icon" type="image/svg+xml" href="/assets/svg/product.svg">
    <link rel="stylesheet" href="/assets/css/products.css">
    <link rel="stylesheet" href="/assets/css/components/product-filters.css">
    <link rel="stylesheet" href="/assets/css/components/product-sort.css">
    <link rel="stylesheet" href="/assets/css/components/product-pagination.css">
    <link rel="stylesheet" href="/assets/css/components/product-actions.css">
    <link rel="stylesheet" href="/assets/css/components/create-product-modal.css">
    <link rel="stylesheet" href="/assets/css/components/warning-modal.css">
    <script type="module" src="/assets/js/products.js"></script>
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to products</a>

    <header class="site-header">
        <div class="header-content">
            <div class="brand">
                <img class="brand-mark" src="/assets/svg/product.svg" alt="" aria-hidden="true" width="32" height="32">
                <span>Product Management</span>
            </div>
        </div>
    </header>

    <main id="main-content" class="page">
        <nav class="catalog-breadcrumb" aria-label="Breadcrumb">
            <span aria-current="page">Catalog</span>
        </nav>

        <div class="page-heading">
            <div>
                <h1>Products <span id="productCount" class="count-badge" hidden></span></h1>
            </div>
        </div>

        <section class="catalog" aria-label="Product catalog">
            <div class="catalog-toolbar">
                <form id="productSearch" class="search-form" role="search" aria-label="Search products">
                    <label class="visually-hidden" for="search">Search products</label>
                    <div class="search-input">
                        <span class="search-icon" aria-hidden="true"></span>
                        <input id="search" name="search" type="search" placeholder="Search by product name…" maxlength="255" autocomplete="off">
                    </div>
                </form>

                <div class="toolbar-actions">
                    <?php require __DIR__ . '/components/product-sort.php'; ?>
                    <?php require __DIR__ . '/components/product-filters.php'; ?>

                    <button id="createProductButton" class="button button-primary" type="button" aria-haspopup="dialog" aria-controls="createProductModal">
                        <span class="button-plus" aria-hidden="true">+</span> Create new product
                    </button>
                </div>
            </div>

            <div id="tableContainer" class="table-scroll" role="region" aria-label="Products table, scroll horizontally for more details" tabindex="0" aria-busy="true">
                <table>
                    <caption class="visually-hidden">Products with image, description, price, stock, availability and timestamps.</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="actions-column"><span class="visually-hidden">Actions</span></th>
                            <th scope="col" class="id-column">ID</th>
                            <th scope="col">Image</th>
                            <th scope="col" class="product-column">Product details</th>
                            <th scope="col" class="numeric-column">Price</th>
                            <th scope="col">Stock</th>
                            <th scope="col">Available from</th>
                            <th scope="col">Created</th>
                            <th scope="col">Updated</th>
                        </tr>
                    </thead>
                    <tbody id="productsBody">
                        <tr><td class="state-cell" colspan="9"><span class="loading-dot" aria-hidden="true"></span> Loading products…</td></tr>
                    </tbody>
                </table>
            </div>

            <footer class="table-footer">
                <div class="page-size">
                    <label for="perPage">Rows per page</label>
                    <select id="perPage" name="per_page">
                        <option value="10">10</option>
                        <option value="20" selected>20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <p id="resultStatus" role="status" aria-live="polite" aria-atomic="true">Loading products…</p>
                </div>

                <?php require __DIR__ . '/components/product-pagination.php'; ?>
            </footer>
        </section>

        <noscript><p class="noscript-message">Enable JavaScript to load products and use search and filters.</p></noscript>
    </main>

    <?php require __DIR__ . '/components/create-product-modal.php'; ?>
    <?php require __DIR__ . '/components/warning-modal.php'; ?>
    <div id="toastContainer" class="toast-container" role="region" aria-label="Notifications"></div>
</body>
</html>
