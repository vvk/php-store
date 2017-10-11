<?php

if (empty($_GET['id'])) {
    header('Location: items.php');
    return;
}

include 'shared.php';

$id = $_GET['id'];
$item = get_item($id);

if (!$item) {
    header('Location: 404.php');
    return;
}

$safe_id = htmlspecialchars($item['id']);
$safe_name = htmlspecialchars($item['name']);
$safe_description = htmlspecialchars($item['description']);
$safe_price = htmlspecialchars($item['price']);

?>
<html>
<head>
    <title>Editing <?php echo $safe_name ?></title>
<style>
    table {
        width: 100%;
        margin: 0 auto;
    }

    td.value {
        text-align: left;
        padding: 5px;
    }

    td.name {
        text-align: left;
        padding: 5px;
    }

    .item {
        width: 40%;
        text-align: center;
        margin: auto;
    }

    button {
        margin: 5px;
    }
</style>

</head>
<body>
<div class="item">
    <?php
    echo "<h3>Edit: <a href='item.php?id=$safe_id'>$safe_name</a></h3>"?>
    <form action="update.php" method="post" enctype="multipart/form-data">
        <table border="1px">
            <tr><th>Parameter</th><th>Value</th></tr>
            <tr><td class="name">Name</td><td class="value"><input type="text" name="name" placeholder="Maximum is 200 symbols." maxlength="200" required = "true"value="<?php echo $safe_name ?>"></td></tr>
            <tr><td class="name">Description</td><td class="value"><textarea name="description" cols="35" rows="5" placeholder="Maximum is 1000 symbols." maxlength="1000"><?php echo $safe_description ?></textarea></td></tr>
            <tr><td class="name">Price</td><td class="value"><input type="number" name="price" min="0.01" max="1000000000" step="any" required = "true" value="<?php echo $safe_price ?>"></td></tr>
            <tr><td class="name">Image (max. size is 2 MB)</td><td class="value"><input type="file" name="image" accept="image/jpeg,image/png,image/gif"/></td></tr>
        </table>
        <input type="hidden" name="id" value="<?php echo $safe_id ?>" />
        <input type="hidden" name="action" value="update" />
        <input type="submit" value="Update">
    </form>
    <form action="item.php" style="display: inline">
        <input type="hidden" name="id" value="<?php echo $safe_id ?>" />
        <input type="submit" value="Cancel"/>
    </form>
</div>
</body>
</html>