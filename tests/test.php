<?php
require_once __DIR__ . '/../vendor/autoload.php';

$db    = new ClassDb\Db();
$query = new ClassDb\Query();
?>

<pre>
    <?php var_dump($db);?>

    <hr>

    <?php var_dump($query); ?>
</pre>