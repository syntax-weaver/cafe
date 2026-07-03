<?php 
/** @var mixed $content */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Cafeteria' ?></title>
</head>
<body>
    <?php partial('navbar'); ?> 
    <br><hr>

    <?= $content ?>

    <br><hr>
    <?php partial('footer'); ?>
</body>
</html>

