<?php 
/** @var array $items */
$status = $items[0]['status'];
?> 
<h1>order details</h1>
<p><?= flash('success') ?></p>
<p><?= flash('errors') ?></p>
<?php if(empty($items)): ?>
    <p>order is empty</p>
<?php else: ?>
    <p>Customer Name : <?= $items[0]['customer_name'] ?? '' ?></p>
    <p>Customer Email : <?= $items[0]['customer_email'] ?? '' ?></p>
    <p>
        Status : <?= $items[0]['status'] ?>
        <?php if($status !== 'cancelled'): ?>
        <form action="/admin/orders/update" method="post">
            <?php if($status === 'pending'): ?>
                <select name="new_status" >
                    <option value="">select new status</option>
                    <option value="preparing">start preparing</option>
                    <option value="cancelled">cancel order</option>
                </select>
            <?php elseif($status === 'preparing'): ?>
                <select name="new_status" >
                    <option value="">select new status</option>
                    <option value="delivered">mark delivered</option>
                </select>
            <?php endif; ?>
            <input type="hidden" name="order_id" value="<?= $items[0]['order_id'] ?>">
            <input type="submit" value="update status">
        </form>
        <?php endif; ?>
    </p>
    <p>Total Price : <?= $items[0]['total_price'] ?? '' ?></p>
    <p>Created At : <?= $items[0]['created_at'] ?? '' ?></p>
    <p>UPdated At : <?= $items[0]['updated_at'] ?? '' ?></p>
    <p>Payment Status : <?= $items[0]['payment_status'] ?></p>
    <hr>
    <?php foreach($items as $i): ?>
        <p>Item Id : <?= $i['item_id'] ?? '' ?></p>
        <p>Product : <?= $i['product_name'] ?? '' ?></p>
        <p>Variant : <?= $i['variant_name'] ?? '' ?></p>
        <p>Quantity : <?= $i['quantity'] ?? '' ?></p>
        <p>Unit Price : $<?= $i['unit_price'] ?? '' ?></p>
        <p>----------------------------</p>
        <p>SubTotal : $<?= $i['sub_total'] ?? '' ?></p>
        <p>*******************************************</p>
    <?php endforeach; ?>
<?php endif; ?>