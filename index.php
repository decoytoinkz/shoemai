<?php
require 'auth.php';
require 'db.php';

// 1. Check if a specific seller filter is active
$seller_filter = isset($_GET['seller']) ? $_GET['seller'] : '';

// 2. Fetch Time-Based Profit & Expense Analytics
$seller_where_sales = $seller_filter ? "WHERE s.seller = :seller" : "";
$seller_where_expenses = $seller_filter ? "WHERE e.seller = :seller" : "";

// Detect driver type for cross-database compatibility (MySQL vs PostgreSQL)
$isPostgres = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql');

if ($isPostgres) {
    // PostgreSQL compliant syntax
    $today = "CURRENT_DATE";
    $sales_week_condition = "EXTRACT(WEEK FROM s.date_sold) = EXTRACT(WEEK FROM CURRENT_DATE) AND EXTRACT(YEAR FROM s.date_sold) = EXTRACT(YEAR FROM CURRENT_DATE)";
    $expense_week_condition = "EXTRACT(WEEK FROM e.date_incurred) = EXTRACT(WEEK FROM CURRENT_DATE) AND EXTRACT(YEAR FROM e.date_incurred) = EXTRACT(YEAR FROM CURRENT_DATE)";
    $sales_month_condition = "EXTRACT(MONTH FROM s.date_sold) = EXTRACT(MONTH FROM CURRENT_DATE) AND EXTRACT(YEAR FROM s.date_sold) = EXTRACT(YEAR FROM CURRENT_DATE)";
    $expense_month_condition = "EXTRACT(MONTH FROM e.date_incurred) = EXTRACT(MONTH FROM CURRENT_DATE) AND EXTRACT(YEAR FROM e.date_incurred) = EXTRACT(YEAR FROM CURRENT_DATE)";
} else {
    // Local MySQL/XAMPP syntax
    $today = "CURDATE()";
    $sales_week_condition = "YEARWEEK(s.date_sold, 0) = YEARWEEK(CURDATE(), 0)";
    $expense_week_condition = "YEARWEEK(e.date_incurred, 0) = YEARWEEK(CURDATE(), 0)";
    $sales_month_condition = "MONTH(s.date_sold) = MONTH(CURDATE()) AND YEAR(s.date_sold) = YEAR(CURDATE())";
    $expense_month_condition = "MONTH(e.date_incurred) = MONTH(CURDATE()) AND YEAR(e.date_incurred) = YEAR(CURDATE())";
}

$analytics_query = "
    SELECT 
        -- Gross Profits (Sales - Cost of Goods Sold)
        SUM(CASE WHEN DATE(s.date_sold) = $today THEN (s.quantity_sold * p.price) - (s.quantity_sold * p.cost_pc) ELSE 0 END) as daily_gross_profit,
        SUM(CASE WHEN $sales_week_condition THEN (s.quantity_sold * p.price) - (s.quantity_sold * p.cost_pc) ELSE 0 END) as weekly_gross_profit,
        SUM(CASE WHEN $sales_month_condition THEN (s.quantity_sold * p.price) - (s.quantity_sold * p.cost_pc) ELSE 0 END) as monthly_gross_profit,
        SUM((s.quantity_sold * p.price) - (s.quantity_sold * p.cost_pc)) as total_gross_profit
    FROM sales s
    JOIN products p ON s.product_id = p.id
    $seller_where_sales
";

$expense_query = "
    SELECT 
        SUM(CASE WHEN DATE(e.date_incurred) = $today THEN e.amount ELSE 0 END) as daily_expenses,
        SUM(CASE WHEN $expense_week_condition THEN e.amount ELSE 0 END) as weekly_expenses,
        SUM(CASE WHEN $expense_month_condition THEN e.amount ELSE 0 END) as monthly_expenses,
        SUM(e.amount) as total_expenses
    FROM expenses e
    $seller_where_expenses
";

