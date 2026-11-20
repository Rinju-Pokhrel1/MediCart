<?php
require_once 'config.php';
require_login();
if (current_user()['role'] !== 'admin') { http_response_code(403); exit('Admin access only'); }

$notice = '';
$error = '';
$allowedStatuses = ['pending','processing','shipped','completed','cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        if ($action === 'save_product') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $price = (float)($_POST['price'] ?? 0);
            $discount = (float)($_POST['discount_percent'] ?? 0);
            $stock = (int)($_POST['stock'] ?? 0);
            if ($name === '' || $categoryId < 1 || $price < 0 || $discount < 0 || $discount > 90 || $stock < 0) {
                throw new RuntimeException('Please enter valid medicine details. Discount must be between 0 and 90%.');
            }
            $data = [
                $categoryId, $name, trim($_POST['description'] ?? ''), $price, $discount, $stock,
                trim($_POST['sku'] ?? '') ?: null, trim($_POST['manufacturer'] ?? ''),
                trim($_POST['batch_number'] ?? ''), trim($_POST['expiry_date'] ?? '') ?: null,
                trim($_POST['image_url'] ?? '')
            ];
            if ($id > 0) {
                $data[] = $id;
                $stmt = $pdo->prepare('UPDATE products SET category_id=?,name=?,description=?,price=?,discount_percent=?,stock=?,sku=?,manufacturer=?,batch_number=?,expiry_date=?,image_url=? WHERE id=?');
                $stmt->execute($data);
                $notice = 'Medicine updated successfully.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO products(category_id,name,description,price,discount_percent,stock,sku,manufacturer,batch_number,expiry_date,image_url) VALUES(?,?,?,?,?,?,?,?,?,?,?)');
                $stmt->execute($data);
                $notice = 'Medicine added successfully. It is now available in the shop catalog.';
            }
        } elseif ($action === 'delete_product') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM order_items WHERE product_id=?');
            $stmt->execute([$id]);
            if ((int)$stmt->fetchColumn() > 0) {
                throw new RuntimeException('This medicine is linked to an order and cannot be deleted. Set its stock to 0 instead.');
            }
            $pdo->prepare('DELETE FROM products WHERE id=?')->execute([$id]);
            $notice = 'Medicine deleted.';
        } elseif ($action === 'update_order') {
            $status = $_POST['status'] ?? '';
            if (!in_array($status, $allowedStatuses, true)) throw new RuntimeException('Invalid order status.');
            $pdo->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$status, (int)$_POST['order_id']]);
            $notice = 'Order status updated.';
        }
    } catch (Throwable $ex) { $error = $ex->getMessage(); }
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$products = $pdo->query('SELECT p.*,c.name category FROM products p JOIN categories c ON c.id=p.category_id ORDER BY p.name')->fetchAll();
$orders = $pdo->query('SELECT o.*,u.name customer,u.email FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC LIMIT 30')->fetchAll();
$customers = $pdo->query("SELECT u.id,u.name,u.email,u.created_at,COUNT(o.id) order_count,COALESCE(SUM(CASE WHEN o.payment_status='paid' THEN o.total ELSE 0 END),0) spent FROM users u LEFT JOIN orders o ON o.user_id=u.id WHERE u.role='customer' GROUP BY u.id ORDER BY u.created_at DESC")->fetchAll();
$views=(int)$pdo->query('SELECT COUNT(*) FROM page_views')->fetchColumn();
$pageStats = $pdo->query(
    'SELECT page, COUNT(*) AS visits
     FROM page_views
     GROUP BY page
     ORDER BY visits DESC'
)->fetchAll(PDO::FETCH_ASSOC);
$subscribers=(int)$pdo->query('SELECT COUNT(*) FROM newsletter_subscribers')->fetchColumn();
$orderCount=(int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$revenue=(float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status='paid'")->fetchColumn();
$lowStock=(int)$pdo->query('SELECT COUNT(*) FROM products WHERE stock<=10')->fetchColumn();
$expired=(int)$pdo->query("SELECT COUNT(*) FROM products WHERE expiry_date IS NOT NULL AND expiry_date<>'' AND date(expiry_date)<date('now')")->fetchColumn();
$pageTitle='Admin Dashboard | MediCart';
require 'includes/header.php';
?>
<section class="admin-hero">
 <div><span class="eyebrow">MEDICART · ADMIN WORKSPACE</span><h1>Good day, <?= e(current_user()['name']) ?> <span>✦</span></h1>
 <p>Your pharmacy at a glance. Manage inventory, orders and customer activity from one place.</p>
 <div class="hero-meta"><span>● Store system active</span><span><?= date('l, d M Y') ?></span></div></div>
 <div class="admin-hero-art" aria-hidden="true"><div class="art-cross">✚</div><div class="art-pill">＋</div><div class="art-dot"></div></div>
</section>
<div class="admin-welcome-row"><div><h2>Overview</h2><p>Here’s what’s happening with your store today.</p></div><a class="button" href="#add-medicine">＋ Add medicine</a></div>
<?php if($notice): ?><p class="success"><?= e($notice) ?></p><?php endif; ?>
<?php if($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<div class="stats admin-stats">
 <article class="product stat-card"><span class="stat-icon">✚</span><div><h2><?= count($products) ?></h2><p>Medicines listed</p></div><small>Catalog</small></article>
 <article class="product stat-card"><span class="stat-icon purple">♙</span><div><h2><?= count($customers) ?></h2><p>Registered customers</p></div><small>Community</small></article>
 <article class="product stat-card"><span class="stat-icon blue">▤</span><div><h2><?= $orderCount ?></h2><p>Total orders</p></div><small>Sales</small></article>
 <article class="product stat-card"><span class="stat-icon gold">↗</span><div><h2><?= money($revenue) ?></h2><p>Paid revenue</p></div><small>Collected</small></article>
 <article class="product stat-card"><span class="stat-icon orange">!</span><div><h2><?= $lowStock ?></h2><p>Low-stock items</p></div><small>Needs attention</small></article>
 <article class="product stat-card"><span class="stat-icon red">⌁</span><div><h2><?= $expired ?></h2><p>Expired medicines</p></div><small>Safety check</small></article>
</div>
<div class="admin-section" id="add-medicine">
<div class="section-title"><div><span class="eyebrow">CATALOG MANAGEMENT</span><h2><?= $edit ? 'Edit medicine' : 'Add a new medicine' ?></h2><p>Add and maintain your pharmacy products.</p></div><span class="section-symbol">✚</span></div>
<form class="admin-form" method="post">
<input type="hidden" name="action" value="save_product"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>">
<label>Medicine name<input name="name" required value="<?= e($edit['name']??'') ?>"></label>
<label>Category<select name="category_id" required><option value="">Choose category</option><?php foreach($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= (int)($edit['category_id']??0)===(int)$cat['id']?'selected':'' ?>><?= e($cat['name']) ?></option><?php endforeach; ?></select></label>
<label>Manufacturer<input name="manufacturer" value="<?= e($edit['manufacturer']??'') ?>" placeholder="e.g. Nepal Pharmaceuticals"></label>
<label>Batch number<input name="batch_number" value="<?= e($edit['batch_number']??'') ?>"></label>
<label>Expiry date<input type="date" name="expiry_date" value="<?= e($edit['expiry_date']??'') ?>"></label>
<label>SKU / Medicine code<input name="sku" value="<?= e($edit['sku']??'') ?>"></label>
<label>Price (Rs.)<input type="number" min="0" step="0.01" name="price" required value="<?= e((string)($edit['price']??'')) ?>"></label>
<label>Discount (%)<input type="number" min="0" max="90" step="0.01" name="discount_percent" value="<?= e((string)($edit['discount_percent']??0)) ?>"></label>
<label>Stock quantity<input type="number" min="0" name="stock" required value="<?= e((string)($edit['stock']??0)) ?>"></label>
<label>Image URL (optional)<input type="url" name="image_url" value="<?= e($edit['image_url']??'') ?>"></label>
<label class="wide">Description<textarea name="description" rows="3"><?= e($edit['description']??'') ?></textarea></label>
<div class="wide"><button><?= $edit?'Save changes':'Add medicine' ?></button> <?php if($edit): ?><a class="button secondary" href="admin.php">Cancel edit</a><?php endif; ?></div>
</form>
</div>
<div class="admin-section"><div class="section-title"><div><span class="eyebrow">PRODUCTS</span><h2>Medicine inventory</h2><p>Monitor availability, pricing and expiry details.</p></div><span class="section-symbol">▦</span></div>
<div class="table-wrap"><table><thead><tr><th>Medicine</th><th>Category</th><th>Manufacturer / Batch</th><th>Expiry</th><th>Price</th><th>Discount</th><th>Stock</th><th>Actions</th></tr></thead><tbody>
<?php foreach($products as $p): ?><tr><td><strong><?= e($p['name']) ?></strong><br><small><?= e($p['sku']??'') ?></small></td><td><?= e($p['category']) ?></td><td><?= e($p['manufacturer']??'') ?><br><small><?= e($p['batch_number']??'') ?></small></td><td><?= e($p['expiry_date']??'Not set') ?><?php if(!empty($p['expiry_date']) && $p['expiry_date']<date('Y-m-d')): ?><br><span class="alert-text">Expired</span><?php endif; ?></td><td><?= money((float)$p['price']) ?></td><td><?= (float)$p['discount_percent'] ?>%</td><td><?= (int)$p['stock'] ?><?php if((int)$p['stock']<=10): ?><br><span class="alert-text">Low stock</span><?php endif; ?></td><td><a href="admin.php?edit=<?= $p['id'] ?>">Edit</a><form method="post" class="inline-form" onsubmit="return confirm('Delete this medicine?')"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="danger">Delete</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<div class="admin-section"><div class="section-title"><div><span class="eyebrow">FULFILMENT</span><h2>Recent orders</h2><p>Review purchases and keep customers updated.</p></div><span class="section-symbol">⇢</span></div><div class="table-wrap"><table><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Update</th></tr></thead><tbody>
<?php foreach($orders as $o): ?><tr><td>#<?= $o['id'] ?><br><small><?= e($o['created_at']) ?></small></td><td><?= e($o['customer']) ?><br><small><?= e($o['email']) ?></small></td><td><?= money((float)$o['total']) ?></td><td><?= e($o['payment_status']) ?></td><td><?= e($o['status']) ?></td><td><form method="post" class="inline-form"><input type="hidden" name="action" value="update_order"><input type="hidden" name="order_id" value="<?= $o['id'] ?>"><select name="status"><?php foreach($allowedStatuses as $st): ?><option <?= $o['status']===$st?'selected':'' ?> value="<?= $st ?>"><?= ucfirst($st) ?></option><?php endforeach; ?></select><button>Update</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<div class="admin-section"><div class="section-title"><div><span class="eyebrow">CUSTOMERS</span><h2>Customer directory</h2><p>Understand your customer activity.</p></div><span class="section-symbol">♙</span></div><div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Orders</th><th>Paid total</th><th>Joined</th></tr></thead><tbody><?php foreach($customers as $customer): ?><tr><td><?= e($customer['name']) ?></td><td><?= e($customer['email']) ?></td><td><?= (int)$customer['order_count'] ?></td><td><?= money((float)$customer['spent']) ?></td><td><?= e($customer['created_at']) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
<div class="admin-section">
    <div class="section-title">
        <div>
            <span class="eyebrow">INSIGHTS</span>
            <h2>Store activity</h2>
            <p>Simple engagement metrics from your lab project.</p>
        </div>
        <span class="section-symbol">⌁</span>
    </div>

    <p>
        Total page views: <strong><?= $views ?></strong>
        · Newsletter subscribers: <strong><?= $subscribers ?></strong>
    </p>

    <h3>Traffic by page</h3>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Page</th>
                    <th>Total Visits</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pageStats as $stat): ?>
                    <tr>
                        <td><?= e(ucwords(str_replace('-', ' ', $stat['page']))) ?></td>
                        <td><?= (int)$stat['visits'] ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($pageStats)): ?>
                    <tr>
                        <td colspan="2">No traffic data available yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
