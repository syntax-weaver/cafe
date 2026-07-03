<h1>create new category</h1>
<form action="/admin/categories" method="post">
    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?= old('name') ?>">
    <label for="name"><?= error('name')[0] ?? '' ?></label>
    <br><br>
    <input type="submit" value="submit">
</form>