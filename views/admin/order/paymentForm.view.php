<?php 
/** @var int $order_id */
/** @var float $total_price */
?>

<h1>admin payment form</h1>
<p>Required: <?= $total_price ?></p>
<form action="/admin/orders/pay" method="post">
    <input type="hidden" name="id" value="<?= $order_id ?>">
    <input type="submit" value="complete payment">
</form>