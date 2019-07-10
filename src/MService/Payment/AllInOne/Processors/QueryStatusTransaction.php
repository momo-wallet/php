<?php


namespace MService\Payment\AllInOne\Processors;

use MService\Payment\AllInOne\Models\QueryStatusRequest;
use MService\Payment\AllInOne\Models\QueryStatusResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\Constants\RequestType;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\Utils\Converter;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;
use MService\Payment\Shared\Utils\Process;

class QueryStatusTransaction extends Process
{
    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process(Environment $env, $orderId, $requestId)
    {
        $queryStatusTransaction = new QueryStatusTransaction($env);

        try {
            $queryStatusRequest = $queryStatusTransaction->createQueryStatusRequest($orderId, $requestId);
            $queryStatusResponse = $queryStatusTransaction->execute($queryStatusRequest);

            return $queryStatusResponse;

        } catch (MoMoException $exception) {
            $queryStatusTransaction->logger->error($exception->getErrorMessage());
        }
    }

    public function createQueryStatusRequest($orderId, $requestId): QueryStatusRequest
    {

        $rawData = Parameter::PARTNER_CODE . "=" . $this->getPartnerInfo()->getPartnerCode() .
            "&" . Parameter::ACCESS_KEY . "=" . $this->getPartnerInfo()->getAccessKey() .
            "&" . Parameter::REQUEST_ID . "=" . $requestId .
            "&" . Parameter::ORDER_ID . "=" . $orderId .
            "&" . Parameter::REQUEST_TYPE . "=" . RequestType::TRANSACTION_STATUS;

        $signature = Encoder::hashSha256($rawData, $this->getPartnerInfo()->getSecretKey());

        $this->logger->debug('[QueryStatusRequest] rawData: ' . $rawData
            . ', [Signature] -> ' . $signature);

        $arr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::ACCESS_KEY => $this->getPartnerInfo()->getAccessKey(),
            Parameter::REQUEST_ID => $requestId,
            Parameter::ORDER_ID => $orderId,
            Parameter::SIGNATURE => $signature,
        );

        return new QueryStatusRequest($arr);
    }

    public function execute($queryStatusRequest)
    {
        try {
            $data = Converter::objectToJsonStrNoNull($queryStatusRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), $data, $this->getLogger());

            if ($response->getStatusCode() != 200) {
                throw new MoMoException('[CaptureMoMoIPNRequest][' . $queryStatusRequest->getOrderId() . '] -> Error API');
            }

            $queryStatusResponse = new QueryStatusResponse(json_decode($response->getBody(), true));

            return $this->checkResponse($queryStatusResponse);

        } catch (MoMoException $exception) {
            $this->logger->error($exception->getErrorMessage());
        }
        return null;
    }

    public function checkResponse(QueryStatusResponse $queryStatusResponse)
    {
        try {

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

            $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());

            $this->logger->info("[QueryStatusResponse] rawData: " . $rawHash
                . ", [Signature] -> " . $signature
                . ", [MoMoSignature] -> " . $queryStatusResponse->getSignature());

            if ($signature == $queryStatusResponse->getSignature())
                return $queryStatusResponse;
            else
                throw new MoMoException("Wrong signature from MoMo side - please contact with us");
        } catch (MoMoException $exception) {
            $this->logger->error('[QueryStatusResponse][' . $queryStatusResponse->getOrderId() . '] -> ' . $exception->getErrorMessage());
        }
        return $queryStatusResponse;
    }

}