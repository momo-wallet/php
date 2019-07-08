<?php


namespace MService\Payment\Pay\Processors;

use MService\Payment\Pay\Models\POSPayRequest;
use MService\Payment\Pay\Models\POSPayResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\Constants\RequestType;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\Utils\Converter;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;
use MService\Payment\Shared\Utils\Process;

class POSPay extends Process
{
    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public
    static function process($env, $paymentCode, $amount, $publicKey, $partnerRefId, $description = null, $storeId = null, $storeName = null)
    {
        $posPay = new POSPay($env);

        try {
            $posPayRequest = $posPay->createPOSPayRequest($paymentCode, $amount, $publicKey, $partnerRefId, $description, $storeId, $storeName);
            $posPayResponse = $posPay->execute($posPayRequest);
            return $posPayResponse;

        } catch (MoMoException $exception) {
            $posPay->logger->error($exception->getErrorMessage());
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

        $hash = Encoder::encryptRSA($jsonArr, $publicKey);
        $this->logger->info("[POSPayRequest] rawData: " . Converter::arrayToJsonStrNoNull($jsonArr)
            . ', [Signature] -> ' . $hash);

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
            $data = Converter::objectToJsonStrNoNull($posPayRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), $data, $this->getLogger());

            if ($response->getStatusCode() != 200) {
                throw new MoMoException("Error API");
            }

            $posPayResponse = new POSPayResponse(json_decode($response->getBody(), true));
            $this->logger->info($data);

            return $posPayResponse;
        } catch (MoMoException $exception) {
            $this->logger->error($exception->getErrorMessage());
        }
        return null;
    }
}