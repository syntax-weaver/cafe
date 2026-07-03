<?php 

/** @var ?array $variants */
/** @var ?int $product_id */
// dd($variants);
?>

<h1><?= isset($variants[0]['product_name']) ? $variants[0]['product_name'] : 'no ' ?> variants</h1>
<p><?= flash('success') ?></p>
<br>
<a href="/admin/productvariants/create?id=<?= $product_id ?>">add new variant</a>
<br><br>
<?php if($variants): ?>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Available Quantity</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th>Actions</th>
        </tr>

        <?php foreach($variants as $v): ?>
            <tr>
                <td><?= $v['id'] ?></td>
                <td><?= $v['name'] ?></td>
                <td><?= $v['price'] ?></td>
                <td><?= $v['available_quantity'] ?></td>
                <td><?= $v['created_at'] ?></td>
                <td><?= $v['updated_at'] ?></td>
                <td>
                    <table><tr>
                        <!-- <td><a href="/admin/productvariatns/edit?id=<?= $product_id ?>">edit</a></td> -->
                        <td><form action="/admin/productvariants/edit" method="GET">
                            <input type="hidden" name="product_id" value="<?= $product_id ?>">
                            <input type="hidden" name="variant_id" value="<?= $v['id'] ?>">
                            <input type="submit" value="edit">
                        </form></td>
                        <td><form action="/admin/productvariants/destroy" method="post">
                            <input type="submit" value="delete">
                            <input type="hidden" name="id" value="<?= $v['id'] ?>">
                        </form></td>
                    </tr></table>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<br>
<a href="/admin/productvariants/trash?id=<?= $product_id ?>">see deleted variants</a>