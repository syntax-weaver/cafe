<?php 
/** @var array $items */
/** @var float $total */
// dd($items);
?>

<h1>cart details</h1>
<?= flash('errors') ?>
<?= flash('success') ?>
<?php if(empty($items)): ?>
    <p>Your cart is empty. Browse our <a href="/products">products</a></p>
<?php else: ?>
    <a href="/checkout">checkout</a>
    
    <p><?= flash('error') ?></p>
    <p><?= flash('success') ?></p>
    
    <?php foreach($items as $i): ?>
        <p><?= $i['variant_name'] ?> (<?= $i['product_name'] ?>)</p>
        <p>
            <form action="/cart/decrement" method="post">
                <input type="hidden" name="id" value="<?= $i['cart_item_id'] ?>">
                <input type="submit" value="decrement">
            </form>
            <?= $i['quantity'] ?>
            <!-- <a href="/cart/increment?id=<?= $i['cart_item_id'] ?>">  add one</a>  -->
            <form action="/cart/increment" method="post">
                <input type="hidden" name="id" value="<?= $i['cart_item_id'] ?>">
                <input type="submit" value="increment">
            </form>
            [ x $<?= $i['unit_price'] ?> ]<?= $i['is_available'] ? '' : ' - not available' ?>
        </p>
        <form action="/cart/remove" method="post">
            <input type="hidden" name="id" value="<?= $i['cart_item_id'] ?>">
            <input type="submit" value="remove">
        </form>
        <hr>
    <?php endforeach; ?>
    <p>Total Price: $<?= $total ?></p>
<?php endif; ?>