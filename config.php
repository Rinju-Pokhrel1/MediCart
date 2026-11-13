<?php
declare(strict_types=1);
session_start();
$dbFile = __DIR__ . '/database/medicart.sqlite';
$pdo = new PDO('sqlite:' . $dbFile, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
$pdo->exec('PRAGMA foreign_keys = ON');
// Backward-compatible migration for existing MediCart databases.
$productColumns = $pdo->query('PRAGMA table_info(products)')->fetchAll();
$existingColumns = array_column($productColumns, 'name');
foreach ([
    'manufacturer' => 'TEXT',
    'batch_number' => 'TEXT',
    'expiry_date' => 'TEXT',
    'image_url' => 'TEXT'
] as $column => $type) {
    if (!in_array($column, $existingColumns, true)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN {$column} {$type}");
    }
}

define('SITE_NAME', 'MediCart');
define('BASE_URL', 'http://localhost:8000');
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function money(float $amount): string { return 'Rs. ' . number_format($amount, 2); }
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): void {
    if (!current_user()) { header('Location: login.php'); exit; }
}
