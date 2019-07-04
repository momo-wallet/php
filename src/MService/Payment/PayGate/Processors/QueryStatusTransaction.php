<?php


namespace MService\Payment\PayGate\Processors;

use MService\Payment\PayGate\Models\QueryStatusRequest;
use MService\Payment\PayGate\Models\QueryStatusResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\Constants\RequestType;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\Process;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;

class QueryStatusTransaction extends Process
{
    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process($env, $orderId, $requestId)
    {
        try {
            echo '========================== START QUERY QUERY STATUS ==================', "\n";

            $queryStatusTransaction = new QueryStatusTransaction($env);
            $queryStatusRequest = $queryStatusTransaction->createQueryStatusRequest($orderId, $requestId);
            $queryStatusResponse = $queryStatusTransaction->execute($queryStatusRequest);

            if (!is_null($queryStatusResponse)) {

                if ($queryStatusResponse->getErrorCode() != 0) {
                    echo "getQueryStatusResponse::errorCode::", $queryStatusResponse->getErrorCode(), "\n";
                    echo "getQueryStatusResponse::errorMessage::", $queryStatusResponse->getMessage(), "\n";
                    echo "getQueryStatusResponse::localMessage::", $queryStatusResponse->getLocalMessage(), "\n";
                } else {
                    echo "getQueryStatusResponse::requestId::", $queryStatusResponse->getRequestId(), "\n";
                    echo "getQueryStatusResponse::orderId::", $queryStatusResponse->getOrderId(), "\n";
                    echo "getQueryStatusResponse::transId::", $queryStatusResponse->getTransId(), "\n";
                }
            }
            echo '========================== END QUERY QUERY STATUS ==================', "\n";
            return $queryStatusResponse;

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
    }

    public function createQueryStatusRequest($orderId, $requestId): QueryStatusRequest
    {

        $rawData = Parameter::PARTNER_CODE . "=" . $this->getPartnerInfo()->getPartnerCode() .
            "&" . Parameter::ACCESS_KEY . "=" . $this->getPartnerInfo()->getAccessKey() .
            "&" . Parameter::REQUEST_ID . "=" . $requestId .
            "&" . Parameter::ORDER_ID . "=" . $orderId .
            "&" . Parameter::REQUEST_TYPE . "=" . RequestType::TRANSACTION_STATUS;

        echo 'createQueryStatusRequest::rawDataBeforeHash::', $rawData, "\n";
        $signature = Encoder::hashSha256($rawData, $this->getPartnerInfo()->getSecretKey());
        echo 'createQueryStatusRequest::signature::' . $signature, "\n";

        $arr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::ACCESS_KEY => $this->getPartnerInfo()->getAccessKey(),
            Parameter::REQUEST_ID => $requestId,
            Parameter::ORDER_ID => $orderId,
            Parameter::SIGNATURE => $signature,
        );

        return new QueryStatusRequest($arr);
    }

    public function execute(QueryStatusRequest $queryStatusRequest)
    {
        try {
            $data = json_encode($queryStatusRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), Parameter::PAY_GATE_URI, $data);

            if ($response->getStatusCode() != 200) {
                throw new MoMoException("Error API");
            }

            $queryStatusResponse = new QueryStatusResponse(json_decode($response->getBody(), true));

            return $this->checkResponse($queryStatusResponse);

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
        return null;
    }

    public function checkResponse(QueryStatusResponse $queryStatusResponse)
    {
        try {
            Process::errorMoMoProcess($queryStatusResponse->getErrorCode());

            //check signature
            $rawHash = Parameter::PARTNER_CODE . "=" . $queryStatusResponse->getPartnerCode() .
                "&" . Parameter::ACCESS_KEY . "=" . $queryStatusResponse->getAccessKey() .
                "&" . Parameter::REQUEST_ID . "=" . $queryStatusResponse->getRequestId() .
                "&" . Parameter::ORDER_ID . "=" . $queryStatusResponse->getOrderId() .
                "&" . Parameter::ERROR_CODE . "=" . $queryStatusResponse->getErrorCode() .
                "&" . Parameter::TRANS_ID . "=" . $queryStatusResponse->getTransId() .
                "&" . Parameter::AMOUNT . "=" . $queryStatusResponse->getAmount() .
                "&" . Parameter::MESSAGE . "=" . $queryStatusResponse->getMessage() .
                "&" . Parameter::LOCAL_MESSAGE . "=" . $queryStatusResponse->getLocalMessage() .
                "&" . Parameter::REQUEST_TYPE . "=" . $queryStatusResponse->getRequestType() .
                "&" . Parameter::PAY_TYPE . "=" . $queryStatusResponse->getPayType() .
                "&" . Parameter::EXTRA_DATA . "=" . $queryStatusResponse->getExtraData();

            echo "getQueryStatusResponse::partnerRawDataBeforeHash::" . $rawHash . "\n";
            $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());
            echo "getQueryStatusResponse::partnerSignature::" . $signature . "\n";
            echo "getQueryStatusResponse::momoSignature::" . $queryStatusResponse->getSignature() . "\n";

            if ($signature == $queryStatusResponse->getSignature())
                return $queryStatusResponse;
            else
                throw new MoMoException("Wrong signature from MoMo side - please contact with us");
        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
        return $queryStatusResponse;
    }

}