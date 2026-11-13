<?php require_once __DIR__ . '/../config.php'; ?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'MediCart Pharmacy') ?></title>
<meta name="description" content="<?= e($metaDescription ?? 'Shop everyday pharmacy essentials at MediCart.') ?>">
<link rel="stylesheet" href="assets/style.css">
<script defer src="assets/app.js"></script></head><body>
<header class="top"><a class="brand" href="index.php">✚ MediCart</a><nav>
<a href="index.php">Shop</a><a href="cart.php">Cart (<?= array_sum($_SESSION['cart'] ?? []) ?>)</a>
<a href="newsletter.php">Newsletter</a>
<?php if(current_user()): ?><span>Hello, <?= e(current_user()['name']) ?></span><?php if(current_user()['role']==='admin'): ?><a href="admin.php">Admin dashboard</a><?php endif; ?><a href="logout.php">Logout</a>
<?php else: ?><a href="login.php">Login</a><a href="register.php">Register</a><?php endif; ?>
</nav></header><main class="container">
