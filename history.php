<?php
require 'auth.php';
require 'db.php';

// Get the date filter from the URL if it exists (defaults to empty)
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$seller_filter = isset($_GET['seller']) ? $_GET['seller'] : '';

// Build the query dynamically based on filters
$query = "
    SELECT 
        s.id, 
        s.date_sold, 
        s.seller, 
        p.name AS product_name, 
        s.quantity_sold, 
        p.price,
        (s.quantity_sold * p.price) AS total_amount,
        ((s.quantity_sold * p.price) - (s.quantity_sold * p.cost_pc)) AS profit_amount
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE 1=1
";

$params = [];

if (!empty($date_filter)) {
    $query .= " AND DATE(s.date_sold) = :date_filter";
    $params['date_filter'] = $date_filter;
}

if (!empty($seller_filter)) {
    $query .= " AND s.seller = :seller_filter";
    $params['seller_filter'] = $seller_filter;
}

$query .= " ORDER BY s.date_sold DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $sales_history = $stmt->fetchAll();
} catch (PDOException $e) {
    die("History Fetch Failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales History</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
    <style>
        .filter-box { background: #232d38; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #374151; }
        .scroll-x { overflow-x: auto; }
    </style>
</head>
<body class="container">
    <?php include 'header.php'; ?>

   

    <main>
        <!-- Filter Form -->
        <div class="filter-box">
            <form method="GET" action="history.php" style="margin: 0;">
                <div class="grid">
                    <label for="date">Filter by Date
                        <input type="date" id="date" name="date" value="<?= htmlspecialchars($date_filter) ?>">
                    </label>
                    <label for="seller">Filter by Seller
                        <select id="seller" name="seller">
                            <option value="">All Sellers</option>
                            <option value="David" <?= $seller_filter === 'David' ? 'selected' : '' ?>>David</option>
                            <option value="Jehtroy" <?= $seller_filter === 'Jehtroy' ? 'selected' : '' ?>>Jehtroy</option>
                        </select>
                    </label>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="submit" style="flex: 2;">Apply Filters</button>
                    <a href="history.php" role="button" class="secondary" style="flex: 1; text-align: center;">Reset</a>
                </div>
            </form>
        </div>

        <!-- History Table -->
        <div class="scroll-x">
            <table>
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Seller</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Profit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($sales_history) > 0): ?>
                        <?php foreach ($sales_history as $sale): ?>
                        <tr>
                            <td><small><?= date('M d, Y h:i A', strtotime($sale['date_sold'])) ?></small></td>
                            <td><?= htmlspecialchars($sale['seller']) ?></td>
                            <td><strong><?= htmlspecialchars($sale['product_name']) ?></strong></td>
                            <td><?= $sale['quantity_sold'] ?></td>
                            <td>₱<?= number_format($sale['total_amount'], 2) ?></td>
                            <td style="color:#10b981;">₱<?= number_format($sale['profit_amount'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center;">No transactions found matching criteria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>