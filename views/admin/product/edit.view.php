<?php 

/** @var ?array $product */
/** @var ?array $all_categories */
?>
<h1>edit product</h1>

<form action="/admin/products/update" method="post">
    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?= old('name', $product['name']) ?>">
    <label for="name"><?= error('name')[0] ?? '' ?></label>

    <br><br>

    <label for="description">Description</label>
    <textarea name="description" id="description"><?= old('description', $product['description']) ?></textarea>
    <label for="description"><?= error('description')[0] ?? '' ?></label>

    <br> <br>

    <label for="category">Category</label>
    <select name="category" id="category">
        <option value="">choose a category</option>
        <?php if($all_categories): ?>
            <?php foreach($all_categories as $category): ?>
                <option value="<?= $category['id'] ?>" <?= ($product['category_id'] === $category['id']) ? 'selected' : '' ?>><?= $category['name'] ?></option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
    <label for="category"><?= error('category')[0] ?? '' ?></label>

    <input type="hidden" name="id" value="<?= $product['id'] ?>">
    <br><br>
    <input type="submit" value="submit">

</form>