<?php
require_once 'config.php'; $_SESSION['cart'] ??= [];
if(isset($_GET['add'])){$id=(int)$_GET['add'];$s=$pdo->prepare('SELECT stock FROM products WHERE id=?');$s->execute([$id]);$p=$s->fetch();if($p && $p['stock']>0)$_SESSION['cart'][$id]=($_SESSION['cart'][$id]??0)+1;header('Location: cart.php');exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){foreach(($_POST['qty']??[]) as $id=>$qty){$qty=max(0,(int)$qty);if($qty===0)unset($_SESSION['cart'][$id]);else $_SESSION['cart'][(int)$id]=$qty;}header('Location: cart.php');exit;}
$items=[];$total=0;
if($_SESSION['cart']){$ids=array_keys($_SESSION['cart']);$marks=implode(',',array_fill(0,count($ids),'?'));$s=$pdo->prepare("SELECT * FROM products WHERE id IN ($marks)");$s->execute($ids);$items=$s->fetchAll();}
$pageTitle='Shopping Cart | MediCart';require 'includes/header.php'; ?>
<h1>Your shopping cart</h1><?php if(!$items): ?><p>Your cart is empty. <a href="index.php">Browse products</a>.</p><?php else: ?>
<form method="post"><table><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th></tr>
<?php foreach($items as $p):$price=$p['price']*(1-$p['discount_percent']/100);$qty=$_SESSION['cart'][$p['id']];$sub=$price*$qty;$total+=$sub; ?>
<tr><td><?= e($p['name']) ?></td><td><?= money($price) ?></td><td><input class="qty" type="number" min="0" max="<?= $p['stock'] ?>" name="qty[<?= $p['id'] ?>]" value="<?= $qty ?>"></td><td><?= money($sub) ?></td></tr><?php endforeach; ?></table><button>Update cart</button></form>
<h2 class="total">Total: <?= money($total) ?></h2><a class="button" href="checkout.php">Proceed to checkout</a><?php endif; ?>
<?php require 'includes/footer.php'; ?>