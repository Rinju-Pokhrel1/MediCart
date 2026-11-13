<?php
// Run: php -S localhost:8443 -t . https-server.php
// First create cert.pem/key.pem using the OpenSSL command in README.md.
$context = stream_context_create(['ssl'=>[
 'local_cert'=>__DIR__.'/cert.pem','local_pk'=>__DIR__.'/key.pem',
 'allow_self_signed'=>true,'verify_peer'=>false
]]);
// PHP's built-in server does not provide HTTPS through this router file.
// Use the OpenSSL reverse-proxy command in README.md for local HTTPS testing.
echo "Use the documented OpenSSL reverse proxy for HTTPS local testing.";
