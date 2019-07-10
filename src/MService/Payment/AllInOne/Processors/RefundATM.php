<?php


namespace MService\Payment\AllInOne\Processors;

use MService\Payment\AllInOne\Models\RefundATMRequest;
use MService\Payment\AllInOne\Models\RefundATMResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\Constants\RequestType;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\Utils\Converter;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;
use MService\Payment\Shared\Utils\Process;

class RefundATM extends Process
{
    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process(Environment $env, $orderId, $requestId, string $amount, $transId, $bankCode)
    {
        $refundATM = new RefundATM($env);

        try {
            $refundATMRequest = $refundATM->createRefundATMRequest($orderId, $requestId, $amount, $transId, $bankCode);
            $refundATMResponse = $refundATM->execute($refundATMRequest);

            return $refundATMResponse;

        } catch (MoMoException $exception) {
            $refundATM->logger->error($exception->getErrorMessage());
        }
    }

    public function createRefundATMRequest($orderId, $requestId, string $amount, $transId, $bankCode): RefundATMRequest
    {

        $rawData = Parameter::PARTNER_CODE . "=" . $this->getPartnerInfo()->getPartnerCode() .
            "&" . Parameter::ACCESS_KEY . "=" . $this->getPartnerInfo()->getAccessKey() .
            "&" . Parameter::REQUEST_ID . "=" . $requestId .
            "&" . Parameter::BANK_CODE . "=" . $bankCode .
            "&" . Parameter::AMOUNT . "=" . $amount .
            "&" . Parameter::ORDER_ID . "=" . $orderId .
            "&" . Parameter::TRANS_ID . "=" . $transId .
            "&" . Parameter::REQUEST_TYPE . "=" . RequestType::REFUND_ATM;

        $signature = Encoder::hashSha256($rawData, $this->getPartnerInfo()->getSecretKey());
        $this->logger->debug("[RefundATMRequest] rawData: " . $rawData
            . ", [Signature] -> " . $signature);

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

    public function execute($refundATMRequest)
    {
        try {
            $data = Converter::objectToJsonStrNoNull($refundATMRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), $data, $this->getLogger());

            if ($response->getStatusCode() != 200) {
                throw new MoMoException('[RefundATMRequest][' . $refundATMRequest->getOrderId() . '] -> Error API');
            }

            $refundATMResponse = new RefundATMResponse(json_decode($response->getBody(), true));

            return $this->checkResponse($refundATMResponse);

        } catch (MoMoException $exception) {
            $this->logger->error($exception->getErrorMessage());
        }
        return null;
    }

    public function checkResponse(RefundATMResponse $refundATMResponse)
    {
        try {

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

            $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());
            $this->logger->info("[RefundATMResponse] rawData: " . $rawHash
                . ", [Signature] -> " . $signature
                . ", [MoMoSignature] -> " . $refundATMResponse->getSignature());

            if ($signature != $refundATMResponse->getSignature())
                throw new MoMoException("Wrong signature from MoMo side - please contact with us");
        } catch (MoMoException $exception) {
            $this->logger->error('[RefundATMResponse][' . $refundATMResponse->getOrderId() . '] -> ' . $exception->getErrorMessage());
        }
        return $refundATMResponse;
    }

}