<?php


namespace MService\Payment\PayGate\Processors;

use MService\Payment\PayGate\Models\RefundMoMoRequest;
use MService\Payment\PayGate\Models\RefundMoMoResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\Constants\RequestType;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\Process;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;

class RefundMoMo extends Process
{
    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process($env, $orderId, $requestId, $amount, $transId)
    {
        try {
            echo '========================== START MOMO REFUND PROCESS ==================', "\n";

            $refundMoMo = new RefundMoMo($env);
            $refundMoMoRequest = $refundMoMo->createRefundMoMoRequest($orderId, $requestId, $amount, $transId);
            $refundMoMoResponse = $refundMoMo->execute($refundMoMoRequest);

            if (!is_null($refundMoMoResponse) && $refundMoMoResponse->getErrorCode() != 0) {
                echo "errorCode::", $refundMoMoResponse->getErrorCode(), "\n";
                echo "errorMessage::", $refundMoMoResponse->getMessage(), "\n";
                echo "localMessage::", $refundMoMoResponse->getLocalMessage(), "\n";
            }

            echo '========================== END MOMO REFUND PROCESS ==================', "\n";
            return $refundMoMoResponse;

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
    }

    public function createRefundMoMoRequest($orderId, $requestId, $amount, $transId): RefundMoMoRequest
    {

        $rawData = Parameter::PARTNER_CODE . "=" . $this->getPartnerInfo()->getPartnerCode() .
            "&" . Parameter::ACCESS_KEY . "=" . $this->getPartnerInfo()->getAccessKey() .
            "&" . Parameter::REQUEST_ID . "=" . $requestId .
            "&" . Parameter::AMOUNT . "=" . $amount .
            "&" . Parameter::ORDER_ID . "=" . $orderId .
            "&" . Parameter::TRANS_ID . "=" . $transId .
            "&" . Parameter::REQUEST_TYPE . "=" . RequestType::REFUND_MOMO_WALLET;

        echo 'createRefundMoMoRequest::rawDataBeforeHash::', $rawData, "\n";
        $signature = Encoder::hashSha256($rawData, $this->getPartnerInfo()->getSecretKey());
        echo 'createRefundMoMoRequest::signature::' . $signature, "\n";

        $arr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::ACCESS_KEY => $this->getPartnerInfo()->getAccessKey(),
            Parameter::REQUEST_ID => $requestId,
            Parameter::AMOUNT => $amount,
            Parameter::ORDER_ID => $orderId,
            Parameter::TRANS_ID => $transId,
            Parameter::SIGNATURE => $signature,
        );

        return new RefundMoMoRequest($arr);
    }

    public function execute(RefundMoMoRequest $refundMoMoRequest)
    {
        try {
            $data = json_encode($refundMoMoRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), Parameter::PAY_GATE_URI, $data);

            if ($response->getStatusCode() != 200) {
                throw new MoMoException("Error API");
            }

            $refundMoMoResponse = new RefundMoMoResponse(json_decode($response->getBody(), true));

            return $this->checkResponse($refundMoMoResponse);

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
        return null;
    }

    public function checkResponse(RefundMoMoResponse $refundMoMoResponse)
    {
        try {
            Process::errorMoMoProcess($refundMoMoResponse->getErrorCode());

            //check signature
            $rawHash = Parameter::PARTNER_CODE . "=" . $refundMoMoResponse->getPartnerCode() .
                "&" . Parameter::ACCESS_KEY . "=" . $refundMoMoResponse->getAccessKey() .
                "&" . Parameter::REQUEST_ID . "=" . $refundMoMoResponse->getRequestId() .
                "&" . Parameter::ORDER_ID . "=" . $refundMoMoResponse->getOrderId() .
                "&" . Parameter::ERROR_CODE . "=" . $refundMoMoResponse->getErrorCode() .
                "&" . Parameter::TRANS_ID . "=" . $refundMoMoResponse->getTransId() .
                "&" . Parameter::MESSAGE . "=" . $refundMoMoResponse->getMessage() .
                "&" . Parameter::LOCAL_MESSAGE . "=" . $refundMoMoResponse->getLocalMessage() .
                "&" . Parameter::REQUEST_TYPE . "=" . $refundMoMoResponse->getRequestType();

            echo "getrefundMoMoResponse::partnerRawDataBeforeHash::" . $rawHash . "\n";
            $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());
            echo "getrefundMoMoResponse::partnerSignature::" . $signature . "\n";
            echo "getrefundMoMoResponse::momoSignature::" . $refundMoMoResponse->getSignature() . "\n";

            if ($signature == $refundMoMoResponse->getSignature())
                return $refundMoMoResponse;
            else
                throw new MoMoException("Wrong signature from MoMo side - please contact with us");
        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
        return $refundMoMoResponse;
    }
}