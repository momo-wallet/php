<?php

include_once "../../../../loader.php";

use MService\Payment\Pay\Processors\PaymentConfirmation;
use MService\Payment\Pay\Processors\POSPay;
use MService\Payment\Pay\Processors\TransactionQuery;
use MService\Payment\Pay\Processors\TransactionRefund;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;

$env = new Environment("https://test-payment.momo.vn", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
    'development');
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

POSPay::process($env, 'MM587977818202493946', 50000, $publicKey, $partnerRefId, '', '', '');
//PaymentConfirmation::process($env, '1562138427', "capture", "2305016460", $requestId);
//
//TransactionQuery::process($env, '1562138468', $publicKey, '1562138427');
//TransactionRefund::process($env, $requestId, 10000, $publicKey, '1562138427', '2305016460');