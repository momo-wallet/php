<?php

namespace MService\Payment\AllInOne\Processors;

use MService\Payment\AllInOne\Models\CaptureMoMoRequest;
use MService\Payment\AllInOne\Models\CaptureMoMoResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\Utils\Converter;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;
use MService\Payment\Shared\Utils\Process;

class CaptureMoMo extends Process
{
    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process(Environment $env, $orderId, $orderInfo, string $amount, $extraData, $requestId, $notifyUrl, $returnUrl)
    {
        $captureMoMoWallet = new CaptureMoMo($env);

        try {
            $captureMoMoRequest = $captureMoMoWallet->createCaptureMoMoRequest($orderId, $orderInfo, $amount, $extraData, $requestId, $notifyUrl, $returnUrl);
            $captureMoMoResponse = $captureMoMoWallet->execute($captureMoMoRequest);
            return $captureMoMoResponse;

        } catch (MoMoException $exception) {
            $captureMoMoWallet->logger->error($exception->getErrorMessage());
        }
    }

    public function createCaptureMoMoRequest($orderId, $orderInfo, string $amount, $extraData, $requestId, $notifyUrl, $returnUrl): CaptureMoMoRequest
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

        $signature = Encoder::hashSha256($rawData, $this->getPartnerInfo()->getSecretKey());

        $this->logger->debug('[CaptureMoMoRequest] rawData: ' . $rawData
            . ', [Signature] -> ' . $signature);

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

    public function execute($captureMoMoRequest)
    {
        try {
            $data = Converter::objectToJsonStrNoNull($captureMoMoRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), $data, $this->getLogger());

            if ($response->getStatusCode() != 200) {
                throw new MoMoException('[CaptureMoMoResponse][' . $captureMoMoRequest->getOrderId() . '] -> Error API');
            }

            $captureMoMoResponse = new CaptureMoMoResponse(json_decode($response->getBody(), true));

            return $this->checkResponse($captureMoMoResponse);

        } catch (MoMoException $e) {
            $this->logger->error($e->getErrorMessage());
        }
        return null;
    }

    public function checkResponse(CaptureMoMoResponse $captureMoMoResponse)
    {
        try {

            //check signature
            $rawHash = Parameter::REQUEST_ID . "=" . $captureMoMoResponse->getRequestId() .
                "&" . Parameter::ORDER_ID . "=" . $captureMoMoResponse->getOrderId() .
                "&" . Parameter::MESSAGE . "=" . $captureMoMoResponse->getMessage() .
                "&" . Parameter::LOCAL_MESSAGE . "=" . $captureMoMoResponse->getLocalMessage() .
                "&" . Parameter::PAY_URL . "=" . $captureMoMoResponse->getPayUrl() .
                "&" . Parameter::ERROR_CODE . "=" . $captureMoMoResponse->getErrorCode() .
                "&" . Parameter::REQUEST_TYPE . "=" . $captureMoMoResponse->getRequestType();

            $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());

            $this->logger->info("[CaptureMoMoResponse] rawData: " . $rawHash
                . ', [Signature] -> ' . $signature
                . ', [MoMoSignature] -> ' . $captureMoMoResponse->getSignature());

            if ($signature == $captureMoMoResponse->getSignature())
                return $captureMoMoResponse;
            else
                throw new MoMoException("Wrong signature from MoMo side - please contact with us");
        } catch (MoMoException $exception) {
            $this->logger->error('[CaptureMoMoResponse][' . $captureMoMoResponse->getOrderId() . '] -> ' . $exception->getErrorMessage());
        }
        return null;
    }

}