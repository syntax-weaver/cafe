<?php 
/** @var array $orders */
// dd($orders);
?>

<h1>orders history</h1>
<br>
<?= flash('errors') ?>
<?= flash('success') ?>

<a href="/products">products</a>


<?php if(empty($orders)): ?>
    <p>you don't have any orders yet. browse our <a href="/products">products</a></p>
<?php else: ?>
    <?php foreach($orders as $o): ?>
        <p>
            <a href="/orders/show?id=<?= $o['id'] ?>">see order details</a>
            <?php if($o['status'] !== 'cancelled' && $o['payment_status'] !== 'paid' && $o['payment_method'] !== 'cash'): ?>
                <a href="/orders/pay?id=<?= $o['id'] ?>">pay for this order</a>
            <?php endif; ?>
        </p>
        <p>Created At: <?= $o['created_at'] ?></p>
        <p>Updated At: <?= $o['updated_at'] ?></p>
        <p>Total Price: <?= $o['total_price'] ?></p>
        <p>Order Status: <?= $o['status'] ?></p>
        <p>Notes: <?= $o['notes'] ?></p>
        <p>Payment Status: <?= $o['payment_status'] ?></p>
        <p>Payment method: <?= $o['payment_method'] ?></p>
        <hr>
    <?php endforeach; ?>
<?php endif; ?>