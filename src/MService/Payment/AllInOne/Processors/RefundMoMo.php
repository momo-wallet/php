<?php


namespace MService\Payment\AllInOne\Processors;

use MService\Payment\AllInOne\Models\RefundMoMoRequest;
use MService\Payment\AllInOne\Models\RefundMoMoResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\Constants\RequestType;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\Utils\Converter;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;
use MService\Payment\Shared\Utils\Process;

class RefundMoMo extends Process
{
    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process(Environment $env, $orderId, $requestId, string $amount, $transId)
    {
        $refundMoMo = new RefundMoMo($env);

        try {
            $refundMoMoRequest = $refundMoMo->createRefundMoMoRequest($orderId, $requestId, $amount, $transId);
            $refundMoMoResponse = $refundMoMo->execute($refundMoMoRequest);

            return $refundMoMoResponse;

        } catch (MoMoException $exception) {
            $refundMoMo->logger->error($exception->getErrorMessage());
        }
    }

    public function createRefundMoMoRequest($orderId, $requestId, string $amount, $transId): RefundMoMoRequest
    {

        $rawData = Parameter::PARTNER_CODE . "=" . $this->getPartnerInfo()->getPartnerCode() .
            "&" . Parameter::ACCESS_KEY . "=" . $this->getPartnerInfo()->getAccessKey() .
            "&" . Parameter::REQUEST_ID . "=" . $requestId .
            "&" . Parameter::AMOUNT . "=" . $amount .
            "&" . Parameter::ORDER_ID . "=" . $orderId .
            "&" . Parameter::TRANS_ID . "=" . $transId .
            "&" . Parameter::REQUEST_TYPE . "=" . RequestType::REFUND_MOMO_WALLET;

        $signature = Encoder::hashSha256($rawData, $this->getPartnerInfo()->getSecretKey());
        $this->logger->debug("[RefundMoMoRequest] rawData: " . $rawData
            . ", [Signature] -> " . $signature);
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

    public function execute($refundMoMoRequest)
    {
        try {
            $data = Converter::objectToJsonStrNoNull($refundMoMoRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), $data, $this->getLogger());

            if ($response->getStatusCode() != 200) {
                throw new MoMoException('[RefundMoMoRequest][' . $refundMoMoRequest->getOrderId() . '] -> Error API');
            }

            $refundMoMoResponse = new RefundMoMoResponse(json_decode($response->getBody(), true));

            return $this->checkResponse($refundMoMoResponse);

        } catch (MoMoException $exception) {
            $this->logger->error($exception->getErrorMessage());
        }
        return null;
    }

    public function checkResponse(RefundMoMoResponse $refundMoMoResponse)
    {
        try {

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

            $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());
            $this->logger->info("[RefundMoMoResponse] rawData: " . $rawHash
                . ", [Signature] -> " . $signature
                . ", [MoMoSignature] -> " . $refundMoMoResponse->getSignature());

            if ($signature == $refundMoMoResponse->getSignature())
                return $refundMoMoResponse;
            else
                throw new MoMoException("Wrong signature from MoMo side - please contact with us");
        } catch (MoMoException $exception) {
            $this->logger->error('[RefundMoMoResponse][' . $refundMoMoResponse->getOrderId() . '] -> ' . $exception->getErrorMessage());
        }
        return $refundMoMoResponse;
    }
}