<?php
require_once 'config.php';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){$s=$pdo->prepare('SELECT * FROM users WHERE email=?');$s->execute([trim($_POST['email']??'')]);$u=$s->fetch();
if($u && password_verify($_POST['password']??'',$u['password_hash'])){session_regenerate_id(true);$_SESSION['user']=['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']];header('Location: '.($u['role']==='admin'?'admin.php':'index.php'));exit;}else $error='Incorrect email or password.';}
$pageTitle='Login | MediCart';require 'includes/header.php'; ?>
<h1>Welcome back</h1><?php if(isset($_GET['registered'])): ?><p class="success">Registration successful. Please log in.</p><?php endif; ?><?php if($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<form class="form" method="post"><label>Email<input type="email" name="email" required></label><label>Password<input type="password" name="password" required></label><button>Login</button></form>
<p>Demo admin: admin@medicart.local / Admin@12345</p><?php require 'includes/footer.php'; ?>