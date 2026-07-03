<?php 
/** @var array $items */
/** @var float $total */

?>

<h1>Order Summary</h1>

<?php foreach($items as $i): ?>
    <p><?= $i['variant_name'] . ' ' . $i['product_name'] . ' x ' . $i['quantity'] ?> </p>
    <p><?= $i['unit_price'] . ' x ' . $i['quantity'] . ' = ' . $i['subtotal'] ?></p>

    <p>-----------------------------</p>
<?php endforeach; ?>
<p>Total = <?= $total ?></p>
<br>

<form action="/checkout" method="post">
    <label for="payment_method">Payment Method</label>
    <select name="payment_method" id="payment_method">
        <option value="cash">Cash</option>
        <option value="card">Card</option>
    </select>
    <br>
    <label for="notes">Notes:</label><br>
    <textarea name="notes" id="notes"></textarea>

    <br><br>
    <input type="submit" value="place order">
</form>