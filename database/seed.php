<?php
require_once __DIR__ . '/../config.php';
$sql = file_get_contents(__DIR__ . '/schema.sql');
$pdo->exec($sql);
$categories = [
 ['Pain Relief','Common pain and fever relief products'],
 ['Vitamins','Vitamins and nutritional supplements'],
 ['First Aid','Basic first-aid essentials'],
 ['Personal Care','Everyday personal-care products'],
 ['Digestive Health','Digestive health products']
];
$stmt = $pdo->prepare('INSERT OR IGNORE INTO categories(name,description) VALUES(?,?)');
foreach ($categories as $c) $stmt->execute($c);
$products = [
 [1,'Paracetamol 500mg','Pain and fever relief tablets',80,10,120,'MED001'],
 [1,'Ibuprofen 200mg','Anti-inflammatory pain relief',150,5,70,'MED002'],
 [2,'Vitamin C 500mg','Vitamin C supplement',350,15,45,'MED003'],
 [2,'Vitamin D3','Vitamin D supplement',500,10,30,'MED004'],
 [3,'Antiseptic Solution','For cleaning minor cuts',220,0,25,'MED005'],
 [3,'Adhesive Bandage Pack','Flexible adhesive bandages',120,5,90,'MED006'],
 [4,'Hand Sanitizer 100ml','Alcohol-based hand sanitizer',100,0,60,'MED007'],
 [4,'Digital Thermometer','Digital body thermometer',450,12,18,'MED008'],
 [5,'ORS Sachet','Oral rehydration salts',25,0,200,'MED009'],
 [5,'Antacid Tablets','For occasional acidity relief',90,8,80,'MED010']
];
$stmt = $pdo->prepare('INSERT OR IGNORE INTO products(category_id,name,description,price,discount_percent,stock,sku) VALUES(?,?,?,?,?,?,?)');
foreach ($products as $p) $stmt->execute($p);
$sampleUsers = [
 ['MediCart Admin','admin@medicart.local','admin'],
 ['Aarav Sharma','aarav@example.com','customer'],
 ['Sita Thapa','sita@example.com','customer'],
 ['Nisha Gurung','nisha@example.com','customer'],
 ['Prakash Rai','prakash@example.com','customer']
];
$userInsert = $pdo->prepare('INSERT OR IGNORE INTO users(name,email,password_hash,role) VALUES(?,?,?,?)');
foreach ($sampleUsers as $u) $userInsert->execute([$u[0],$u[1],password_hash($u[2]==='admin'?'Admin@12345':'Demo@12345', PASSWORD_DEFAULT),$u[2]]);
// Five sample orders ensure the Lab 1 database contains records in the Orders table.
$allUsers = $pdo->query('SELECT id FROM users ORDER BY id LIMIT 5')->fetchAll();
$firstProduct = (int)$pdo->query('SELECT id FROM products ORDER BY id LIMIT 1')->fetch()['id'];
foreach ($allUsers as $index => $u) {
 $exists = $pdo->prepare('SELECT id FROM orders WHERE user_id=?');
 $exists->execute([$u['id']]);
 if (!$exists->fetch()) {
  $amount = 72.00 + ($index * 10);
  $pdo->prepare('INSERT INTO orders(user_id,total,status,payment_status) VALUES(?,?,"sample","paid")')->execute([$u['id'],$amount]);
  $orderId = (int)$pdo->lastInsertId();
  $pdo->prepare('INSERT INTO order_items(order_id,product_id,quantity,unit_price) VALUES(?,?,1,?)')->execute([$orderId,$firstProduct,$amount]);
 }
}
echo 'Database initialized with 5 users, 5 categories, 10 products and 5 sample orders. Admin: admin@medicart.local / Admin@12345. Demo customer password: Demo@12345';
