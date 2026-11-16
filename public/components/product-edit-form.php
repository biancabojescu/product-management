<form id="editProductForm" class="inline-product-form" enctype="multipart/form-data">
    <fieldset id="editProductFields" class="product-layout" disabled>
        <legend class="visually-hidden">Edit product details</legend>
        <div class="product-media">
            <div class="product-preview">
                <img id="productImage" src="/assets/images/image.png" alt="Product image" width="400" height="400">
            </div>
            <span id="imagePreviewLabel" class="preview-label" hidden>Unsaved image preview</span>
            <div class="image-upload-controls">
                <label class="visually-hidden" for="editImage">Product image</label>
                <input id="editImage" name="image" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="editImageHint">
                <p id="editImageHint" class="editor-hint">JPG, PNG or WebP. Maximum 2 MB.</p>
                <div class="image-actions">
                    <button id="removeProductImage" class="image-action" type="button" hidden>Remove image</button>
                    <button id="undoProductImage" class="image-action" type="button" hidden>Undo image change</button>
                </div>
            </div>
        </div>

        <section class="inline-product-details" aria-label="Product details, click a value to edit">
            <div class="product-detail-heading">
                <span id="productId" class="count-badge" hidden></span>
                <span id="productStockStatus" class="stock-badge"></span>
            </div>
            <div class="inline-field inline-name">
                <label for="editName">Product name</label>
                <input id="editName" class="inline-value" name="name" type="text" maxlength="255" placeholder="Product name" required>
            </div>
            <div class="inline-field">
                <label for="editDescription">Description</label>
                <textarea id="editDescription" class="inline-value" name="description" rows="3" maxlength="65535" placeholder="Add a description..."></textarea>
            </div>
            <div class="product-value-row">
                <div class="inline-field">
                    <label for="editPrice">Price</label>
                    <input id="editPrice" class="inline-value inline-number" name="price" type="number" min="0" max="99999999.99" step="0.01" inputmode="decimal" required>
                </div>
                <div class="inline-field">
                    <label for="editStock">Stock</label>
                    <div class="stock-value">
                        <input id="editStock" class="inline-value inline-number" name="stock" type="number" min="0" max="4294967295" step="1" inputmode="numeric" required>
                        <span class="stock-unit" aria-hidden="true">units</span>
                    </div>
                </div>
            </div>
            <div class="inline-field">
                <label for="editAvailability">Available from</label>
                <input id="editAvailability" class="inline-value" name="availability_date" type="text" data-date-input placeholder="dd/mm/yyyy" maxlength="10" pattern="[0-9]{2}/[0-9]{2}/[0-9]{4}" aria-describedby="editAvailabilityHint">
                <p id="editAvailabilityHint" class="editor-hint">Leave empty to use today's date.</p>
            </div>
            <dl class="product-metadata">
                <div><dt>Created</dt><dd id="productCreated">-</dd></div>
                <div><dt>Last updated</dt><dd id="productUpdated">-</dd></div>
            </dl>
        </section>
    </fieldset>
</form>
