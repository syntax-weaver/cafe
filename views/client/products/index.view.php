<?php 
/** @var array $products */

?>

<h1>list available products</h1>
<a href="/cart">cart</a>
<a href="/orders">my orders</a>
<?= flash('errors') ?>
<?= flash('success') ?>
<?php if (empty($products)): ?>
    <p>no available products currently</p>
<?php else: ?>
    <?php foreach($products as $p): ?>
        <div>
            <a href="/products/show?id=<?= $p['id'] ?>"><h4>Name: <?= $p['name'] ?></h4></a>
            <p>Description: <?= $p['short_description'] ?></p>
            <p>Category: <?= $p['category_name'] ?></p>
            <p>Lowest Price: <?= $p['lowest_price'] ?></p>
            <p>Status: <?= $p['is_out_of_stock'] ? 'restocking..., order later' : 'available' ?></p>
        </div>
        <hr>
    <?php endforeach; ?>
<?php endif; ?>