<?php 
/** @var array $orders */

?>

<h1>order list - admin view</h1>
<br>
<?= flash('errors') ?>
<?= flash('success') ?>

<form action="/admin/orders" method="get">
    <select name="filter">
        <option value="">All Orders</option>
        <option value="pending">Pending</option>
        <option value="preparing">Preparing</option>
        <option value="delivered">Delivered</option>
        <option value="cancelled">Cancelled</option>
    </select>
    <input type="submit" value="filter">
</form>
<?php if(empty($orders)): ?>
    <p>no orders</p>
<?php else: ?>
    <?php foreach($orders as $o): ?>
        <p>Order Id : <?= $o['id'] ?? '' ?></p>
        <p>Customer Name : <?= $o['customer_name'] ?? '' ?></p>
        <p>Order Status : <?= $o['status'] ?? '' ?></p>
        <p>Total Price : <?= $o['total_price'] ?? '' ?></p>
        <p>Payment Status: <?= $o['payment_status'] ?? '' ?></p>
        <p>Payment Method: <?= $o['payment_method'] ?? '' ?></p>
        <p>Created At : <?= $o['created_at'] ?? '' ?></p>
        <?php if($o['payment_method'] === 'card'): ?>
            <?php if($o['status'] !== 'cancelled' && $o['payment_status'] !== 'paid'): ?>
                <p><a href="/admin/orders/pay?id=<?= $o['id'] ?>">pay for this order online</a></p>
            <?php endif; ?>
        <?php elseif($o['status'] === 'delivered' && $o['payment_status'] === 'pending'): ?>
            <p>
                <form action="/admin/orders/updatePaymentStatus" method="post">
                    <input type="hidden" name="id" value="<?= $o['id'] ?>">
                    <input type="submit" value="mark cash received">
                </form>
            </p>
        <?php endif; ?>
        
        <a href="/admin/orders/show?id=<?= $o['id'] ?>">View Details</a>
        <hr>
    <?php endforeach; ?>
<?php endif; ?>