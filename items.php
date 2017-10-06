<?php error_reporting(E_ALL) ?>
<div style="float: left; width: auto; margin-left: 10px; margin-right: 10px">
    <h3>Add new item</h3>
    <form action="add.php" method="post" enctype="multipart/form-data">
        <p>Name: <input type="text" name="name" required="required" /></p>
        <p>Description: <br /> <textarea name="description" cols="35" rows="5"></textarea></p>
        <p>Picture: <input type="file" name="picture" accept="image/jpeg,image/png,image/gif"/></p>
        <p>Price: <input type="number" step="any" name="price" required="required" /></p>
        <input type="submit" value="Add item"/>
    </form>
</div>
<?php

const DEFAULT_SORTING_FIELD = 'id';
const DEFAULT_SORTING_DIRECTION = 'asc';

function resolve_sorting(&$sort_by, &$sort_dir)
{
    if (!empty($_GET['sort_by'])) {
        $sort_by = strtolower($_GET['sort_by']);
    }

    if (!empty($_GET['sort_dir'])) {
        $sort_dir = strtolower($_GET['sort_dir']);
    }

    if ($sort_by != 'id' && $sort_by != 'price') {
        $sort_by = DEFAULT_SORTING_FIELD;
    }

    if ($sort_dir != 'asc' && $sort_dir != 'desc') {
        $sort_dir = DEFAULT_SORTING_DIRECTION;
    }
}

function build_items_query($sort_by = 'id', $sort_dir = 'asc') {
    $query = 'SELECT * FROM items';

    if ($sort_by == 'id') {
        // Unfortunately here we have to specify ORDER BY
        // explicitly even though ID is a primary key, because
        // the sorting may be requested as descending.
        // Similar story with sorting direction:
        // index MAY be reversed (but MySQL just ignores it currently),
        // so need to specify ASC explicitly as well.
        $query .= ' ORDER BY id ' . strtoupper($sort_dir);
    } else if ($sort_by == 'price') {
        $query .= ' ORDER BY price ' . strtoupper($sort_dir);
    }

    return $query;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mysqli = new mysqli(
    'localhost',
    'root',
    'root',
    'store'
);

resolve_sorting($sort_by, $sort_dir);
$items = $mysqli->query(build_items_query($sort_by, $sort_dir));

if ($items) { ?>
    <div style="float: left; width: 50%; margin-bottom: 10px">
        <h3>Items</h3>
        <form action="#">
            <p>Sort by
                <select name="sort_by">
                    <option value="id" <?php if ($sort_by == 'id') echo 'selected' ?>>ID</option>
                    <option value="price" <?php if ($sort_by == 'price') echo 'selected' ?>>Price</option>
                </select>
                , direction:
                <select name="sort_dir">
                    <option value="asc" <?php if ($sort_dir == 'asc') echo 'selected' ?>>Ascending</option>
                    <option value="desc" <?php if ($sort_dir == 'desc') echo 'selected' ?>>Descending</option>
                </select>
                <input type="submit" value="Apply">
            </p>
        </form>
        <table border="1px">
            <tr>
                <th align="center">ID</th>
                <th align="center">Picture</th>
                <th align="center">Name</th>
                <th align="center">Price</th>
                <th align="center">Description</th>
            </tr>
            <?php while ($row = $items->fetch_assoc()) {
                $id = htmlspecialchars($row['id']);
                $id_link = "<a href=\"item.php?id=$id\">$id</a>"
                ?>
                <tr>
                    <td align="center"><?php echo $id_link ?></td>
                    <td align="center">
                        <?php if (!empty($row['image_url'])) {
                            echo "<img src='http://$_SERVER[HTTP_HOST]/$row[image_url]' style='max-width: 100%; max-height: 100%; display: block;' >";
                        } else {
                            echo 'No picture';
                        }?>
                    </td>
                    <td align="center"><?php echo $row['name'] ?></td>
                    <td align="right"><?php echo $row['price'] ?></td>
                    <td align="center"><?php echo $row['description'] ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
<?php } ?>