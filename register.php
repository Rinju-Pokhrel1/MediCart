<?php
require_once 'config.php';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){$name=trim($_POST['name']??'');$email=filter_var($_POST['email']??'',FILTER_VALIDATE_EMAIL);$password=$_POST['password']??'';
if(!$name||!$email||strlen($password)<8)$error='Enter a name, valid email and password of at least 8 characters.';
else{try{$s=$pdo->prepare('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,?)');$s->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),'customer']);header('Location: login.php?registered=1');exit;}catch(PDOException $e){$error='That email may already be registered.';}}}
$pageTitle='Register | MediCart';require 'includes/header.php'; ?>
<h1>Create customer account</h1><?php if($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<form class="form" method="post"><label>Full name<input name="name" required></label><label>Email<input type="email" name="email" required></label><label>Password<input type="password" name="password" minlength="8" required></label><button>Create account</button></form>
<?php require 'includes/footer.php'; ?>