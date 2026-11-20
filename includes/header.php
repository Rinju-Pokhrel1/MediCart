<?php
require_once __DIR__ . '/../config.php';

$pageTitle = $pageTitle ?? 'MediCart Pharmacy';
$metaDescription = $metaDescription ?? 'Shop everyday pharmacy essentials at MediCart.';

$siteUrl = 'https://localhost:8443';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($requestPath === '/index.php') {
    $requestPath = '/';
}
// Track visits to selected public pages.
$trackedPage = null;
$currentPage = basename($requestPath);

if ($requestPath === '/') {
    $trackedPage = 'catalog';
} elseif ($currentPage === 'product.php' && isset($_GET['id'])) {
    $productId = (int) $_GET['id'];

    if ($productId > 0) {
        $trackedPage = 'product-' . $productId;
    }
} elseif ($currentPage === 'newsletter.php') {
    $trackedPage = 'newsletter';
}

if ($trackedPage !== null) {
    $stmt = $pdo->prepare('INSERT INTO page_views (page) VALUES (:page)');
    $stmt->execute([':page' => $trackedPage]);
}
$canonicalUrl = $siteUrl . $requestPath;

if (basename($requestPath) === 'product.php' && isset($_GET['id'])) {
    $canonicalUrl .= '?id=' . (int) $_GET['id'];
}

$ogType = basename($requestPath) === 'product.php' ? 'product' : 'website';
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= e($pageTitle) ?></title>

<meta name="description" content="<?= e($metaDescription) ?>">

<link rel="canonical" href="<?= e($canonicalUrl) ?>">

<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($metaDescription) ?>">
<meta property="og:type" content="<?= e($ogType) ?>">
<meta property="og:url" content="<?= e($canonicalUrl) ?>">
<meta property="og:site_name" content="MediCart">

<link rel="stylesheet" href="assets/style.css">
<script defer src="assets/app.js"></script></head><body>
<header class="top"><a class="brand" href="index.php">✚ MediCart</a><nav>
<a href="index.php">Shop</a><a href="cart.php">Cart (<?= array_sum($_SESSION['cart'] ?? []) ?>)</a>
<a href="newsletter.php">Newsletter</a>
<?php if(current_user()): ?><span>Hello, <?= e(current_user()['name']) ?></span><?php if(current_user()['role']==='admin'): ?><a href="admin.php">Admin dashboard</a><?php endif; ?><a href="logout.php">Logout</a>
<?php else: ?><a href="login.php">Login</a><a href="register.php">Register</a><?php endif; ?>
</nav></header><main class="container">
