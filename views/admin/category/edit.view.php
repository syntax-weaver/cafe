<?php

/** @var array $category */
// dd($category);
?>
<h1>Edit Category <?= $category['name'] ?> </h1>
<form action="/admin/categories/update" method="post">
    <label for="name">New Name</label>
    <input type="text" id="name" name="name" value="<?= old('name', $category['name']) ?>">
    <label for="name"><?= error('name')[0] ?? '' ?></label>
    <input type="hidden" id="id" name="id" value="<?= $category['id'] ?>">
    <br><br>
    <input type="submit" value="update category">
</form>
