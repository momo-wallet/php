<?php

namespace MService\Payment\PayGate\Processors;

use MService\Payment\PayGate\Models\CaptureMoMoRequest;
use MService\Payment\PayGate\Models\CaptureMoMoResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\Process;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;

class CaptureMoMo extends Process
{
    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process($env, $orderId, $orderInfo, $amount, $extraData, $requestId, $notifyUrl, $returnUrl)
    {
        try {
            echo '========================== START CAPTURE MOMO WALLET ==================', "\n";

            $captureMoMoWallet = new CaptureMoMo($env);
            $captureMoMoRequest = $captureMoMoWallet->createCaptureMoMoRequest($orderId, $orderInfo, $amount, $extraData, $requestId, $notifyUrl, $returnUrl);
            $captureMoMoResponse = $captureMoMoWallet->execute($captureMoMoRequest);
            if (!is_null($captureMoMoResponse)) {

                if ($captureMoMoResponse->getErrorCode() != 0) {
                    echo "getCaptureMoMoResponse::errorCode::" . $captureMoMoResponse->getErrorCode() . "\n";
                    echo "getCaptureMoMoResponse::message::" . $captureMoMoResponse->getMessage() . "\n";
                    echo "getCaptureMoMoResponse::localMessage::" . $captureMoMoResponse->getLocalMessage() . "\n";
                } else {
                    echo "getCaptureMoMoResponse::payUrl::" . $captureMoMoResponse->getPayUrl() . "\n";
                }
            }
            echo '========================== END CAPTURE MOMO WALLET ==================', "\n";
            return $captureMoMoResponse;

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
    }

    public function createCaptureMoMoRequest($orderId, $orderInfo, $amount, $extraData, $requestId, $notifyUrl, $returnUrl): CaptureMoMoRequest
    {

        $rawData = Parameter::PARTNER_CODE . "=" . $this->getPartnerInfo()->getPartnerCode() .
            "&" . Parameter::ACCESS_KEY . "=" . $this->getPartnerInfo()->getAccessKey() .
            "&" . Parameter::REQUEST_ID . "=" . $requestId .
            "&" . Parameter::AMOUNT . "=" . $amount .
            "&" . Parameter::ORDER_ID . "=" . $orderId .
            "&" . Parameter::ORDER_INFO . "=" . $orderInfo .
            "&" . Parameter::RETURN_URL . "=" . $returnUrl .
            "&" . Parameter::NOTIFY_URL . "=" . $notifyUrl .
            "&" . Parameter::EXTRA_DATA . "=" . $extraData;

        echo 'createCaptureMoMoRequest::rawDataBeforeHash::', $rawData, "\n";
        $signature = Encoder::hashSha256($rawData, $this->getPartnerInfo()->getSecretKey());
        echo 'createCaptureMoMoRequest::signature::' . $signature, "\n";

        $arr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::ACCESS_KEY => $this->getPartnerInfo()->getAccessKey(),
            Parameter::REQUEST_ID => $requestId,
            Parameter::AMOUNT => $amount,
            Parameter::ORDER_ID => $orderId,
            Parameter::ORDER_INFO => $orderInfo,
            Parameter::RETURN_URL => $returnUrl,
            Parameter::NOTIFY_URL => $notifyUrl,
            Parameter::EXTRA_DATA => $extraData,
            Parameter::SIGNATURE => $signature,
        );

        return new CaptureMoMoRequest($arr);
    }

    public function execute(CaptureMoMoRequest $captureMoMoRequest)
    {
        try {
            $data = json_encode($captureMoMoRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), Parameter::PAY_GATE_URI, $data);

            if ($response->getStatusCode() != 200) {
                throw new MoMoException("Error API");
            }

            $captureMoMoResponse = new CaptureMoMoResponse(json_decode($response->getBody(), true));

            return $this->checkResponse($captureMoMoResponse);

        } catch (MoMoException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

    public function checkResponse(CaptureMoMoResponse $captureMoMoResponse)
    {
        try {
            Process::errorMoMoProcess($captureMoMoResponse->getErrorCode());

            //check signature
            $rawHash = Parameter::REQUEST_ID . "=" . $captureMoMoResponse->getRequestId() .
                "&" . Parameter::ORDER_ID . "=" . $captureMoMoResponse->getOrderId() .
                "&" . Parameter::MESSAGE . "=" . $captureMoMoResponse->getMessage() .
                "&" . Parameter::LOCAL_MESSAGE . "=" . $captureMoMoResponse->getLocalMessage() .
                "&" . Parameter::PAY_URL . "=" . $captureMoMoResponse->getPayUrl() .
                "&" . Parameter::ERROR_CODE . "=" . $captureMoMoResponse->getErrorCode() .
                "&" . Parameter::REQUEST_TYPE . "=" . $captureMoMoResponse->getRequestType();

            echo "getCaptureMoMoResponse::partnerRawDataBeforeHash::" . $rawHash . "\n";
            $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());
            echo "getCaptureMoMoResponse::partnerSignature::" . $signature . "\n";
            echo "getCaptureMoMoResponse::momoSignature::" . $captureMoMoResponse->getSignature() . "\n";

            if ($signature == $captureMoMoResponse->getSignature())
                return $captureMoMoResponse;
            else
                throw new MoMoException("Wrong signature from MoMo side - please contact with us");
        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
        return null;
    }

}