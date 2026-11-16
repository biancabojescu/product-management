<div id="sortMenu" class="sort-menu">
    <button id="toggleSort" class="button button-secondary sort-toggle" type="button" aria-expanded="false" aria-controls="productSort">
        <span class="sort-icon" aria-hidden="true">
            <img src="/assets/svg/sort.svg" alt="" aria-hidden="true" width="24" height="24">
        </span>
        Sort by
        <span class="sort-chevron" aria-hidden="true"></span>
    </button>

    <div id="productSort" class="sort-dropdown" hidden>
        <fieldset class="sort-options">
            <legend>Sort products</legend>
            <label class="sort-option">
                <input type="radio" name="product_sort" value="created_at:desc" checked>
                <span>Newest first</span>
            </label>
            <label class="sort-option">
                <input type="radio" name="product_sort" value="created_at:asc">
                <span>Oldest first</span>
            </label>
            <label class="sort-option">
                <input type="radio" name="product_sort" value="name:asc">
                <span>Name: A to Z</span>
            </label>
            <label class="sort-option">
                <input type="radio" name="product_sort" value="name:desc">
                <span>Name: Z to A</span>
            </label>
            <label class="sort-option">
                <input type="radio" name="product_sort" value="price:asc">
                <span>Price: low to high</span>
            </label>
            <label class="sort-option">
                <input type="radio" name="product_sort" value="price:desc">
                <span>Price: high to low</span>
            </label>
        </fieldset>
    </div>
</div>
