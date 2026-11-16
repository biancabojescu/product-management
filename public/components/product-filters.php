<div id="filterMenu" class="filter-menu">
    <button id="toggleFilters" class="button button-secondary filter-toggle" type="button" aria-expanded="false" aria-controls="productFilters">
        <span class="sort-icon" aria-hidden="true">
            <img src="/assets/svg/filters.svg" alt="" aria-hidden="true" width="22" height="22">
        </span>
        Filters
        <span id="activeFilterCount" class="filter-count" hidden></span>
        <span class="filter-chevron" aria-hidden="true"></span>
    </button>

    <form id="productFilters" class="filter-dropdown" aria-labelledby="filterTitle" hidden>
        <div class="filter-dropdown-heading">
            <div>
                <h2 id="filterTitle">Filter products</h2>
            </div>
            <button id="closeFilters" class="filter-close" type="button" aria-label="Close filters">&times;</button>
        </div>

        <div class="filter-fields">
            <div class="field">
                <label for="inStock">Stock status</label>
                <div class="select-control">
                    <select id="inStock" name="in_stock">
                        <option value="">All stock statuses</option>
                        <option value="true">In stock</option>
                        <option value="false">Out of stock</option>
                    </select>
                </div>
            </div>
            <div class="filter-row">
                <div class="field">
                    <label for="minPrice">Minimum price</label>
                    <input id="minPrice" name="min_price" type="number" min="0" max="99999999.99" step="0.01" placeholder="0.00" inputmode="decimal">
                </div>
                <div class="field">
                    <label for="maxPrice">Maximum price</label>
                    <input id="maxPrice" name="max_price" type="number" min="0" max="99999999.99" step="0.01" placeholder="No limit" inputmode="decimal">
                </div>
            </div>
            <div class="field">
                <label for="availableOn">Available on or before</label>
                <input id="availableOn" name="available_on" type="text" data-date-input placeholder="dd/mm/yyyy" maxlength="10" pattern="[0-9]{2}/[0-9]{2}/[0-9]{4}">
            </div>
        </div>

        <div class="filter-actions">
            <button class="button button-secondary" id="resetFilters" type="button">Reset filters</button>
            <button class="button button-primary" type="submit">Apply filters</button>
        </div>
    </form>
</div>
