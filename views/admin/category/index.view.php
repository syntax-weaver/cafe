<?php 

/** @var array $all_categories */

?>

<h1>list categories</h1>

<?php if($message = flash('success')): ?>
    <p><?= $message ?></p>
<?php endif; ?>

<table border="1">
    <tr>
        <th>Name</th>
        <th>Action</th>
    </tr>
    <?php
    if (! empty($all_categories)) {
        foreach ($all_categories as $category): ?>
            <tr>
                <td><?= $category['name'] ?></td>
                <td>
                    <table>
                        <tr>
                            <?php $id = $category['id']; ?>
                            <td>
                                <a href="/admin/categories/edit?id=<?= $id ?>">edit</a>
                            </td>
                            <td>
                                <form action="/admin/categories/delete" method="post">
                                    <input type="hidden" name="id" value="<?= $id ?>">
                                    <input type="submit" value="delete category">
                                </form>
                            </td>
                            <!-- <td>
                                <form action="/admin/products/destroyByCategory" method="post">
                                    <input type="hidden" name="id" value="<?= $id ?>">
                                    <input type="submit" value="delete connected products">
                                </form>
                            </td> -->
                        </tr>
                    </table>
                </td>

            </tr>
        <?php endforeach; 
    } else {
        echo "<h4>no categories yet</h4>";
    }?>
</table>
<a href="/admin/categories/create">create new category</a>
<br><br>
<a href="/admin/categories/trash">trash</a>

