<?php
require_once 'config.php';$message='';
if($_SERVER['REQUEST_METHOD']==='POST'){$email=filter_var($_POST['email']??'',FILTER_VALIDATE_EMAIL);if($email){try{$pdo->prepare('INSERT INTO newsletter_subscribers(email) VALUES(?)')->execute([$email]);$message='Subscribed! A welcome email preview was added to storage/mail.log.';$body="Welcome to MediCart! You will receive pharmacy updates and offers.\n";file_put_contents(__DIR__.'/storage/mail.log',"TO: $email\nSUBJECT: Welcome to MediCart\n$body---\n",FILE_APPEND);}catch(PDOException $e){$message='This email is already subscribed.';}}else $message='Please enter a valid email.';}
$pageTitle='Newsletter | MediCart';require 'includes/header.php'; ?>
<h1>Get pharmacy updates</h1><p>Subscribe for product news and promotional offers.</p><?php if($message): ?><p class="success"><?= e($message) ?></p><?php endif; ?><form class="form" method="post"><label>Email address<input type="email" name="email" required></label><button>Subscribe</button></form>
<p>For real email delivery, configure SMTP with PHPMailer and a Mailtrap sandbox. This lab version logs a safe preview locally.</p>
<?php require 'includes/footer.php'; ?>