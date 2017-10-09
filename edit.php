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

?>
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
<div class="item">
    <?php
    $safe_id = htmlspecialchars($item['id']);
    $safe_name = htmlspecialchars($item['name']);
    $safe_description = htmlspecialchars($item['description']);
    $safe_price = htmlspecialchars($item['price']);
    echo "<h3>Edit: <a href='item.php?id=$safe_id'>$safe_name</a></h3>"?>
    <form id="edit-item">
        <table border="1px">
            <tr><th>Parameter</th><th>Value</th></tr>
            <tr><td class="name">Name</td><td class="value"><input type="text" name="name" required = "true"value="<?php echo $safe_name ?>"></td></tr>
            <tr><td class="name">Description</td><td class="value"><input width="400" type="text" name="description" value="<?php echo $safe_description ?>"></td></tr>
            <tr><td class="name">Price</td><td class="value"><input type="number" name="price" step="any" max="9999999999.99" required = "true" value="<?php echo $safe_price ?>"></td></tr>
            <tr><td class="name">Picture</td><td class="value"><input type="file" name="image" value="<?php echo $safe_name ?>"></td></tr>
        </table>
        <input type="hidden" name="id" value="<?php echo $safe_id ?>" />
    </form>
    <button form="edit-item" formaction="update.php" formmethod="post">Update</button>
    <form action="item.php" style="display: inline">
        <input type="hidden" name="id" value="<?php echo $safe_id ?>" />
        <input type="submit" value="Cancel"/>
    </form>
</div>
