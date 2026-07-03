
<?php
/** @var ?int $product_id */

?>
<h1>create new variant</h1>
<p><?= flash('success') ?></p>
<form action="/admin/productvariants" method="post">
    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?= old('name') ?>">
    <label for="name"><?= error('name')[0]?? '' ?></label>
    <br><br>
    <label for="price">Price</label>
    <input type="floatval" id="price" name="price" value="<?= old('price') ?>">
    <label for="price"><?= error('price')[0]?? '' ?></label>
    <br><br>
    <label for="q">Available Quantity</label>
    <input type="number" id="q" name="available_quantity" value="<?= old('quantity') ?>">
    <label for="q"><?= error('available_quantity')[0]?? '' ?></label>

    <input type="hidden" name="product_id" value="<?= $product_id ?>">
    <br><br>
    <input type="submit" value="submit">
    
</form>