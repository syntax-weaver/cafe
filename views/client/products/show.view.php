<?php 
/** @var array $product */
/** @var array $variants */

?>

<h1>show product and its variants</h1>

<br>
<a href="/cart">cart</a>
<p><?= flash('success') ?></p>
<p><?= flash('errors') ?></p>
<h4><?= $product['name'] ?? '' ?></h4>
<p><?= $product['description'] ?? '' ?></p>

<?php foreach($variants as $v): ?>
    <h4><?= $v['name'] ?? '' ?></h4>
    <p>$ <?= $v['price'] ?? '' ?></p>
    <?php if($v['available_quantity'] > 0): ?>
        <form action="/cart/add" method="post">
            <input type="hidden" name="id" value="<?= $v['id'] ?>">
            <input type="submit" value="add to cart">
        </form>
    <?php else: ?>
        <p>out of stock currently</p>
    <?php endif; ?>
    <hr>
<?php endforeach; ?>
