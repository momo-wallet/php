<?php


namespace MService\Payment\PayGate;

use MService\Payment\PayGate\Processors\CaptureIPN;
use MService\Payment\PayGate\Processors\CaptureMoMo;
use MService\Payment\PayGate\Processors\PayATM;
use MService\Payment\PayGate\Processors\QueryStatusTransaction;
use MService\Payment\PayGate\Processors\RefundATM;
use MService\Payment\PayGate\Processors\RefundStatus;
use MService\Payment\PayGate\Processors\RefundMoMo;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;
use MService\Payment\Shared\Utils\HttpClient;

require "../../../../loader.php";

$orderId = time() . "";
$requestId = time() . "";

//CaptureMoMo::process($env, $orderId, "Pay With MoMo", "35000", "sjygdvi", $requestId, "https://google.com.vn", "https://google.com.vn");
//QueryStatusTransaction::process(Environment::selectEnv('dev'), '1561972963', '1561972963');
//
//$orderId = (time() + (7 * 24 * 60 * 60))."";
//$requestId = (time() + (7 * 24 * 60 * 60))."";
//PayATM::process($env, $orderId, "Pay With MoMo", "35000", '', $requestId, "https://google.com.vn", "https://google.com.vn", "SML");
//QueryStatusTransaction::process($env, $orderId, $requestId);

//////////Refund Processes
//$orderId = (time() + (5 * 24 * 60 * 60))."";
//RefundATM::process($env, $orderId, '1562059843', '10000', '2304992176', 'SML');

//$orderId = (time() + (10 * 24 * 60 * 60))."";
//RefundMoMo::process($env, $orderId, '1561972963', '7000', '2304963974');
//RefundStatus::process($env, '1561972963', '1561972963');
$env = new Environment("https://test-payment.momo.vn", new PartnerInfo("ZjF6taKUohp7iN8l", 'MOMOTUEN20190312', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
    'development');
$data = "partnerCode=MOMOTUEN20190312&accessKey=ZjF6taKUohp7iN8l&requestId=1555383430&orderId=1555383430&orderInfo=&orderType=momo_wallet&transId=2302586804&errorCode=0&message=Success&localMessage=Th%C3%A0nh%20c%C3%B4ng&payType=qr&responseTime=2019-04-09%2014%3A53%3A38&extraData=&signature=2a23a88aab6b6dd00b07669d84904778cc9d429c6a5748fa77298b05886a8620&amount=300000";
CaptureIPN::process($env, $data);