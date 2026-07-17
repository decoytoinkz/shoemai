<?php
require 'auth.php';
require 'db.php';

$message = "";

// Fetch products to populate the dropdown selection menu
try {
    $stmt = $pdo->query("SELECT id, name FROM products ORDER BY name ASC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching products: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity_added = intval($_POST['quantity_added']);

    if ($product_id > 0 && $quantity_added > 0) {
        try {
            // Log the new stock entry
            $stmt = $pdo->prepare("INSERT INTO inventory (product_id, quantity_added) VALUES (?, ?)");
            $stmt->execute([$product_id, $quantity_added]);
            $message = "<ins>Stock successfully added!</ins>";
        } catch (PDOException $e) {
            $message = "<mark>Error: " . $e->getMessage() . "</mark>";
        }
    } else {
        $message = "<mark>Please select a product and enter a valid quantity.</mark>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Stock</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
</head>
<body class="container">
    <?php include 'header.php'; ?>

    <main>
        <?php if (!empty($message)): ?>
            <p><?= $message ?></p>
        <?php endif; ?>

        <form method="POST" action="add_stock.php">
            <label for="product_id">Select Product
                <select id="product_id" name="product_id" required>
                    <option value="" disabled selected>Which item are you restocking?</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label for="quantity_added">Quantity Received
                <input type="number" id="quantity_added" name="quantity_added" min="1" placeholder="e.g., 100" required>
            </label>

            <button type="submit">Update Stock Level</button>
        </form>
    </main>
</body>
</html>