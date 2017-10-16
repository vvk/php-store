<?php

include_once 'shared.php';
include_once 'markups.php';

resolve_sorting($sorting_field, $sorting_direction);
resolve_pagination($items_per_page, $page, $total_pages);

$pagination_markup = pagination_markup($items_per_page, $page, $total_pages, $sorting_field, $sorting_direction); // Used twice in the page, so cache it.
$items = page_items($page, $items_per_page, $sorting_field, $sorting_direction);
$total_items = get_total_items();
?>
<html>
    <head>
        <title>PHP Store Items</title>
    </head>
    <body>
        <?php include 'add-item-form.html' ?>
        <h2 style="margin-left: 10px; margin-top: 10px"><a href="items.php">PHP Store</a></h2>
        <div style="float: left; width: 50%; margin-bottom: 10px">
            <h3>Items list.</h3>
            <form action="items.php">
                <p>Sort by
                    <select name="sort_by">
                        <?php echo build_sorting_field_selector_markup($sorting_field) ?>
                    </select>
                    , direction:
                    <select name="sort_dir">
                        <?php echo build_sorting_direction_selector_markup($sorting_direction) ?>
                    </select>
                    , items per page:
                    <select name="ipp">
                        <?php echo build_items_per_page_selector_markup($items_per_page) ?>
                    </select>
                    <input type="submit" value="Apply">
                </p>
            </form>
            <div style="float: left">
                <?php echo "Total items: $total_items." ?>
            </div>
            <div style='float: right'>
                <?php echo $pagination_markup ?>
            </div>
            <table border="1px" style="width: 100%;">
                <tr>
                    <th align="center">ID</th>
                    <th align="center">Image</th>
                    <th align="center">Name</th>
                    <th align="center">Price</th>
                    <th align="center">Description</th>
                </tr>
                <?php
                    foreach ($items as $item) {
                        echo build_item_markup($item), PHP_EOL;
                    }
                ?>
            </table>
            <div style="float: left">
                <?php echo "Total items: $total_items." ?>
            </div>
            <div style='float: right'>
                <?php echo $pagination_markup ?>
            </div>
        </div>
    </body>
</html>
