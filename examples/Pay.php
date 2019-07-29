<?php

//Sample Code -- please change the autoload yourself as appropriate
include_once '../loader.php';
include_once '../vendor/autoload.php';

use MService\Payment\Pay\Processors\AppPay;
use MService\Payment\Pay\Processors\PaymentConfirmation;
use MService\Payment\Pay\Processors\POSPay;
use MService\Payment\Pay\Processors\QRNotify;
use MService\Payment\Pay\Processors\TransactionQuery;
use MService\Payment\Pay\Processors\TransactionRefund;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;

$env = new Environment("https://test-payment.momo.vn/pay/pos", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'), 'development');
$publicKey = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkpa+qMXS6O11x7jBGo9W3yxeHEsAdyDE
40UoXhoQf9K6attSIclTZMEGfq6gmJm2BogVJtPkjvri5/j9mBntA8qKMzzanSQaBEbr8FyByHnf
226dsLt1RbJSMLjCd3UC1n0Yq8KKvfHhvmvVbGcWfpgfo7iQTVmL0r1eQxzgnSq31EL1yYNMuaZj
pHmQuT24Hmxl9W9enRtJyVTUhwKhtjOSOsR03sMnsckpFT9pn1/V9BE2Kf3rFGqc6JukXkqK6ZW9
mtmGLSq3K+JRRq2w8PVmcbcvTr/adW4EL2yc1qk9Ec4HtiDhtSYd6/ov8xLVkKAQjLVt7Ex3/agR
PfPrNwIDAQAB
-----END PUBLIC KEY-----";
$requestId = time() . "";
$partnerRefId = time() . "";

/** Pay Processes:
 * App-In-App
 * POS
 * QR Code
 * Payment Confirmation
 * Transaction Query
 * Transaction Refund
 */

//get new Token from MoMoApp and put in appData
//uncomment to use AppPay function
//$appData = '' ;
//$customerNumber = '0917003000';
//$pKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkpa+qMXS6O11x7jBGo9W3yxeHEsAdyDE40UoXhoQf9K6attSIclTZMEGfq6gmJm2BogVJtPkjvri5/j9mBntA8qKMzzanSQaBEbr8FyByHnf226dsLt1RbJSMLjCd3UC1n0Yq8KKvfHhvmvVbGcWfpgfo7iQTVmL0r1eQxzgnSq31EL1yYNMuaZjpHmQuT24Hmxl9W9enRtJyVTUhwKhtjOSOsR03sMnsckpFT9pn1/V9BE2Kf3rFGqc6JukXkqK6ZW9mtmGLSq3K+JRRq2w8PVmcbcvTr/adW4EL2yc1qk9Ec4HtiDhtSYd6/ov8xLVkKAQjLVt7Ex3/agRPfPrNwIDAQAB';
//AppPay::process(new Environment("https://test-payment.momo.vn/pay/app", new PartnerInfo("IICsyHpuwP8IzVvE", 'CGV01', 'vLujzooXM8ySdHJOBFuwmWB3T4ZBYLJ'), 'development'), 10000, $appData, $pKey, $customerNumber, $partnerRefId);

//POSPay::process($env, 'MM587977818202493946', 50000, $publicKey, $partnerRefId, '', '', '');

$qrRawData = '{
  "partnerCode": "MOMOIQA420180417",
  "accessKey": "mTCKt9W3eU1m39TW",
  "amount": 10000,
  "partnerRefId": "B001221",
  "partnerTransId": "",
  "transType": "momo_wallet",
  "momoTransId": "43121679",
  "status": 0,
  "message": "Thành Công",
  "responseTime": 1555472829549,
  "signature": "e33dcd33ea016023a1ca49877241fa35609163e967e86716f9fc974e91a23164",
  "storeId": "store001"
}';
//QRNotify::process($env, $qrRawData);

PaymentConfirmation::process(new Environment("https://test-payment.momo.vn/pay/confirm", new PartnerInfo("IICsyHpuwP8IzVvE", 'CGV01', 'vLujzooXM8ySdHJOBFuwmWB3T4ZBYLJ'), 'development'), 'e671ffb0-af61-11e9-ba3c-4b08721e3699', "capture", "2305581638", $requestId);

TransactionQuery::process(new Environment("https://test-payment.momo.vn/pay/query-status", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'), 'development'), '1562138468', $publicKey, '1562138427');

TransactionRefund::process(new Environment("https://test-payment.momo.vn/pay/refund", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'), 'development'), $requestId, 10000, $publicKey, '1562138427', '2305016460');

