<?php


namespace MService\Payment\PayGate;

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

$env = new Environment("https://test-payment.momo.vn", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
'development');
$orderId = time() . "";
$requestId = time() . "";

//CaptureMoMo::process($env, $orderId, "Pay With MoMo", "35000", "sjygdvi", $requestId, "https://google.com.vn", "https://google.com.vn");
//QueryStatusTransaction::process($env, '1561972963', '1561972963');
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

$env = Environment::selectEnv('dev');
HttpClient::HTTPPost($env->getMomoEndpoint(), "/gw_payment/transactionProcessor", "{\"partnerCode\":\"MOMOLRJZ20181206\",\"orderId\":\"1562228609\",\"orderInfo\":\"Pay With MoMo\",\"accessKey\":\"mTCKt9W3eU1m39TW\",\"amount\":\"35000\",\"signature\":\"a19946aea4ffaf25f405d9640e96345b9fa5674732de775f9461b2a0ab5e46d2\",\"extraData\":\"sjygdvi\",\"requestId\":\"1562228609\",\"notifyUrl\":\"https:\/\/google.com.vn\",\"returnUrl\":\"https:\/\/google.com.vn\",\"requestType\":\"captureMoMoWallet\"}");