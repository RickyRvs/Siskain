<?php

$config = [
    "digest_alg" => "sha256",
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
    "config" => "C:\\xampp\\apache\\conf\\openssl.cnf",
];

$res = openssl_pkey_new($config);

if ($res === false) {
    while ($msg = openssl_error_string()) {
        echo "OpenSSL Error: $msg\n";
    }
    exit;
}

openssl_pkey_export($res, $privateKey, null, $config);

$publicKeyDetails = openssl_pkey_get_details($res);
$publicKey = $publicKeyDetails["key"];

file_put_contents('private_key.pem', $privateKey);
file_put_contents('public_key.pem', $publicKey);

echo "=== PRIVATE KEY ===\n" . $privateKey . "\n\n";
echo "=== PUBLIC KEY ===\n" . $publicKey . "\n";