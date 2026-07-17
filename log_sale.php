<?php
require 'db.php';

$message = "";

// Fetch active products
$stmt = $pdo->query("SELECT id, name FROM products ORDER BY name ASC");
$products = $stmt->fetchAll();

// NEW: Fetch only active sellers for the dropdown
$stmt_sellers = $pdo->query("SELECT name FROM sellers WHERE status = 'active' ORDER BY name ASC");
$active_sellers = $stmt_sellers->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity_sold = intval($_POST['quantity_sold']);
    $seller = trim($_POST['seller']);

    if ($product_id > 0 && $quantity_sold > 0 && !empty($seller)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO sales (product_id, quantity_sold, seller) VALUES (?, ?, ?)");
            $stmt->execute([$product_id, $quantity_sold, $seller]);
            $message = "<ins>Sale logged successfully by $seller!</ins>";
        } catch (PDOException $e) {
            $message = "<mark>Error: " . $e->getMessage() . "</mark>";
        }
    } else {
        $message = "<mark>Please fill out all fields correctly.</mark>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Sale</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
</head>
<body class="container">
    <?php include 'header.php'; ?>

    <main>
        <?php if (!empty($message)): ?>
            <p><?= $message ?></p>
        <?php endif; ?>

        <label for="seller">Select Seller
                <select id="seller" name="seller" required>
                    <option value="" disabled selected>Who is making this sale?</option>
                    <?php foreach ($active_sellers as $s): ?>
                        <option value="<?= htmlspecialchars($s['name']) ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label for="product_id">Select Product
                <select id="product_id" name="product_id" required>
                    <option value="" disabled selected>Choose an item...</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label for="quantity_sold">Quantity Sold
                <input type="number" id="quantity_sold" name="quantity_sold" min="1" placeholder="e.g., 5" required>
            </label>

            <button type="submit">Log Sale</button>
        </form>
    </main>
</body>
</html>