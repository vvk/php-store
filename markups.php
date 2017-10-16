<?php

function build_items_per_page_selector_markup($ipp = 25) {
    $markup = "";
    $allowed = array(25, 50, 100);
    foreach ($allowed as $val) {
        $markup .= "<option value='$val' " . ($ipp == $val ? "selected" : "") . ">$val</option>" . PHP_EOL;
    }

    return $markup;
}

function build_sorting_field_selector_markup($sorting_field = DEFAULT_SORTING_FIELD) {
    $markup = "";
    $supported_sortings = array('id', 'price');

    foreach ($supported_sortings as $option) {
        $markup .= "<option value='$option'";
        if ($option == $sorting_field) {
            $markup .= " selected";
        }
        $markup .= ">" . ucfirst($option) . "</option>" . PHP_EOL;
    }

    return $markup;
}

function build_sorting_direction_selector_markup($sorting_direction = DEFAULT_SORTING_DIRECTION) {
    $markup = "";
    $supported_sortings = array('asc', 'desc');

    foreach ($supported_sortings as $option) {
        $markup .= "<option value='$option'";
        if ($option == $sorting_direction) {
            $markup .= " selected";
        }
        $markup .= ">" . ucfirst($option) . "</option>" . PHP_EOL;
    }

    return $markup;
}

function build_item_markup($item) {
    $id = htmlentities($item['id']);
    $name = htmlentities($item['name']);
    $price = htmlentities(number_format($item['price'], 2));
    $description = nl2br(htmlentities($item['description']));

    $markup = "<tr>";
    $markup .= "<td align='center'><a href='item.php?id=$id'>$id</a></td>";
    if (!empty($item['image'])) {
        $image_name = htmlspecialchars($item['image']);
        $image_markup = "<img src='http://$_SERVER[HTTP_HOST]/images/t_$image_name' style='max-width: 100%; max-height: 100%; display: block;' >";
    } else {
        $image_markup = "<img src='http://$_SERVER[HTTP_HOST]/static/images/t_no-image.jpg' style='max-width: 100%; max-height: 100%; display: block;' >";
    }
    $markup .= "<td align='center'>$image_markup</td>";
    $markup .= "<td align='center'>$name</td>";
    $markup .= "<td align='right'>$price</td>";
    $markup .= "<td align='left'>$description</td>";
    $markup .= "</tr>";

    return $markup;
}

function pagination_markup($items_per_page, $page, $total_pages, $sort_by, $sort_dir) {
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

    return $pagination_markup;
}
