<?php
error_reporting(E_ALL);

include 'shared.php';

const DEFAULT_SORTING_FIELD = 'id';
const DEFAULT_SORTING_DIRECTION = 'asc';
const DEFAULT_PAGE_INDEX = 0;
const DEFAULT_ITEMS_PER_PAGE = 25;

function resolve_sorting(&$sort_by = DEFAULT_SORTING_FIELD, &$sort_dir = DEFAULT_SORTING_DIRECTION)
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

function resolve_pagination(&$page, &$items_per_page)
{
    if (!empty($_GET['page'])) {
        $page = (int) $_GET['page'];
        if ($page < 0) {
            $page = DEFAULT_PAGE_INDEX;
        }
    } else {
        $page = DEFAULT_PAGE_INDEX;
    }

    if (!empty($_GET['ipp'])) {
        $items_per_page = (int) $_GET['ipp'];
        if ($items_per_page < 1) {
            $items_per_page = DEFAULT_ITEMS_PER_PAGE;
        }
    } else {
        $items_per_page = DEFAULT_ITEMS_PER_PAGE;
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
?>
<?php include 'add-item-form.html'; ?>
<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

resolve_sorting($sort_by, $sort_dir);
resolve_pagination($page, $items_per_page);

$total_items = get_total_items();
$total_pages = intdiv($total_items, $items_per_page); // Here we stay 0-based to calculate start item index correctly.

if ($page > $total_pages) {
    $page = DEFAULT_PAGE_INDEX;
}

$from = $page * $items_per_page;
$to = $from + $items_per_page; // Currently not used, but may be useful later for cache.

if ($to > $total_items) {
    $to = $total_items;
}

$items_to_get = get_items_ids($sort_by, $sort_dir, $items_per_page, $from);
$items = get_items_by_id($items_to_get);

if ($items) { ?>
    <div style="float: left; width: 50%; margin-bottom: 10px">
    <?php
    $user_page = $page + 1;
    $user_total_pages = $total_pages + 1;
    echo "<h3>Found $total_items item(s). Showing page $user_page of $user_total_pages.</h3>"
    ?>
        <form action="items.php">
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
                , items per page:
                <select name="ipp">
                    <?php
                        $allowed = array(25, 50, 100);
                        foreach ($allowed as $val) {
                            $option = "<option value='$val'";
                            if ($items_per_page == $val) {
                                $option .= ' selected';
                            }
                            $option .= ">$val</option>";
                            echo $option . PHP_EOL;
                        }
                    ?>
                </select>
                <input type="submit" value="Apply">
            </p>
            <?php echo "<input type='hidden' name='page' value='$page'>"?>
        </form>
        <table border="1px">
            <tr>
                <th align="center">ID</th>
                <th align="center">Picture</th>
                <th align="center">Name</th>
                <th align="center">Price</th>
                <th align="center">Description</th>
            </tr>
            <?php foreach ($items as $row) {
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
