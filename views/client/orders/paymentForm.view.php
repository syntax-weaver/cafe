<?php 
/** @var int $order_id */
/** @var float $total_price */

?>

<h1>payment form</h1>
<form action="/orders/pay" method="post">
    <p>Required: $<?= $total_price ?></p>
    
    <br>
    <input type="hidden" name="order_id" value="<?= $order_id ?>">
    <input type="submit" value="complete payment">
</form>