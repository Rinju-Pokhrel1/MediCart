<?php
require_once 'config.php';require_login();$cart=$_SESSION['cart']??[];$error='';
if(!$cart){header('Location: cart.php');exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{$pdo->beginTransaction();$total=0;$rows=[];
 foreach($cart as $id=>$qty){$s=$pdo->prepare('SELECT * FROM products WHERE id=?');$s->execute([(int)$id]);$p=$s->fetch();if(!$p||(int)$p['stock']<(int)$qty)throw new RuntimeException('Insufficient stock for '.($p['name']??'a product'));$price=$p['price']*(1-$p['discount_percent']/100);$total+=$price*$qty;$rows[]=[$p,$qty,$price];}
 $s=$pdo->prepare("INSERT INTO orders(user_id,total,status,payment_status) VALUES(?,?,'processing','unpaid')");$s->execute([current_user()['id'],$total]);$orderId=(int)$pdo->lastInsertId();
 foreach($rows as [$p,$qty,$price]){$pdo->prepare('INSERT INTO order_items(order_id,product_id,quantity,unit_price) VALUES(?,?,?,?)')->execute([$orderId,$p['id'],$qty,$price]);$u=$pdo->prepare('UPDATE products SET stock=stock-? WHERE id=? AND stock>=?');$u->execute([$qty,$p['id'],$qty]);if($u->rowCount()!==1)throw new RuntimeException('Stock changed. Please retry.');}
 $pdo->commit();$_SESSION['cart']=[];$_SESSION['last_order']=$orderId;header('Location: payment.php?order='.$orderId);exit;
 }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$error=$e->getMessage();}
}
$pageTitle='Checkout | MediCart';require 'includes/header.php'; ?>
<h1>Confirm your order</h1><?php if($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?><p>Checkout validates available stock, creates an order and decreases stock inside one database transaction.</p><form method="post"><button>Place order and continue to payment</button></form>
<?php require 'includes/footer.php'; ?>