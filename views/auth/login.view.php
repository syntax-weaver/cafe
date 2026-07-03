<?php 

echo 'login view';
// dd(flash('errors'));
// dd(error('email'));
?>

    <form action="/login" method="post">
        <input type="email" id="email" name="email" value="<?= old('email') ?>">
        <label for="email"><?= error('email')[0] ?? '' ?></label>

        <input type="password" id="password" name="password">
        <label for="password"><?= error('password')[0] ?? '' ?></label>
        <input type="submit" value="submit">
    </form>

