<?php


namespace MService\Payment\Pay\Processors;

use MService\Payment\Pay\Models\POSPayRequest;
use MService\Payment\Pay\Models\POSPayResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\Constants\RequestType;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\Process;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;

class POSPay extends Process
{
    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public
    static function process($env, $paymentCode, $amount, $publicKey, $partnerRefId, $description = null, $storeId = null, $storeName = null)
    {
        try {
            echo '========================== START POS PAYMENT STATUS ==================', "\n";

            $posPay = new POSPay($env);
            $posPayRequest = $posPay->createPOSPayRequest($paymentCode, $amount, $publicKey, $partnerRefId, $description, $storeId, $storeName);
            $posPayResponse = $posPay->execute($posPayRequest);

            echo '========================== END POS PAYMENT STATUS ==================', "\n";
            return $posPayResponse;

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
    }

    public function createPOSPayRequest($paymentCode, $amount, $publicKey, $partnerRefId, $description = null, $storeId = null, $storeName = null): POSPayRequest
    {

        $jsonArr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::PARTNER_REF_ID => $partnerRefId,
            Parameter::AMOUNT => $amount,
            Parameter::PAYMENT_CODE => $paymentCode,
            Parameter::STORE_ID => $storeId,
            Parameter::STORE_NAME => $storeName
        );

        echo 'createPOSPayRequest::rawDataBeforeHash::', json_encode(array_filter($jsonArr, function ($var) {
            return !is_null($var);
        })), "\n";
        $hash = Encoder::encryptRSA($jsonArr, $publicKey);
        echo 'createPOSPayRequest::hashRSA::' . $hash, "\n";

        $arr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::PARTNER_REF_ID => $partnerRefId,
            Parameter::HASH => $hash,
            Parameter::VERSION => RequestType::VERSION,
            Parameter::PAY_TYPE => RequestType::APP_PAY_TYPE,
            Parameter::DESCRIPTION => $description,
        );

        return new POSPayRequest($arr);
    }

    public function execute(POSPayRequest $posPayRequest)
    {
        try {
            $data = json_encode($posPayRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), Parameter::PAY_POS_URI, $data);

            if ($response->getStatusCode() != 200) {
                throw new MoMoException("Error API");
            }

            $posPayResponse = new POSPayResponse(json_decode($response->getBody(), true));

            $data = $posPayResponse->getMessage();

            if ($posPayResponse->getStatus() != 0) {
                echo "getPOSPayResponse::errorCode::", $posPayResponse->getStatus(), "\n";
                echo "getPOSPayResponse::errorMessage::", $data->getDescription(), "\n";
            }

            echo "getPOSPayResponse::transid::", $data->getTransid(), "\n";
            echo "getPOSPayResponse::amount::", $data->getAmount(), "\n";
            echo "getPOSPayResponse::phoneNumber::", $data->getPhoneNumber(), "\n";

            return $posPayResponse;
        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
        return null;
    }
}