<?php error_reporting(E_ALL) ?>
<div style="float: left; width: auto; margin-left: 10px; margin-right: 10px">
    <h3>Add new item</h3>
    <form action="add.php" method="post">
        <p>Name: <input type="text" name="name" required="required" /></p>
        <p>Description: <br /> <textarea name="description" cols="35" rows="5"></textarea></p>
        <p>Price: <input type="number" step="any" name="price" required="required" /></p>
        <input type="submit" value="Add item"/>
    </form>
</div>
<?php
$mysqli = new mysqli(
    'localhost',
    'root',
    'root',
    'store'
);

$items = $mysqli->query('SELECT * FROM items');

if ($items) { ?>
    <div style="float: left; width: auto">
        <h3>Items</h3>
        <table border="1px">
            <tr>
                <th align="center">ID</th>
                <th align="center">Name</th>
                <th align="center">Price</th>
                <th align="center">Description</th>
            </tr>
            <?php while ($row = $items->fetch_assoc()) { ?>
                <tr>
                    <td align="center"><?php echo $row['id'] ?></td>
                    <td align="center"><?php echo $row['name'] ?></td>
                    <td align="right"><?php echo $row['price'] ?></td>
                    <td align="center"><?php echo $row['description'] ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
<?php } ?>