<?php
$id = $_GET['id'];

if (!$id) {
    // If we have no ID then just redirect to the all items list.
    header('Location: items.php');
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $mysqli = new mysqli('localhost', 'root', 'root', 'store');
    $stmt = $mysqli->prepare('SELECT name, description, price FROM items WHERE id = ?');
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
} catch (Exception $e) {
    error_log($e);
    die('Could not open the items with ID:' . htmlspecialchars($id) . '.');
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($mysqli)) {
        $mysqli->close();
    }
}

if (!$item) {
    die('Item with ID:' . htmlspecialchars($id) . ' is not found.');
}

$name = $item['name'];
$description = $item['description'];
$price = $item['price'];

if ($description && substr($description, -1) != ".") {
    $description .= '.';
}

?>
<div style="margin-left: 10px">
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