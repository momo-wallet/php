<?php


namespace MService\Payment\Shared\Utils;


class Encoder
{

    public static function hashSha256($rawData, $secretKey = null)
    {
        $signature = hash_hmac("sha256", $rawData, $secretKey);
        return $signature;
    }

    public static function encryptRSA(array $rawData, $publicKey)
    {

        $rawJson = json_encode($rawData);

        if (openssl_public_encrypt($rawJson, $crypted, $publicKey, OPENSSL_PKCS1_PADDING)) {
            return base64_encode($crypted);
        } else {
            trigger_error('Failed to encrypt data.');
            return '';
        }
    }

    public static function decryptRSA($hashData, $privateKey)
    {

        if (openssl_private_decrypt($hashData, $decrypted, $privateKey, OPENSSL_PKCS1_PADDING)) {
            return $decrypted;
        } else {
            trigger_error("Failed to decrypt data");
            return "";
        }

    }

}
