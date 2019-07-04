<?php


namespace MService\Payment\Pay\Processors;

use MService\Payment\Pay\Models\TransactionQueryRequest;
use MService\Payment\Pay\Models\TransactionQueryResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\Constants\RequestType;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\Process;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;

class TransactionQuery extends Process
{
    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public function createTransactionQueryRequest($requestId, $publicKey, $partnerRefId, $momoTransId = null): TransactionQueryRequest
    {

        $jsonArr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::PARTNER_REF_ID => $partnerRefId,
            Parameter::REQUEST_ID => $requestId,
            Parameter::MOMO_TRANS_ID => $momoTransId
        );

        echo 'createTransactionQueryRequest::rawDataBeforeHash::', json_encode(array_filter($jsonArr, function ($var) {return !is_null($var);})), "\n";
        $hash = Encoder::encryptRSA($jsonArr, $publicKey);
        echo 'createTransactionQueryRequest::hashRSA::' . $hash, "\n";

        $arr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::PARTNER_REF_ID => $partnerRefId,
            Parameter::HASH => $hash,
            Parameter::VERSION => RequestType::VERSION,
            Parameter::MOMO_TRANS_ID => $momoTransId,
        );

        return new TransactionQueryRequest($arr);
    }

    public function execute(TransactionQueryRequest $transactionQueryRequest)
    {
        try {
            $data = json_encode($transactionQueryRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), Parameter::PAY_STATUS_URI, $data);

            if ($response->getStatusCode() != 200) {
                throw new MoMoException("Error API");
            }

            $transactionQueryResponse = new TransactionQueryResponse(json_decode($response->getBody(), true));

            if ($transactionQueryResponse->getStatus() != 0) {
                echo "getTransactionQueryResponse::errorCode::", $transactionQueryResponse->getStatus(), "\n";
                echo "getTransactionQueryResponse::errorMessage::", $transactionQueryResponse->getMessage(), "\n";
            } else {
                $data = $transactionQueryResponse->getData();
                echo "getTransactionQueryResponse::billId::", $data->getBillId(), "\n";
                echo "getTransactionQueryResponse::amount::", $data->getAmount(), "\n";
                echo "getTransactionQueryResponse::phoneNumber::", $data->getPhoneNumber(), "\n";
            }

            return $transactionQueryResponse;
        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
        return null;
    }


    public static function process($env, $requestId, $publicKey, $partnerRefId, $momoTransId = null)
    {
        try {
            echo '========================== START TRANSACTION QUERY STATUS ==================', "\n";

            $transactionQuery = new TransactionQuery($env);
            $transactionQueryRequest = $transactionQuery->createTransactionQueryRequest($requestId, $publicKey, $partnerRefId, $momoTransId);
            $transactionQueryResponse = $transactionQuery->execute($transactionQueryRequest);

            echo '========================== END TRANSACTION QUERY STATUS ==================', "\n";
            return $transactionQueryResponse;

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
    }
}