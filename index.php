<?php
require_once 'config.php';
$pdo->exec("INSERT INTO page_views(page) VALUES('catalog')");
$q = trim($_GET['q'] ?? ''); $category = (int)($_GET['category'] ?? 0);
$sql = "SELECT p.*, c.name AS category FROM products p JOIN categories c ON c.id=p.category_id WHERE (p.name LIKE :q OR p.description LIKE :q)";
$params = [':q'=>'%'.$q.'%'];
if ($category) { $sql .= " AND p.category_id=:category"; $params[':category']=$category; }
$sql .= " ORDER BY p.name";
$stmt=$pdo->prepare($sql); $stmt->execute($params); $products=$stmt->fetchAll();
$categories=$pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$pageTitle='MediCart | Pharmacy Catalog';
$metaDescription='Search pharmacy essentials, compare prices and shop safely at MediCart.';
require 'includes/header.php';
?>
<section class="hero"><div><p class="eyebrow">YOUR EVERYDAY PHARMACY</p><h1>Health essentials,<br><span>made simple.</span></h1><p>Browse trusted everyday wellness and first-aid essentials.</p></div><div class="hero-icon">✚</div></section>
<form class="filters" method="get"><input name="q" placeholder="Search products..." value="<?= e($q) ?>">
<select name="category"><option value="0">All categories</option><?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>" <?= $category===$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select><button>Search</button></form>
<div class="section-heading"><h2>Popular products</h2><span><?= count($products) ?> products</span></div>
<div class="products"><?php foreach($products as $p): $final=$p['price']*(1-$p['discount_percent']/100); ?>
<article class="product"><div class="product-icon">✚</div><span class="tag"><?= e($p['category']) ?></span><h3><a href="product.php?id=<?= $p['id'] ?>"><?= e($p['name']) ?></a></h3><p><?= e($p['description']) ?></p>
<div class="price"><?= money((float)$final) ?> <?php if($p['discount_percent']>0): ?><del><?= money((float)$p['price']) ?></del><?php endif; ?></div>
<p class="stock"><?= $p['stock']>0 ? $p['stock'].' in stock' : 'Out of stock' ?></p><a class="button" href="cart.php?add=<?= $p['id'] ?>">Add to cart</a></article>
<?php endforeach; ?></div>
<?php require 'includes/footer.php'; ?>