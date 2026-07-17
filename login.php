<?php
session_start();

// If already logged in, skip this page
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    // Retrieve the secure hash from the environment variables
    $hash = getenv('ADMIN_PASSWORD_HASH');

    if ($hash && password_verify($password, $hash)) {
        $_SESSION['logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "Incorrect password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        main { max-width: 400px; width: 100%; padding: 20px; }
    </style>
</head>
<body class="container">
    <main>
        <article>
            <h3 style="text-align: center;">Authorized Access Only</h3>
            <?php if ($error): ?>
                <p style="color: #ef4444; text-align: center;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <label for="password">Enter Passkey</label>
                <input type="password" id="password" name="password" required autofocus>
                <button type="submit" class="contrast">Unlock Dashboard</button>
            </form>
        </article>
    </main>
</body>
</html>