try {
    // Run sales profits calculations
    $analytics_stmt = $pdo->prepare($analytics_query);
    if ($seller_filter) {
        $analytics_stmt->execute(['seller' => $seller_filter]);
    } else {
        $analytics_stmt->execute();
    }
    $sales_analytics = $analytics_stmt->fetch();

    // Run expenses calculations
    $expense_stmt = $pdo->prepare($expense_query);
    if ($seller_filter) {
        $expense_stmt->execute(['seller' => $seller_filter]);
    } else {
        $expense_stmt->execute();
    }
    $expense_analytics = $expense_stmt->fetch();
} catch (PDOException $e) {
    die("Analytics Query Failed: " . $e->getMessage());
}

// Compute Net Profits (Gross Profit minus Expenses)
$daily_net = ($sales_analytics['daily_gross_profit'] ?? 0) - ($expense_analytics['daily_expenses'] ?? 0);
$weekly_net = ($sales_analytics['weekly_gross_profit'] ?? 0) - ($expense_analytics['weekly_expenses'] ?? 0);
$monthly_net = ($sales_analytics['monthly_gross_profit'] ?? 0) - ($expense_analytics['monthly_expenses'] ?? 0);

// 3. Fetch Product List Breakdown
$query = "
    SELECT 
        p.id, p.name, p.cost_pc, p.price,
        COALESCE(i.stock_in, 0) as stock_in,
        COALESCE(s.sold, 0) as sold,
        (COALESCE(i.stock_in, 0) - COALESCE(s.sold, 0)) as left_stock,
        (COALESCE(s.sold, 0) * p.price) as sales,
        ((COALESCE(s.sold, 0) * p.price) - (COALESCE(s.sold, 0) * p.cost_pc)) as profit
    FROM products p
    LEFT JOIN (
        SELECT product_id, SUM(quantity_added) as stock_in 
        FROM inventory 
        GROUP BY product_id
    ) i ON p.id = i.product_id
    LEFT JOIN (
        SELECT product_id, SUM(quantity_sold) as sold 
        FROM sales 
        " . ($seller_filter ? "WHERE seller = :seller" : "") . "
        GROUP BY product_id
    ) s ON p.id = s.product_id
    ORDER BY p.name ASC
";

try {
    $stmt = $pdo->prepare($query);
    if ($seller_filter) {
        $stmt->execute(['seller' => $seller_filter]);
    } else {
        $stmt->execute();
    }
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error processing metrics: " . $e->getMessage());
}

// Compute cumulative totals for display
$total_stock = array_sum(array_column($products, 'stock_in'));
$total_sold = array_sum(array_column($products, 'sold'));
$total_sales = array_sum(array_column($products, 'sales'));
$total_gross_all = array_sum(array_column($products, 'profit'));
$total_net_all = $total_gross_all - ($expense_analytics['total_expenses'] ?? 0);

