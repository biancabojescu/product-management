<dialog id="createProductModal" class="product-modal" aria-labelledby="createProductTitle" aria-describedby="createProductDescription">
    <form id="createProductForm" class="product-form" enctype="multipart/form-data">
        <header class="modal-heading">
            <div>
                <h2 id="createProductTitle">Create product</h2>
            </div>
            <button class="modal-close" type="button" data-close-modal aria-label="Close create product modal">&times;</button>
        </header>

        <div class="modal-body">
            <div data-modal-notifications class="modal-notifications" role="region" aria-label="Product notifications"></div>

            <div class="field">
                <label for="createName">Product name <span class="required-mark" aria-hidden="true">*</span></label>
                <input id="createName" name="name" type="text" placeholder="Enter a product name" maxlength="255" required autofocus>
            </div>

            <div class="field">
                <label for="createDescription">Description <span class="optional-label">Optional</span></label>
                <textarea id="createDescription" name="description" rows="4" maxlength="65535" placeholder="Describe your product…"></textarea>
            </div>

            <div class="modal-field-row">
                <div class="field">
                    <label for="createPrice">Price <span class="required-mark" aria-hidden="true">*</span></label>
                    <input id="createPrice" name="price" type="number" min="0" max="99999999.99" step="0.01" inputmode="decimal" placeholder="0.00" required>
                </div>
                <div class="field">
                    <label for="createStock">Stock <span class="required-mark" aria-hidden="true">*</span></label>
                    <input id="createStock" name="stock" type="number" min="0" max="4294967295" step="1" inputmode="numeric" value="0" required>
                </div>
            </div>

            <div class="field">
                <label for="createAvailability">Available from <span class="optional-label">Optional</span></label>
                <input id="createAvailability" name="availability_date" type="text" data-date-input placeholder="dd/mm/yyyy" maxlength="10" pattern="[0-9]{2}/[0-9]{2}/[0-9]{4}" aria-describedby="availabilityHint">
                <p id="availabilityHint" class="field-hint">Leave empty to use today's date.</p>
            </div>

            <div class="field">
                <label for="createImage">Product image <span class="optional-label">Optional</span></label>
                <div class="image-upload">
                    <img id="createImagePreview" class="upload-preview" src="/assets/images/image.png" alt="Product image preview" width="76" height="76">
                    <div class="upload-controls">
                        <input id="createImage" name="image" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="imageHint">
                        <p id="imageHint" class="field-hint">JPG, PNG or WebP. Maximum 2 MB.</p>
                        <button id="removeCreateImage" class="remove-image" type="button" hidden>Remove image</button>
                    </div>
                </div>
            </div>
        </div>

        <footer class="modal-actions">
            <span id="createProductStatus" class="save-status" role="status" aria-live="polite"></span>
            <button class="button button-secondary" type="button" data-close-modal>Cancel</button>
            <button id="saveProduct" class="button button-primary" type="submit">Create product</button>
        </footer>
    </form>
</dialog>
