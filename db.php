<?php
// Detect if running on Render by checking for an environment variable
if (getenv('RENDER') || isset($_ENV['DATABASE_URL'])) {
    // ----------------------------------------------------
    // LIVE PRODUCTION: Render PostgreSQL Connection
    // ----------------------------------------------------
    
    // Render provides a complete connection string called DATABASE_URL.
    // We will parse it to extract the host, database name, user, and password.
    $db_url = getenv('DATABASE_URL') ?: $_ENV['DATABASE_URL'];
    $dbopts = parse_url($db_url);

    $host = $dbopts["host"];
    $port = isset($dbopts["port"]) ? $dbopts["port"] : "5432";
    $user = $dbopts["user"];
    $pass = $dbopts["pass"];
    // Remove the leading slash from the path to get the database name
    $db   = ltrim($dbopts["path"], '/'); 
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
} else {
    // ----------------------------------------------------
    // LOCAL DEVELOPMENT: XAMPP MySQL Connection
    // ----------------------------------------------------
    $host = 'localhost';
    $db   = 'marc'; // Your local MySQL database name
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
}

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Database connection failed: " . $e->getMessage());
}
?>