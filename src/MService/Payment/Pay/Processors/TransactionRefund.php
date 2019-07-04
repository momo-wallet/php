<?php


namespace MService\Payment\Pay\Processors;

use MService\Payment\Pay\Models\TransactionRefundRequest;
use MService\Payment\Pay\Models\TransactionRefundResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\Constants\RequestType;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\Process;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;

class TransactionRefund extends Process
{
    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public function createTransactionRefundRequest($requestId, $amount, $publicKey, $partnerRefId, $momoTransId, $storeId = null, $description = null): TransactionRefundRequest
    {

        $jsonArr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::PARTNER_REF_ID => $partnerRefId,
            Parameter::AMOUNT => $amount,
            Parameter::STORE_ID => $storeId,
            Parameter::DESCRIPTION => $description,
            Parameter::MOMO_TRANS_ID => $momoTransId
        );

        echo 'createTransactionRefundRequest::rawDataBeforeHash::', json_encode(array_filter($jsonArr, function ($var) {return !is_null($var);})), "\n";
        $hash = Encoder::encryptRSA($jsonArr, $publicKey);
        echo 'createTransactionRefundRequest::hashRSA::' . $hash, "\n";

        $arr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::REQUEST_ID => $requestId,
            Parameter::HASH => $hash,
            Parameter::VERSION => RequestType::VERSION,
        );

        return new TransactionRefundRequest($arr);
    }

    public function execute(TransactionRefundRequest $transactionRefundRequest)
    {
        try {
            $data = json_encode($transactionRefundRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), Parameter::PAY_REFUND_URI, $data);

            if ($response->getStatusCode() != 200) {
                throw new MoMoException("Error API");
            }

            $transactionRefundResponse = new TransactionRefundResponse(json_decode($response->getBody(), true));

            if ($transactionRefundResponse->getStatus() != 0) {
                echo "getTransactionRefundResponse::errorCode::", $transactionRefundResponse->getStatus(), "\n";
                echo "getTransactionRefundResponse::errorMessage::", $transactionRefundResponse->getMessage(), "\n";
            }

            echo "getTransactionRefundResponse::partnerRefId::", $transactionRefundResponse->getPartnerRefId(), "\n";
            echo "getTransactionRefundResponse::transid::", $transactionRefundResponse->getTransid(), "\n";
            echo "getTransactionRefundResponse::amount::", $transactionRefundResponse->getAmount(), "\n";

            return $transactionRefundResponse;

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
        return null;
    }


    public static function process($env, $requestId, $amount, $publicKey, $partnerRefId, $momoTransId, $storeId = null, $description = null)
    {
        try {
            echo '========================== START TRANSACTION REFUND STATUS ==================', "\n";

            $transactionRefund = new TransactionRefund($env);
            $transactionRefundRequest = $transactionRefund->createTransactionRefundRequest($requestId, $amount, $publicKey, $partnerRefId, $momoTransId, $storeId, $description);
            $transactionRefundResponse = $transactionRefund->execute($transactionRefundRequest);

            echo '========================== END TRANSACTION REFUND STATUS ==================', "\n";
            return $transactionRefundResponse;

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
    }
}