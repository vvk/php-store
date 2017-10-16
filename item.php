<?php
include 'shared.php';

$id = $_GET['id'];

if (!$id) {
    // If we have no ID then just redirect to the all items list.
    header('Location: items.php');
    exit();
}

$item = get_item($id);

if (!$item) {
    die('Item with ID:' . htmlentities($id) . ' is not found.');
}

$id = htmlentities($id);
$name = htmlentities($item['name']);
$price = htmlentities(number_format($item['price'], 2));
$description = !empty($item['description']) ? nl2br(htmlentities($item['description'])) : "No description.";
$image_ref = !empty($item['image']) ? htmlentities("http://$_SERVER[HTTP_HOST]/images/$item[image]") : "http://$_SERVER[HTTP_HOST]/static/images/no-image.jpg";

// Finish description with period.
if ($description && substr($description, -1) != ".") {
    $description .= '.';
}

?>
<html>
    <head>
        <title>Item: <?php echo $name ?></title>
    </head>
    <body>
        <div style="margin-left: 10px">
            <form action="items.php">
                <input type="submit" value="Back to the items">
            </form>
            <div style="float: left; margin-right: 10px; width: 256px;">
                <h3><?php echo $name?></h3>
                <p><b>ID: </b> <?php echo $id ?></p
                <p><b>Description: </b><?php echo $description ?></p>
                <p><b>Price: </b><?php echo $price ?></p>
                <form action="edit.php" style="display: inline;">
                    <input type="hidden" name="id" value="<?php echo $id ?>"/>
                    <input type="submit" value="Edit"/>
                </form>
                <form action="update.php" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="<?php echo $id ?>"/>
                    <input type="hidden" name="action" value="delete"/>
                    <input type="submit" value="Delete" onclick="return confirm('Are you sure?')"/>
                </form>
            </div>
            <div style="width: 50%; height: 50%; float: left">
                <img alt="No image" src="<?php echo $image_ref ?>"
                     style="max-height: 100%; max-width: 100%">
            </div>
        </div>
    </body>
</html>