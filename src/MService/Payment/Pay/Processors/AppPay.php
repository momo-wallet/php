<?php


namespace MService\Payment\Pay\Processors;

use MService\Payment\Pay\Models\AppPayRequest;
use MService\Payment\Pay\Models\AppPayResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\Process;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;

class AppPay extends Process
{

    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process($env, $amount, $appData, $publicKey, $customerNumber, $partnerRefId, $version = 2.0, $payType = 3, $description = null,
                                   $partnerName = null, $partnerTransId = null, $storeId = null, $storeName = null)
    {
        try {
            echo '========================== START APP IN APP PAYMENT PROCESS ==================', "\n";

            $appPay = new AppPay($env);
            $appPayRequest = $appPay->createAppPayRequest($amount, $appData, $publicKey, $customerNumber, $partnerRefId, $version, $payType, $description,
                $partnerName, $partnerTransId, $storeId, $storeName);
            $appPayResponse = $appPay->execute($appPayRequest);

            echo '========================== END APP IN APP PAYMENT PROCESS ==================', "\n";
            return $appPayResponse;

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
    }

    public function createAppPayRequest($amount, $appData, $publicKey, $customerNumber, $partnerRefId, $version = 2.0, $payType = 3, $description = null,
                                        $partnerName = null, $partnerTransId = null, $storeId = null, $storeName = null): AppPayRequest
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

        echo 'createAppPayRequest::rawDataBeforeHash::', json_encode(array_filter($jsonArr, function ($var) {
            return !is_null($var);
        })), "\n";
        $hash = Encoder::encryptRSA($jsonArr, $publicKey);
        echo 'createAppPayRequest::hashRSA::' . $hash, "\n";

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

    public function execute(AppPayRequest $appPayRequest)
    {
        try {
            $data = json_encode($appPayRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), Parameter::PAY_APP_URI, $data);

            if ($response->getStatusCode() != 200) {
                throw new MoMoException("Error API");
            }

            $appPayResponse = new AppPayResponse(json_decode($response->getBody(), true));

            return $this->checkResponse($appPayResponse);
        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
        return null;
    }

    public function checkResponse(AppPayResponse $appPayResponse)
    {
        try {

            if ($appPayResponse->getStatus() != 0) {
                echo "getAppPayResponse::errorCode::", $appPayResponse->getStatus(), "\n";
                echo "getAppPayResponse::errorMessage::", $appPayResponse->getMessage(), "\n";
            }
            echo "getAppPayResponse::transid::", $appPayResponse->getTransid(), "\n";
            echo "getAppPayResponse::amount::", $appPayResponse->getAmount(), "\n";

            //check signature
            $rawHash = Parameter::STATUS . "=" . $appPayResponse->getStatus() .
                "&" . Parameter::MESSAGE . "=" . $appPayResponse->getMessage() .
                "&" . Parameter::AMOUNT . "=" . $appPayResponse->getAmount() .
                "&" . Parameter::PAY_TRANS_ID . "=" . $appPayResponse->getTransid();

            echo "getAppPayResponse::partnerRawDataBeforeHash::" . $rawHash . "\n";
            $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());
            echo "getAppPayResponse::partnerSignature::" . $signature . "\n";
            echo "getAppPayResponse::momoSignature::" . $appPayResponse->getSignature() . "\n";

            if ($signature == $appPayResponse->getSignature())
                return $appPayResponse;
            else
                throw new MoMoException("Wrong signature from MoMo side - please contact with us");
        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
        return null;
    }

}