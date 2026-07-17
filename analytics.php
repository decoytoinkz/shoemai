<?php
require 'auth.php';
require 'db.php';

// Detect driver type for cross-database compatibility (MySQL vs PostgreSQL)
$isPostgres = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql');

if ($isPostgres) {
    // PostgreSQL Queries
    $weekly_query = "
        SELECT 
            TO_CHAR(date_combined, 'YYYY-\"W\"IW') as period_label,
            SUM(coalesce(sales_profit, 0)) as total_gross_profit,
            SUM(coalesce(expense_amount, 0)) as total_expenses
        FROM (
            SELECT date_sold as date_combined, (quantity_sold * price) - (quantity_sold * cost_pc) as sales_profit, 0 as expense_amount 
            FROM sales s JOIN products p ON s.product_id = p.id
            UNION ALL
            SELECT date_incurred, 0, amount FROM expenses
        ) combined
        GROUP BY period_label
        ORDER BY period_label DESC
        LIMIT 10
    ";

    $monthly_query = "
        SELECT 
            TO_CHAR(date_combined, 'Month YYYY') as period_label,
            SUM(coalesce(sales_profit, 0)) as total_gross_profit,
            SUM(coalesce(expense_amount, 0)) as total_expenses
        FROM (
            SELECT date_sold as date_combined, (quantity_sold * price) - (quantity_sold * cost_pc) as sales_profit, 0 as expense_amount 
            FROM sales s JOIN products p ON s.product_id = p.id
            UNION ALL
            SELECT date_incurred, 0, amount FROM expenses
        ) combined
        GROUP BY period_label
        ORDER BY MIN(date_combined) DESC
        LIMIT 12
    ";
} else {
    // MySQL Queries
    $weekly_query = "
        SELECT 
            CONCAT(YEAR(date_combined), '-W', WEEK(date_combined, 1)) as period_label,
            SUM(coalesce(sales_profit, 0)) as total_gross_profit,
            SUM(coalesce(expense_amount, 0)) as total_expenses
        FROM (
            SELECT date_sold as date_combined, (quantity_sold * price) - (quantity_sold * cost_pc) as sales_profit, 0 as expense_amount 
            FROM sales s JOIN products p ON s.product_id = p.id
            UNION ALL
            SELECT date_incurred, 0, amount FROM expenses
        ) combined
        GROUP BY period_label
        ORDER BY period_label DESC
        LIMIT 10
    ";

    $monthly_query = "
        SELECT 
            DATE_FORMAT(date_combined, '%M %Y') as period_label,
            SUM(coalesce(sales_profit, 0)) as total_gross_profit,
            SUM(coalesce(expense_amount, 0)) as total_expenses
        FROM (
            SELECT date_sold as date_combined, (quantity_sold * price) - (quantity_sold * cost_pc) as sales_profit, 0 as expense_amount 
            FROM sales s JOIN products p ON s.product_id = p.id
            UNION ALL
            SELECT date_incurred, 0, amount FROM expenses
        ) combined
        GROUP BY period_label
        ORDER BY MIN(date_combined) DESC
        LIMIT 12
    ";
}

try {
    $weekly_data = $pdo->query($weekly_query)->fetchAll();
    $monthly_data = $pdo->query($monthly_query)->fetchAll();
} catch (PDOException $e) {
    die("Analytics Generation Failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interval Analytics</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
    <style>
        .scroll-x { overflow-x: auto; }
        .tab-content { padding: 20px; background: #232d38; border-radius: 8px; border: 1px solid #374151; }
        .tab-btn { margin: 0; }
    </style>
</head>
<body class="container">
    <?php include 'header.php'; ?>

    <main>
        <h2 style="margin-bottom: 25px;">📊 Financial Performance Ledger</h2>

        <!-- Segmented Tab Navigation -->
        <div role="group" style="margin-bottom: 25px;">
            <button id="btn-weekly" class="tab-btn" onclick="showTab('weekly')">Weekly Intervals</button>
            <button id="btn-monthly" class="tab-btn secondary outline" onclick="showTab('monthly')">Monthly Intervals</button>
        </div>

        <!-- Weekly Panel -->
        <article id="panel-weekly" class="tab-content">
            <h4 style="margin-top: 0; color: #9ca3af;">📆 Weekly Breakdown</h4>
            <div class="scroll-x">
                <table>
                    <thead>
                        <tr>
                            <th>Interval</th>
                            <th>Gross Profit</th>
                            <th>Expenses</th>
                            <th>Net Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($weekly_data) > 0): ?>
                            <?php foreach ($weekly_data as $row): 
                                $net = $row['total_gross_profit'] - $row['total_expenses']; 
                                $net_color = $net >= 0 ? '#10b981' : '#ef4444';
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['period_label']) ?></strong></td>
                                <td style="color: #10b981;">₱<?= number_format($row['total_gross_profit'], 2) ?></td>
                                <td style="color: #ef4444;">₱<?= number_format($row['total_expenses'], 2) ?></td>
                                <td style="color: <?= $net_color ?>; font-weight: bold;">₱<?= number_format($net, 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align: center;">No weekly records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>

        <!-- Monthly Panel -->
        <article id="panel-monthly" class="tab-content" style="display: none;">
            <h4 style="margin-top: 0; color: #9ca3af;">📅 Monthly Breakdown</h4>
            <div class="scroll-x">
                <table>
                    <thead>
                        <tr>
                            <th>Interval</th>
                            <th>Gross Profit</th>
                            <th>Expenses</th>
                            <th>Net Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($monthly_data) > 0): ?>
                            <?php foreach ($monthly_data as $row): 
                                $net = $row['total_gross_profit'] - $row['total_expenses']; 
                                $net_color = $net >= 0 ? '#10b981' : '#ef4444';
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['period_label']) ?></strong></td>
                                <td style="color: #10b981;">₱<?= number_format($row['total_gross_profit'], 2) ?></td>
                                <td style="color: #ef4444;">₱<?= number_format($row['total_expenses'], 2) ?></td>
                                <td style="color: <?= $net_color ?>; font-weight: bold;">₱<?= number_format($net, 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align: center;">No monthly records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </main>

    <script>
    function showTab(type) {
        const btnWeekly = document.getElementById('btn-weekly');
        const btnMonthly = document.getElementById('btn-monthly');
        const panelWeekly = document.getElementById('panel-weekly');
        const panelMonthly = document.getElementById('panel-monthly');

        if (type === 'weekly') {
            btnWeekly.classList.remove('secondary', 'outline');
            btnMonthly.classList.add('secondary', 'outline');
            panelWeekly.style.display = 'block';
            panelMonthly.style.display = 'none';
        } else {
            btnMonthly.classList.remove('secondary', 'outline');
            btnWeekly.classList.add('secondary', 'outline');
            panelMonthly.style.display = 'block';
            panelWeekly.style.display = 'none';
        }
    }
    </script>
</body>
</html>