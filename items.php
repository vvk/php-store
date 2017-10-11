<html>
    <head>
        <title>PHP Store Items</title>
    </head>
<body>
<?php
error_reporting(E_ALL);

include 'shared.php';
include_once 'constants.php';

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

function build_items_query($sort_by = DEFAULT_SORTING_FIELD, $sort_dir = DEFAULT_SORTING_DIRECTION) {
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

function pagination($page, $total_pages) {
    global $sort_by;
    global $sort_dir;
    global $items_per_page;

    $params = array(
            'sort_by' => $sort_by,
            'sort_dir' => $sort_dir,
            'ipp' => $items_per_page
    );
    $url_params = http_build_query($params);

    $pagination_markup = 'Pages:';

    // First page
    if ($page == 0) {
        $pagination_markup .= ' 1';
    } else {
        $pagination_markup .= " <a href='items.php?${url_params}&page=0'>1</a> ";
    }

    // Not shown pages
    if ($page > 2) {
        $pagination_markup .= ' ...';
    }

    // Previous from current
    if ($page > 1) {
        $pagination_markup .= " <a href='items.php?${url_params}&page=".($page - 1)."'>$page</a>";
    }

    // Current pages with no link
    if ($page != 0) { // We have already drown it.
        $pagination_markup .= " ".($page + 1);
    }

    // Next from current
    if ($page != $total_pages) {
        $next_page = $page + 1;
        $next_page_markup = $next_page + 1;
        $pagination_markup .= " <a href='items.php?${url_params}&page=$next_page'>$next_page_markup</a>";
    }

    // Not shown pages
    if ($total_pages - $page > 2) {
        $pagination_markup .= ' ...';
    }

    // Last page
    if ($page < $total_pages - 1) {
        $last_page_markup = $total_pages + 1;
        $pagination_markup .= " <a href='items.php?${url_params}&page=$total_pages'>$last_page_markup</a>";
    }

    // Buttons
    if ($total_pages != 0) {
        $pagination_markup .= "<form action='items.php' style='display: inline;'>";
        if ($page != 0) {
            $prev_page = $page - 1;
            $pagination_markup .= "  <button type='submit' name='page' value='$prev_page'>&lt; Previous</button>";
        }

        if ($page != $total_pages) {
            $next_page = $page + 1;
            $pagination_markup .= "  <button type='submit' name='page' value='$next_page'>Next &gt;</button>";
        }

        foreach ($params as $key => $value) {
            $pagination_markup .= "<input type='hidden' name='$key' value='$value'>";
        }

        $pagination_markup .= "</form>";
    }

    echo $pagination_markup;
}
?>
<?php include 'add-item-form.html'; ?>
<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

resolve_sorting($sort_by, $sort_dir);
resolve_pagination($page, $items_per_page);

$total_items = get_total_items();
$total_pages = floor($total_items / $items_per_page); // Here we stay 0-based to calculate start item index correctly.

if ($page > $total_pages) {
    $page = DEFAULT_PAGE_INDEX;
}

$from = $page * $items_per_page;
$to = $from + $items_per_page; // Currently not used, but may be useful later for cache.

if ($to > $total_items) {
    $to = $total_items;
}
$ids = get_items_ids_sorted($items_per_page, $from, $sort_by, $sort_dir);
$items = get_items($ids);

if ($items) { ?>
    <div style="float: left; width: 50%; margin-bottom: 10px">
        <h3>Items list.</h3>
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
        </form>
        <div style="float: left">
            <?php echo "Total items: $total_items." ?>
        </div>
        <div style='float: right'>
            <?php pagination($page, $total_pages) ?>
        </div>
        <table border="1px" style="width: 100%;">
            <tr>
                <th align="center">ID</th>
                <th align="center">Image</th>
                <th align="center">Name</th>
                <th align="center">Price</th>
                <th align="center">Description</th>
            </tr>
            <?php foreach ($items as $row) {
                $id = htmlspecialchars($row['id']);
                $id_link = "<a href='item.php?id=$id'>$id</a>"
                ?>
                <tr>
                    <td align="center"><?php echo $id_link ?></td>
                    <td align="center">
                        <?php if (!empty($row['image'])) {
                            $image = htmlspecialchars($row['image']);
                            echo "<img src='http://$_SERVER[HTTP_HOST]/images/t_$image' style='max-width: 100%; max-height: 100%; display: block;' >";
                        } else {
                            echo 'No image';
                        }?>
                    </td>
                    <td align="center"><?php echo htmlspecialchars($row['name']) ?></td>
                    <td align="right"><?php echo htmlspecialchars(number_format($row['price'], 2)) ?></td>
                    <td align="center"><?php echo htmlspecialchars($row['description']) ?></td>
                </tr>
            <?php } ?>
        </table>
        <div style="float: left">
            <?php echo "Total items: $total_items." ?>
        </div>
        <div style='float: right'>
            <?php pagination($page, $total_pages) ?>
        </div>
    </div>
<?php } ?>
    </body>
</html>