<?php


namespace MService\Payment\Shared\Utils;

use phpseclib\Crypt\RSA;

class Encoder
{

    public static function hashSha256($rawData, $secretKey = null)
    {
        $signature = hash_hmac("sha256", $rawData, $secretKey);
        return $signature;
    }

    public static function encryptRSA(array $rawData, $publicKey)
    {

        $rawJson = json_encode($rawData, JSON_UNESCAPED_UNICODE);

        $rsa = new RSA();
        $rsa->loadKey($publicKey);
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);

        $cipher = $rsa->encrypt($rawJson);
        return base64_encode($cipher);

//OpenSSL is another option; but key must be in PEM Format
//        if (openssl_public_encrypt($rawJson, $crypted, $publicKey, OPENSSL_PKCS1_PADDING)) {
//            return base64_encode($crypted);
//        } else {
//            trigger_error('Failed to encrypt data.');
//            return '';
//        }

    }

    public static function decryptRSA($hashData, $privateKey)
    {

        $rsa = new RSA();
        $rsa->loadKey($privateKey);
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        $decrypted = $rsa->decrypt(base64_decode($hashData));

        return $decrypted;

//        if (openssl_private_decrypt($hashData, $decrypted, $privateKey, OPENSSL_PKCS1_PADDING)) {
//            return $decrypted;
//        } else {
//            trigger_error("Failed to decrypt data");
//            return "";
//        }
    }

}


