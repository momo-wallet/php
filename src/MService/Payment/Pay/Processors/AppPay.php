<?php


namespace MService\Payment\Pay\Processors;

use MService\Payment\Pay\Models\AppPayRequest;
use MService\Payment\Pay\Models\AppPayResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\Utils\Converter;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;
use MService\Payment\Shared\Utils\Process;

class AppPay extends Process
{

    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process(Environment $env, int $amount, $appData, $publicKey, $customerNumber, $partnerRefId, $version = 2.0, $payType = 3, $description = '',
                                   $partnerName = '', $partnerTransId = '', $storeId = '', $storeName = '')
    {
        $appPay = new AppPay($env);

        try {
            $appPayRequest = $appPay->createAppPayRequest($amount, $appData, $publicKey, $customerNumber, $partnerRefId, $version, $payType, $description, $partnerName, $partnerTransId, $storeId, $storeName);
            $appPayResponse = $appPay->execute($appPayRequest);
            return $appPayResponse;

        } catch (MoMoException $exception) {
            $appPay->logger->error($exception->getErrorMessage());
        }
    }

    public function createAppPayRequest(int $amount, $appData, $publicKey, $customerNumber, $partnerRefId, $version = 2.0, $payType = 3, $description = '',
                                        $partnerName = '', $partnerTransId = '', $storeId = '', $storeName = ''): AppPayRequest
    {

        $jsonArr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::PARTNER_REF_ID => $partnerRefId,
            Parameter::AMOUNT => $amount,
            Parameter::PARTNER_NAME => $partnerName,
            Parameter::PARTNER_TRANS_ID => $partnerTransId,
            Parameter::STORE_ID => $storeId,
            Parameter::STORE_NAME => $storeName
        );

        $hash = Encoder::encryptRSA($jsonArr, $publicKey);
        $this->logger->debug("[AppPayRequest] rawData: " . Converter::arrayToJsonStrNoNull($jsonArr)
            . ', [Signature] -> ' . $hash);

        $arr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::PARTNER_REF_ID => $partnerRefId,
            Parameter::CUSTOMER_NUMBER => $customerNumber,
            Parameter::APP_DATA => $appData,
            Parameter::HASH => $hash,
            Parameter::VERSION => $version,
            Parameter::PAY_TYPE => $payType,
            Parameter::DESCRIPTION => $description,
        );

        return new AppPayRequest($arr);
    }

    public function execute($appPayRequest)
    {
        try {
            $data = Converter::objectToJsonStrNoNull($appPayRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), $data, $this->getLogger());

            if ($response->getStatusCode() != 200) {
                throw new MoMoException('[AppPayRequest][' . $appPayRequest->getPartnerRefId() . '] -> Error API');
            }

            $appPayResponse = new AppPayResponse(json_decode($response->getBody(), true));

            return $this->checkResponse($appPayResponse);
        } catch (MoMoException $exception) {
            $this->logger->error($exception->getErrorMessage());
        }
        return null;
    }

    public function checkResponse(AppPayResponse $appPayResponse)
    {
        try {

            //check signature
            $rawHash = Parameter::STATUS . "=" . $appPayResponse->getStatus() .
                "&" . Parameter::MESSAGE . "=" . $appPayResponse->getMessage() .
                "&" . Parameter::AMOUNT . "=" . $appPayResponse->getAmount() .
                "&" . Parameter::PAY_TRANS_ID . "=" . $appPayResponse->getTransid();

            $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());
            $this->logger->info("[AppPayResponse] rawData: " . $rawHash
                . ', [Signature] -> ' . $signature
                . ', [MoMoSignature] -> ' . $appPayResponse->getSignature());

            if ($appPayResponse->getSignature() == null || $signature == $appPayResponse->getSignature())
                return $appPayResponse;
            else
                throw new MoMoException("Wrong signature from MoMo side - please contact with us");
        } catch (MoMoException $exception) {
            $this->logger->error('[AppPayResponse] -> ' . $exception->getErrorMessage());
        }
        return null;
    }

}