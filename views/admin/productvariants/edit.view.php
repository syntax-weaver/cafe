<?php
/** @var ?array $variant */
// /** @var ?array $product */

?>
<h1>edit variant</h1>

<form action="/admin/productvariants/update" method="post">
    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?= old('name', $variant['name']) ?>">
    <label for="name"><?= error('name')[0]?? '' ?></label>
    <br><br>
    <label for="price">Price</label>
    <input type="floatval" id="price" name="price" value="<?= old('price', $variant['price']) ?>">
    <label for="price"><?= error('price')[0]?? '' ?></label>
    <br><br>
    <label for="q">Available Quantity</label>
    <input type="number" id="q" name="available_quantity" value="<?= old('available_quantity', $variant['available_quantity']) ?>">
    <label for="q"><?= error('available_quantity')[0]?? '' ?></label>

    <input type="hidden" name="product_id" value="<?= $variant['product_id'] ?>">
    <input type="hidden" name="variant_id" value="<?= $variant['id'] ?>">
    <br><br>
    <input type="submit" value="submit">
    
</form>