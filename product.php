<?php
require_once 'config.php'; $id=(int)($_GET['id']??0);
$stmt=$pdo->prepare('SELECT p.*,c.name category FROM products p JOIN categories c ON c.id=p.category_id WHERE p.id=?');$stmt->execute([$id]);$p=$stmt->fetch();
if(!$p){http_response_code(404);exit('Product not found');}
$related=$pdo->prepare('SELECT * FROM products WHERE category_id=? AND id<>? LIMIT 4');$related->execute([$p['category_id'],$id]);
$pageTitle=$p['name'].' | MediCart';$metaDescription=substr($p['description'],0,150);
require 'includes/header.php'; ?>
<div class="detail"><div class="detail-icon">✚</div><div><span class="tag"><?= e($p['category']) ?></span><h1><?= e($p['name']) ?></h1><p><?= e($p['description']) ?></p>
<?php if(!empty($p['manufacturer'])): ?><p><strong>Manufacturer:</strong> <?= e($p['manufacturer']) ?></p><?php endif; ?>
<?php if(!empty($p['batch_number'])): ?><p><strong>Batch:</strong> <?= e($p['batch_number']) ?></p><?php endif; ?>
<?php if(!empty($p['expiry_date'])): ?><p><strong>Expiry date:</strong> <?= e($p['expiry_date']) ?></p><?php endif; ?>
<h2><?= money($p['price']*(1-$p['discount_percent']/100)) ?></h2><p><?= (int)$p['stock'] ?> available</p><a class="button" href="cart.php?add=<?= $p['id'] ?>">Add to cart</a></div></div>
<h2>Related products</h2><div class="products"><?php foreach($related as $r): ?><article class="product"><div class="product-icon">✚</div><h3><a href="product.php?id=<?= $r['id'] ?>"><?= e($r['name']) ?></a></h3><p><?= e($r['description']) ?></p><b><?= money($r['price']*(1-$r['discount_percent']/100)) ?></b></article><?php endforeach; ?></div>
<?php require 'includes/footer.php'; ?>