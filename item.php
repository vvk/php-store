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
    die('Item with ID:' . htmlspecialchars($id) . ' is not found.');
}

$name = $item['name'];
$price = $item['price'];
$description = array_key_exists('description', $item) ? $item['description'] : null;
$image_name = array_key_exists('image', $item) ? $item['image'] : null;

if ($description && substr($description, -1) != ".") {
    $description .= '.';
}

?>
<div style="margin-left: 10px">
    <div style="float: left; margin-right: 10px; width: 256px;">
        <h3><?php echo htmlspecialchars($name) ?></h3>
        <p><b>ID:</b> <?php echo htmlspecialchars($id) ?></p>
        <?php
        if ($description) {
            ?>
            <p><b>Description:</b> <?php echo htmlspecialchars($description) ?></p>
            <?php
        }
        ?>
        <p><b>Price:</b> <?php echo htmlspecialchars($price) ?></p>
        <form action="items.php">
            <input type="submit" value="Back to the items">
        </form>
    </div>
    <?php if ($image_name) {?>
        <div style="width: 50%; height: 50%; float: left">
            <img src="<?php echo "http://$_SERVER[HTTP_HOST]/images/$image_name"?>" style="max-height: 100%; max-width: 100%">
        </div>
    <?php }?>
</div>