<?php
require 'auth.php';
require 'db.php';

$message = "";

// Fetch active sellers for the dropdown
try {
    $stmt_sellers = $pdo->query("SELECT name FROM sellers WHERE status = 'active' ORDER BY name ASC");
    $active_sellers = $stmt_sellers->fetchAll();
} catch (PDOException $e) {
    die("Error fetching sellers: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description']);
    $seller = trim($_POST['seller']);

    if ($amount > 0 && !empty($description) && !empty($seller)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO expenses (amount, description, seller) VALUES (?, ?, ?)");
            $stmt->execute([$amount, $description, $seller]);
            $message = "<ins>Expense of ₱" . number_format($amount, 2) . " logged by $seller!</ins>";
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
    <title>Log Expense</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
</head>
<body class="container">
    <?php include 'header.php'; ?>

    <main>
        <h2>💸 Log Expense</h2>
        
        <?php if (!empty($message)): ?>
            <p><?= $message ?></p>
        <?php endif; ?>

        <form method="POST" action="log_expense.php">
            <label for="seller">Select Person
                <select id="seller" name="seller" required>
                    <option value="" disabled selected>Who spent the money?</option>
                    <?php foreach ($active_sellers as $s): ?>
                        <option value="<?= htmlspecialchars($s['name']) ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label for="description">Expense Description
                <input type="text" id="description" name="description"  required>
            </label>

            <label for="amount">Amount Spent (₱)
                <input type="number" step="0.01" id="amount" name="amount" min="0.01" placeholder="0.00" required>
            </label>

            <button type="submit">Record Expense</button>
        </form>
    </main>
</body>
</html>