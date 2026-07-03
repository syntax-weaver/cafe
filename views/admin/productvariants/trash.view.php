<?php
/** @var ?array $deleted_variants */
?>
<h1>deleted variants</h1>
<p><?= flash('success') ?></p>
<?php if($deleted_variants): ?>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Available Quantity</th>
            <th>Action</th>
        </tr>
        <?php foreach($deleted_variants as $v): ?>
            <tr>
                <td><?= $v['id'] ?></td>
                <td><?= $v['name'] ?></td>
                <td><?= $v['price'] ?></td>
                <td><?= $v['available_quantity'] ?></td>
                <td><form action="/admin/productvariants/restore" method="post">
                    <input type="hidden" name="variant_id" value="<?= $v['id'] ?>">
                    <input type="hidden" name="product_id" value="<?= $v['product_id'] ?>">
                    <input type="submit" value="restore">
                </form></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>