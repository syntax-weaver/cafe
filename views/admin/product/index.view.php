<?php
/** @var ?array $all_products */
// dd($all_products[0]);
?>
<h1>all products</h1>
<p><?= flash('success') ?: '' ?></p>
<?php if($all_products): ?>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Description</th>
            <th>Image</th>
            <th>Created At</th>
            <th>Update At</th>
            <th>Manage Variants</th>
            <th>Actions</th>
        </tr>
        <?php foreach($all_products as $product): ?>
            <tr>
                <td><?= $product['id'] ?? '' ?></td>
                <td><?= $product['name'] ?? '' ?></td>
                <td><?= $product['category_name'] ?? '' ?></td>
                <td><?= $product['description'] ?? '' ?></td>
                <td><?= $product['image'] ?? '' ?></td>
                <td><?= $product['created_at'] ?? '' ?></td>
                <td><?= $product['updated_at'] ?? '' ?></td>
                <td><a href="/admin/productvariants?id=<?= $product['id'] ?>"><?= $product['name'] ?> variants</a></td>
                <td>
                    <table><tr>
                        <td><a href="/admin/products/edit?id=<?= $product['id'] ?>">edit</a></td>
                        <td>
                            <form action="/admin/products/destroy" method="post">
                            <input type="submit" value="delete">
                            <input type="hidden" name="id" value="<?= $product['id'] ?>">
                            </form>
                        </td>
                    </tr></table>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<br><br>
<a href="/admin/products/trash">see deleted products</a>
<br><br>
<a href="/admin/products/create">create new product</a>