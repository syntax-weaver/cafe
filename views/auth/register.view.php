

<h1>register view</h1>

<form action="/register" method="post">
    <label for="name">Name`</label>
    <input type="text" id="name" name="name" value="<?= old('name') ?>">
    <label for="name"><?= error('name')[0] ?? '' ?></label>
    <br>
    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= old('email') ?>">
    <label for="email"><?= error('email')[0] ?? '' ?></label>
    <br>
    <label for="password">Password</label>
    <input type="password" id="password" name="password">
    <label for="password"><?= error('password')[0] ?? '' ?></label>
    <br>
    <label for="confirm-password">Confirm Password</label>
    <input type="password" id="confirm-password" name="confirm_password">
    <label for="confirm-password"><?= error('confirm_password')[0] ?? '' ?></label>
    <br>
    <input type="submit" value="submit">
</form>