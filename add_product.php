<?php
require 'auth.php';
require 'db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $cost_pc = floatval($_POST['cost_pc']);
    $price = floatval($_POST['price']);

    if (!empty($name) && $cost_pc >= 0 && $price >= 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, cost_pc, price) VALUES (?, ?, ?)");
            $stmt->execute([$name, $cost_pc, $price]);
            $message = "<ins>Product '$name' added successfully!</ins>";
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
    <title>Add Product</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
</head>
<body class="container">
    <?php include 'header.php'; ?>

    <main>
        <?php if (!empty($message)): ?>
            <p><?= $message ?></p>
        <?php endif; ?>

        <form method="POST" action="add_product.php">
            <label for="name">Product Name
                <input type="text" id="name" name="name" placeholder="e.g., Japanese, Pork, Java Rice" required>
            </label>

            <div class="grid">
                <label for="cost_pc">Cost per Piece
                    <input type="number" step="0.01" id="cost_pc" name="cost_pc" placeholder="0.00" required>
                </label>

                <label for="price">Selling Price
                    <input type="number" step="0.01" id="price" name="price" placeholder="0.00" required>
                </label>
            </div>

            <button type="submit">Save Product</button>
        </form>
    </main>
</body>
</html>