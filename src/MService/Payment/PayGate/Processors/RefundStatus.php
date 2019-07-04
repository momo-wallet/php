<?php


namespace MService\Payment\PayGate\Processors;

use MService\Payment\PayGate\Models\RefundStatusRequest;
use MService\Payment\PayGate\Models\RefundStatusResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\Constants\RequestType;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\Process;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;

class RefundStatus extends Process
{

    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process($env, $orderId, $requestId)
    {
        try {
            echo '========================== START REFUND QUERY STATUS ==================', "\n";

            $refundStatus = new RefundStatus($env);
            $refundStatusRequest = $refundStatus->createRefundStatusRequest($orderId, $requestId);
            $refundStatusResponse = $refundStatus->execute($refundStatusRequest);

            echo '========================== END REFUND QUERY STATUS ==================', "\n";
            return $refundStatusResponse;

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
    }

    public function createRefundStatusRequest($orderId, $requestId): RefundStatusRequest
    {

        $rawData = Parameter::PARTNER_CODE . "=" . $this->getPartnerInfo()->getPartnerCode() .
            "&" . Parameter::ACCESS_KEY . "=" . $this->getPartnerInfo()->getAccessKey() .
            "&" . Parameter::REQUEST_ID . "=" . $requestId .
            "&" . Parameter::ORDER_ID . "=" . $orderId .
            "&" . Parameter::REQUEST_TYPE . "=" . RequestType::QUERY_REFUND;

        echo 'createRefundStatusRequest::rawDataBeforeHash::', $rawData, "\n";
        $signature = Encoder::hashSha256($rawData, $this->getPartnerInfo()->getSecretKey());
        echo 'createRefundStatusRequest::signature::' . $signature, "\n";

        $arr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::ACCESS_KEY => $this->getPartnerInfo()->getAccessKey(),
            Parameter::REQUEST_ID => $requestId,
            Parameter::ORDER_ID => $orderId,
            Parameter::SIGNATURE => $signature,
        );

        return new RefundStatusRequest($arr);
    }

    public function execute(RefundStatusRequest $refundStatusRequest) : array
    {
        try {
            $data = json_encode($refundStatusRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), Parameter::PAY_GATE_URI, $data);

            if ($response->getStatusCode() != 200) {
                throw new MoMoException("Error API");
            }

            $arrObj = json_decode($response->getBody(), true);
            $arrResponse = [];

            foreach ($arrObj as $arr) {
                $refundStatusResponse = new RefundStatusResponse($arr);
                $refundStatusResponse = $this->checkResponse($refundStatusResponse);
                if (get_class($refundStatusResponse) === RefundStatusResponse::class)
                    $arrResponse[] = $refundStatusResponse;
            }

            return $arrResponse;

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
        return null;
    }

    public function checkResponse(RefundStatusResponse $refundStatusResponse)
    {
        try {

            Process::errorMoMoProcess($refundStatusResponse->getErrorCode());

            //check signature
            $rawHash = Parameter::PARTNER_CODE . "=" . $refundStatusResponse->getPartnerCode() .
                "&" . Parameter::ACCESS_KEY . "=" . $refundStatusResponse->getAccessKey() .
                "&" . Parameter::REQUEST_ID . "=" . $refundStatusResponse->getRequestId() .
                "&" . Parameter::ORDER_ID . "=" . $refundStatusResponse->getOrderId() .
                "&" . Parameter::ERROR_CODE . "=" . $refundStatusResponse->getErrorCode() .
                "&" . Parameter::TRANS_ID . "=" . $refundStatusResponse->getTransId() .
                "&" . Parameter::AMOUNT . "=" . $refundStatusResponse->getAmount() .
                "&" . Parameter::MESSAGE . "=" . $refundStatusResponse->getMessage() .
                "&" . Parameter::LOCAL_MESSAGE . "=" . $refundStatusResponse->getLocalMessage() .
                "&" . Parameter::REQUEST_TYPE . "=" . $refundStatusResponse->getRequestType();

            echo '============================================', "\n";

            echo "getrefundStatusResponse::partnerRawDataBeforeHash::" . $rawHash . "\n";
            $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());
            echo "getrefundStatusResponse::partnerSignature::" . $signature . "\n";
            echo "getrefundStatusResponse::momoSignature::" . $refundStatusResponse->getSignature() . "\n";

            if ($refundStatusResponse->getErrorCode() != 0) {
                echo "errorCode::", $refundStatusResponse->getErrorCode(), "\n";
                echo "errorMessage::", $refundStatusResponse->getMessage(), "\n";
                echo "localMessage::", $refundStatusResponse->getLocalMessage(), "\n";
            }

            echo '============================================', "\n";

            if ($signature == $refundStatusResponse->getSignature())
                return $refundStatusResponse;
            else
                throw new MoMoException("Wrong signature from MoMo side - please contact with us");
        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
        return null;
    }
}