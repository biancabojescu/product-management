<form id="createProductForm">
    <input
        type="text"
        name="name"
        placeholder="Product name"
        required
    >

    <input
        type="number"
        name="price"
        min="0"
        step="0.01"
        placeholder="Price"
        required
    >

    <textarea
        name="description"
        placeholder="Description"
    ></textarea>

    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">

    <input type="date" name="availability_date">

    <input type="number" name="stock" min="0" value="0">

    <button type="submit">Create Product</button>
</form>

<script src="/assets/js/products.js"></script>