<?php 
/** @var array $deleted_categories */

if (! empty($deleted_categories)){ ?>
    <table>
        <tr>
            <th>Name</th>
            <th>Action</th>
        </tr>
        <?php foreach($deleted_categories as $category): ?>
            <tr>
                <td><?= $category['name'] ?></td>
                <td>
                    <form action="/admin/categories/restore" method="post">
                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                        <input type="submit" value="restore">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php } else {
    echo "<h4>no deleted categories</h4>";
}
?>
