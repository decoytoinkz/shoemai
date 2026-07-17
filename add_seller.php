<?php
require 'db.php';

$message = "";

// Handle adding a new seller
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_seller'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO sellers (name) VALUES (?)");
            $stmt->execute([$name]);
            $message = "<ins>Worker '$name' added successfully!</ins>";
        } catch (PDOException $e) {
            $message = "<mark>Error: Worker might already exist.</mark>";
        }
    }
}

// Handle toggling active/inactive status
if (isset($_GET['toggle_id'])) {
    $id = intval($_GET['toggle_id']);
    try {
        $stmt = $pdo->prepare("UPDATE sellers SET status = IF(status='active', 'inactive', 'active') WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: add_seller.php");
        exit;
    } catch (PDOException $e) {
        $message = "<mark>Error updating status.</mark>";
    }
}

// Fetch all sellers
$sellers = $pdo->query("SELECT * FROM sellers ORDER BY status ASC, name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Workers</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
</head>
<body class="container">
    <?php include 'header.php'; ?>
    
    <h2>👥 Manage Workers</h2>

    <main>
        <?php if (!empty($message)): ?>
            <p><?= $message ?></p>
        <?php endif; ?>

        <!-- Add Seller Form -->
        <form method="POST" action="add_seller.php">
            <label for="name">New Worker Name
                <input type="text" id="name" name="name" placeholder="e.g., John" required>
            </label>
            <button type="submit" name="add_seller">Register Worker</button>
        </form>

        <!-- Current Workers List -->
        <h3>Current Workers</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sellers as $s): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                    <td>
                        <span style="color: <?= $s['status'] === 'active' ? '#10b981' : '#ef4444' ?>;">
                            <?= ucfirst($s['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="add_seller.php?toggle_id=<?= $s['id'] ?>" role="button" class="<?= $s['status'] === 'active' ? 'secondary' : '' ?>" style="padding: 4px 10px; font-size: 0.8rem;">
                            <?= $s['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>