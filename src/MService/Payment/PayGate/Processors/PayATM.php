<?php


namespace MService\Payment\PayGate\Processors;

use MService\Payment\PayGate\Models\PayATMRequest;
use MService\Payment\PayGate\Models\PayATMResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\Constants\RequestType;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\Process;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;

class PayATM extends Process
{

    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process($env, $orderId, $orderInfo, $amount, $extraData, $requestId, $notifyUrl, $returnUrl, $bankCode)
    {
        try {
            echo '========================== START ATM PAYMENT ==================', "\n";

            $payATM = new PayATM($env);
            $payATMRequest = $payATM->createPayATMRequest($orderId, $orderInfo, $amount, $extraData, $requestId, $notifyUrl, $returnUrl, $bankCode);
            $payATMResponse = $payATM->execute($payATMRequest);

            if (!is_null($payATMResponse)) {
                if ($payATMResponse->getErrorCode() != 0) {
                    echo "getPayATMMoMoResponse::errorCode::" . $payATMResponse->getErrorCode() . "\n";
                    echo "getPayATMMoMoResponse::message::" . $payATMResponse->getMessage() . "\n";
                    echo "getPayATMMoMoResponse::localMessage::" . $payATMResponse->getLocalMessage() . "\n";
                } else {
                    echo "getPayATMMoMoResponse::payUrl::" . $payATMResponse->getPayUrl() . "\n";
                }
            }

            echo '========================== END ATM PAYMENT ==================', "\n";
            return $payATMResponse;

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
    }

    public function createPayATMRequest($orderId, $orderInfo, $amount, $extraData, $requestId, $notifyUrl, $returnUrl, $bankCode): PayATMRequest
    {

        $rawData = Parameter::PARTNER_CODE . "=" . $this->getPartnerInfo()->getPartnerCode() .
            "&" . Parameter::ACCESS_KEY . "=" . $this->getPartnerInfo()->getAccessKey() .
            "&" . Parameter::REQUEST_ID . "=" . $requestId .
            "&" . Parameter::BANK_CODE . "=" . $bankCode .
            "&" . Parameter::AMOUNT . "=" . $amount .
            "&" . Parameter::ORDER_ID . "=" . $orderId .
            "&" . Parameter::ORDER_INFO . "=" . $orderInfo .
            "&" . Parameter::RETURN_URL . "=" . $returnUrl .
            "&" . Parameter::NOTIFY_URL . "=" . $notifyUrl .
            "&" . Parameter::EXTRA_DATA . "=" . $extraData .
            "&" . Parameter::REQUEST_TYPE . "=" . RequestType::PAY_WITH_ATM;

        echo 'createPayATMRequest::rawDataBeforeHash::', $rawData, "\n";
        $signature = Encoder::hashSha256($rawData, $this->getPartnerInfo()->getSecretKey());
        echo 'createPayATMRequest::signature::' . $signature, "\n";

        $arr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::ACCESS_KEY => $this->getPartnerInfo()->getAccessKey(),
            Parameter::REQUEST_ID => $requestId,
            Parameter::AMOUNT => $amount,
            Parameter::ORDER_ID => $orderId,
            Parameter::ORDER_INFO => $orderInfo,
            Parameter::RETURN_URL => $returnUrl,
            Parameter::NOTIFY_URL => $notifyUrl,
            Parameter::BANK_CODE => $bankCode,
            Parameter::SIGNATURE => $signature,
            Parameter::EXTRA_DATA => $extraData,
        );

        return new PayATMRequest($arr);
    }

    public function execute(PayATMRequest $payATMRequest)
    {
        try {
            $data = json_encode($payATMRequest);

            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), Parameter::PAY_GATE_URI, $data);

            if ($response->getStatusCode() != 200) {
                throw new MoMoException("Error API");
            }

            $payATMResponse = new PayATMResponse(json_decode($response->getBody(), true));
            Process::errorMoMoProcess($payATMResponse->getErrorCode());

            return $this->checkResponse($payATMResponse);

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
    }

    public function checkResponse(PayATMResponse $payATMResponse)
    {
        try {

            //check signature
            $rawHash = Parameter::PARTNER_CODE . "=" . $payATMResponse->getPartnerCode() .
                "&" . Parameter::ACCESS_KEY . "=" . $payATMResponse->getAccessKey() .
                "&" . Parameter::REQUEST_ID . "=" . $payATMResponse->getRequestId() .
                "&" . Parameter::PAY_URL . "=" . $payATMResponse->getPayUrl() .
                "&" . Parameter::ERROR_CODE . "=" . $payATMResponse->getErrorCode() .
                "&" . Parameter::ORDER_ID . "=" . $payATMResponse->getOrderId() .
                "&" . Parameter::MESSAGE . "=" . $payATMResponse->getMessage() .
                "&" . Parameter::LOCAL_MESSAGE . "=" . $payATMResponse->getLocalMessage() .
                "&" . Parameter::REQUEST_TYPE . "=" . $payATMResponse->getRequestType();

            echo "getPayATMMoMoResponse::partnerRawDataBeforeHash::" . $rawHash . "\n";
            $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());
            echo "getPayATMMoMoResponse::partnerSignature::" . $signature . "\n";
            echo "getPayATMMoMoResponse::momoSignature::" . $payATMResponse->getSignature() . "\n";

            if ($signature == $payATMResponse->getSignature())
                return $payATMResponse;
            else
                throw new MoMoException("Wrong signature from MoMo side - please contact with us");
        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
        return $payATMResponse;
    }
}