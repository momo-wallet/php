<?php

use MService\Payment\AllInOne\Processors\CaptureMoMo;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;

$orderId = time() . "";
$requestId = time() . "";

$env = new Environment("https://test-payment.momo.vn/gw_payment/transactionProcessor", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
    'development', 'MoMoLogger', false);

CaptureMoMo::process($env, $orderId, "Pay With MoMo", "35000", "sjygdvi", $requestId, "https://google.com.vn", "https://google.com.vn");
//uncomment to use QueryStatusTransaction
//QueryStatusTransaction::process($env, '1561972963', '1561972963');
//
//uncomment to use PayATM and QueryStatusTransaction
//$orderId = (time() + (7 * 24 * 60 * 60))."";
//$requestId = (time() + (7 * 24 * 60 * 60))."";
//PayATM::process($env, $orderId, "Pay With MoMo", "35000", '', $requestId, "https://google.com.vn", "https://google.com.vn", "SML");
//QueryStatusTransaction::process($env, $orderId, $requestId);

//uncomment to use RefundATM
//$orderId = (time() + (5 * 24 * 60 * 60))."";
//RefundATM::process($env, $orderId, '1562059843', '10000', '2304992176', 'SML');

//uncomment to use RefundMoMo and RefundStatus
//$orderId = (time() + (10 * 24 * 60 * 60))."";
//RefundMoMo::process($env, $orderId, '1561972963', '7000', '2304963974');
//RefundStatus::process($env, '1561972963', '1561972963');

//uncomment to use CaptureIPN
//$data = "partnerCode=MOMOTUEN20190312&accessKey=ZjF6taKUohp7iN8l&requestId=1555383430&orderId=1555383430&orderInfo=&orderType=momo_wallet&transId=2302586804&errorCode=0&message=Success&localMessage=Th%C3%A0nh%20c%C3%B4ng&payType=qr&responseTime=2019-04-09%2014%3A53%3A38&extraData=&signature=2a23a88aab6b6dd00b07669d84904778cc9d429c6a5748fa77298b05886a8620&amount=300000";
//CaptureIPN::process($env, $data);
