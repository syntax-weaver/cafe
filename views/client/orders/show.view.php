<?php 
/** @var array $items */
// dd($items);
?>

<h1>order details</h1>
<a href="/orders">all orders</a>
<?= flash('errors') ?>
<?= flash('success') ?>
<?php foreach($items as $i): ?>
    <p>Name: <?= $i['product_name'] ?> ( <?= $i['category_name'] ?> ) - <?= $i['variant_name'] ?></p>
    <p>Number of Units Ordered: 
        <?php if($i['order_status'] === 'pending'): ?>
            <form action="/orders/show/increment" method="post">
                <input type="hidden" name="item_id" value="<?= $i['item_id'] ?>">
                <input type="hidden" name="order_id" value="<?= $i['order_id'] ?>">
                <input type="submit" value="increment">
            </form>
        <?php endif; ?>
        <?= $i['quantity'] ?>
        <?php if($i['order_status'] === 'pending'): ?>
            <form action="/orders/show/decrement" method="post">
                <input type="hidden" name="item_id" value="<?= $i['item_id'] ?>">
                <input type="hidden" name="order_id" value="<?= $i['order_id'] ?>">
                <input type="submit" value="decrement">
            </form>
        <?php endif; ?>
    </p>
    <p>Unit Price: $<?= $i['unit_price'] ?></p>
    <?php if($i['order_status'] === 'pending'): ?>
        <form action="/orders/show/remove" method="post">
            <input type="hidden" name="item_id" value="<?= $i['item_id'] ?>">
                <input type="hidden" name="order_id" value="<?= $i['order_id'] ?>">
                <input type="submit" value="remove item">
        </form>
    <?php endif; ?>
    <hr>

<?php endforeach; ?>
<?php if($items[0]['order_status'] === 'pending'): ?>
    <form action="/orders/show/notes" method="post">
        <input type="hidden" name="item_id" value="<?= $items[0]['item_id'] ?>">
        <input type="hidden" name="order_id" value="<?= $items[0]['order_id'] ?>">
        <textarea name="notes" ><?= $items[0]['order_notes'] ?></textarea>
        <br>
        <input type="submit" value="update order notes">
    </form>
<?php endif; ?>