// Fetch all active sellers to build the filter group
$active_sellers = $pdo->query("SELECT name FROM sellers WHERE status = 'active' ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
    <style>
        .grid-totals { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 25px; }
        .grid-analytics { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 25px; }
        .card { padding: 15px; border-radius: 8px; background: #232d38; text-align: center; border: 1px solid #374151; }
        .card h5 { margin-bottom: 5px; color: #9ca3af; font-size: 0.85rem; }
        .card p { margin: 0; font-size: 1.25rem; font-weight: bold; }
        .scroll-x { overflow-x: auto; }
        .filter-group { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
        .filter-group a { flex: 1; min-width: 100px; text-align: center; padding: 8px; border-radius: 4px; text-decoration: none; font-size: 0.9rem; }
        .btn-active { background-color: #10b981 !important; color: white !important; border-color: #10b981 !important; }
        .btn-inactive { background-color: #1f2937; color: #9ca3af; border: 1px solid #374151; }
        
        /* Small screen responsive adjustments */
        @media (max-width: 576px) {
            .grid-analytics { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="container">
    <?php include 'header.php'; ?>

    <main>
        <!-- DYNAMIC Seller Performance Toggle -->
        <div class="filter-group">
            <a href="index.php" class="<?= $seller_filter === '' ? 'btn-active' : 'btn-inactive' ?>">All Shops</a>
            <?php foreach ($active_sellers as $s): ?>
                <a href="index.php?seller=<?= urlencode($s['name']) ?>" class="<?= $seller_filter === $s['name'] ? 'btn-active' : 'btn-inactive' ?>">
                    <?= htmlspecialchars($s['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Profit Breakdown Timeline -->
       <h3>Net Profit Intervals (<?= $seller_filter ? htmlspecialchars($seller_filter) : 'All' ?>)</h3>
        <div class="grid-analytics">
            <!-- Daily Net Card -->
            <div class="card" style="border-top: 4px solid #3b82f6;">
                <h5>Daily Net</h5>
                <p style="color:#3b82f6; margin-bottom: 5px;">₱<?= number_format($daily_net, 2) ?></p>
                <div style="font-size: 0.85rem; color: #9ca3af; border-top: 1px solid #374151; margin-top: 8px; padding-top: 8px;">
                    Daily Expense: <span style="color: #ef4444; font-weight: bold;">₱<?= number_format($expense_analytics['daily_expenses'] ?? 0, 2) ?></span>
                </div>
            </div>
            
            <div class="card" style="border-top: 4px solid #f59e0b;">
                <h5>Weekly Net</h5>
                <p style="color:#f59e0b; margin-bottom: 5px;">₱<?= number_format($weekly_net, 2) ?></p>
                <div style="font-size: 0.85rem; color: #9ca3af; border-top: 1px solid #374151; margin-top: 8px; padding-top: 8px;">
                    Weekly Expense: <span style="color: #ef4444; font-weight: bold;">₱<?= number_format($expense_analytics['weekly_expenses'] ?? 0, 2) ?></span>
                </div>
            </div>

            <div class="card" style="border-top: 4px solid #10b981;">
                <h5>Monthly Net</h5>
                <p style="color:#10b981; margin-bottom: 5px;">₱<?= number_format($monthly_net, 2) ?></p>
                <div style="font-size: 0.85rem; color: #9ca3af; border-top: 1px solid #374151; margin-top: 8px; padding-top: 8px;">
                    Monthly Expense: <span style="color: #ef4444; font-weight: bold;">₱<?= number_format($expense_analytics['monthly_expenses'] ?? 0, 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Cumulative Business Metrics -->
        <h3>All-Time Metrics</h3>
        <div class="grid-totals">
            <div class="card"><h5>Total Stock</h5><p><?= $total_stock ?></p></div>
            <div class="card"><h5>Total Sold</h5><p><?= $total_sold ?></p></div>
            <div class="card"><h5>Total Spent</h5><p style="color:#ef4444;">₱<?= number_format($expense_analytics['total_expenses'] ?? 0, 2) ?></p></div>
            <div class="card"><h5>Total Net Profit</h5><p style="color:#10b981;">₱<?= number_format($total_net_all, 2) ?></p></div>
        </div>

        <!-- Breakdown Ledger Layout with Action Button Inline -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; margin-top: 30px;">
            <h3 style="margin: 0;">Product Breakdown</h3>
            <a href="add_product.php" class="button" style="background-color: #10b981; border-color: #10b981; padding: 6px 15px; font-size: 0.9rem; margin: 0;">+ Add Product</a>
        </div>

        <div class="scroll-x">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Stock In</th>
                        <th>Left</th>
                        <th>Sold</th>
                        <th>Sales</th>
                        <th>Profit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $p): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                            <td><?= $p['stock_in'] ?></td>
                            <td><?= $p['left_stock'] ?></td>
                            <td><?= $p['sold'] ?></td>
                            <td>₱<?= number_format($p['sales'], 2) ?></td>
                            <td>₱<?= number_format($p['profit'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center;">No product data available yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>