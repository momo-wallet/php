<?php


namespace MService\Payment\PayGate\Processors;

use MService\Payment\PayGate\Models\RefundATMRequest;
use MService\Payment\PayGate\Models\RefundATMResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\Constants\RequestType;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\Process;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;

class RefundATM extends Process
{
    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process($env, $orderId, $requestId, $amount, $transId, $bankCode)
    {
        try {
            echo '========================== START ATM REFUND PROCESS ==================', "\n";

            $refundATM = new RefundATM($env);
            $refundATMRequest = $refundATM->createRefundATMRequest($orderId, $requestId, $amount, $transId, $bankCode);
            $refundATMResponse = $refundATM->execute($refundATMRequest);

            if (!is_null($refundATMResponse) && $refundATMResponse->getErrorCode() != 0) {
                echo "getrefundATMResponse::errorCode::", $refundATMResponse->getErrorCode(), "\n";
                echo "getrefundATMResponse::errorMessage::", $refundATMResponse->getMessage(), "\n";
                echo "getrefundATMResponse::localMessage::", $refundATMResponse->getLocalMessage(), "\n";
            }

            echo '========================== END ATM REFUND PROCESS ==================', "\n";
            return $refundATMResponse;

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
    }

    public function createRefundATMRequest($orderId, $requestId, $amount, $transId, $bankCode): RefundATMRequest
    {

        $rawData = Parameter::PARTNER_CODE . "=" . $this->getPartnerInfo()->getPartnerCode() .
            "&" . Parameter::ACCESS_KEY . "=" . $this->getPartnerInfo()->getAccessKey() .
            "&" . Parameter::REQUEST_ID . "=" . $requestId .
            "&" . Parameter::BANK_CODE . "=" . $bankCode .
            "&" . Parameter::AMOUNT . "=" . $amount .
            "&" . Parameter::ORDER_ID . "=" . $orderId .
            "&" . Parameter::TRANS_ID . "=" . $transId .
            "&" . Parameter::REQUEST_TYPE . "=" . RequestType::REFUND_ATM;

        echo 'createRefundATMRequest::rawDataBeforeHash::', $rawData, "\n";
        $signature = Encoder::hashSha256($rawData, $this->getPartnerInfo()->getSecretKey());
        echo 'createRefundATMRequest::signature::' . $signature, "\n";

        $arr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::ACCESS_KEY => $this->getPartnerInfo()->getAccessKey(),
            Parameter::REQUEST_ID => $requestId,
            Parameter::AMOUNT => $amount,
            Parameter::BANK_CODE => $bankCode,
            Parameter::ORDER_ID => $orderId,
            Parameter::TRANS_ID => $transId,
            Parameter::SIGNATURE => $signature,
        );

        return new RefundATMRequest($arr);
    }

    public function execute(RefundATMRequest $refundATMRequest)
    {
        try {
            $data = json_encode($refundATMRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), Parameter::PAY_GATE_URI, $data);

            if ($response->getStatusCode() != 200) {
                throw new MoMoException("Error API");
            }

            $refundATMResponse = new RefundATMResponse(json_decode($response->getBody(), true));

            return $this->checkResponse($refundATMResponse);

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
        return null;
    }

    public function checkResponse(RefundATMResponse $refundATMResponse)
    {
        try {
            Process::errorMoMoProcess($refundATMResponse->getErrorCode());

            //check signature
            $rawHash = Parameter::PARTNER_CODE . "=" . $refundATMResponse->getPartnerCode() .
                "&" . Parameter::ACCESS_KEY . "=" . $refundATMResponse->getAccessKey() .
                "&" . Parameter::REQUEST_ID . "=" . $refundATMResponse->getRequestId() .
                "&" . Parameter::ORDER_ID . "=" . $refundATMResponse->getOrderId() .
                "&" . Parameter::ERROR_CODE . "=" . $refundATMResponse->getErrorCode() .
                "&" . Parameter::TRANS_ID . "=" . $refundATMResponse->getTransId() .
                "&" . Parameter::MESSAGE . "=" . $refundATMResponse->getMessage() .
                "&" . Parameter::LOCAL_MESSAGE . "=" . $refundATMResponse->getLocalMessage() .
                "&" . Parameter::REQUEST_TYPE . "=" . $refundATMResponse->getRequestType();

            echo "getrefundATMResponse::partnerRawDataBeforeHash::" . $rawHash . "\n";
            $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());
            echo "getrefundATMResponse::partnerSignature::" . $signature . "\n";
            echo "getrefundATMResponse::momoSignature::" . $refundATMResponse->getSignature() . "\n";

            if ($signature != $refundATMResponse->getSignature())
                throw new MoMoException("Wrong signature from MoMo side - please contact with us");
        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
        return $refundATMResponse;
    }

}