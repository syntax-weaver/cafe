<?php 
/** @var ?array $deleted_products */
// dd($deleted_products);
?>

<h1>trash</h1>
<?= flash('error')?: '' ?>
<?= flash('success')?: '' ?>
<?php if($deleted_products): ?>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Description</th>
            <th>Image</th>
            <th>Created At</th>
            <th>Update At</th>
            <th>Actions</th>
        </tr>
        <?php foreach($deleted_products as $product): ?>
            
            <tr>
                <td><?= $product['id'] ?? '' ?></td>
                <td><?= $product['name'] ?? '' ?></td>
                <td><?= $product['category_name'] ?? '' ?></td>
                <td><?= $product['description'] ?? '' ?></td>
                <td><?= $product['image'] ?? '' ?></td>
                <td><?= $product['created_at'] ?? '' ?></td>
                <td><?= $product['updated_at'] ?? '' ?></td>
                <td>
                    <form action="/admin/products/restore" method="post">
                        <input type="submit" value="restore">
                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>