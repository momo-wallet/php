<?php

//Sample Code -- please change the autoload yourself as appropriate
include_once '../loader.php';
include_once '../vendor/autoload.php';

use MService\Payment\AllInOne\Processors\CaptureIPN;
use MService\Payment\AllInOne\Processors\PayATM;
use MService\Payment\AllInOne\Processors\QueryStatusTransaction;
use MService\Payment\AllInOne\Processors\RefundATM;
use MService\Payment\AllInOne\Processors\RefundMoMo;
use MService\Payment\AllInOne\Processors\RefundStatus;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;
use MService\Payment\AllInOne\Processors\CaptureMoMo;

$orderId = time() . "";
$requestId = time() . "";

$env = new Environment("https://test-payment.momo.vn/gw_payment/transactionProcessor",
    new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
    'development', '', false);

/** MoMo Wallet Processes:
 * CaptureMoMo returns a payURL with a QR Code for user to scan with MoMo App and pay
 * QueryStatusTransaction checks the status of the transaction, given orderId and requestId
 * CaptureIPN processes the request MoMo sent to the server
 * RefundMoMo allows user to request refund for transactions paid through MoMo Wallet
 * RefundStatus checks the status of the refund requests, given the orderId of the paid transaction and requestId
 **/

CaptureMoMo::process($env, $orderId, "Pay With MoMo", "35000", "sjygdvi", $requestId, "https://google.com.vn", "https://google.com.vn");
QueryStatusTransaction::process($env, $requestId, $orderId);

$data = "partnerCode=MOMOLRJZ20181206&accessKey=mTCKt9W3eU1m39TW&requestId=1555383430&orderId=1555383430&orderInfo=&orderType=momo_wallet&transId=2302586804&errorCode=0&message=Success&localMessage=Th%C3%A0nh%20c%C3%B4ng&payType=qr&responseTime=2019-04-09%2014%3A53%3A38&extraData=&signature=e9469360fe360e8c63a97a755300e4321648a796593e41411bea5c426af33249&amount=300000";
CaptureIPN::process($env, $data);

$orderId = (time() + (10 * 24 * 60 * 60))."";
RefundMoMo::process($env, $orderId, '1561972963', '7000', '2304963974');
RefundStatus::process($env, '1561972963', '1561972963');

/** MoMo ATM Processes:
 * PayATM
 * QueryStatusTransaction
 * RefundATM
**/

$orderId = (time() + (7 * 24 * 60 * 60))."";
$requestId = (time() + (7 * 24 * 60 * 60))."";

PayATM::process($env, $orderId, "Pay With MoMo", "35000", '', $requestId, "https://google.com.vn", "https://google.com.vn", "SML");
QueryStatusTransaction::process($env, $orderId, $requestId);

$orderId = (time() + (5 * 24 * 60 * 60))."";
RefundATM::process($env, $orderId, '1562059843', '10000', '2304992176', 'SML');
