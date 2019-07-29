<?php


namespace MService\Payment\Pay\Processors;

use MService\Payment\Pay\Models\PaymentConfirmationRequest;
use MService\Payment\Pay\Models\PaymentConfirmationResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\Utils\Converter;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;
use MService\Payment\Shared\Utils\Process;

class PaymentConfirmation extends Process
{

    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process(Environment $env, $partnerRefId, $requestType, $momoTransId, $requestId, $customerNumber = null, $description = null)
    {
        $paymentConfirmation = new PaymentConfirmation($env);

        try {
            $paymentConfirmationRequest = $paymentConfirmation->createPaymentConfirmationRequest($partnerRefId, $requestType, $momoTransId, $requestId, $customerNumber, $description);
            $paymentConfirmationResponse = $paymentConfirmation->execute($paymentConfirmationRequest);
            return $paymentConfirmationResponse;

        } catch (MoMoException $exception) {
            $paymentConfirmation->logger->error($exception->getErrorMessage());
        }
    }

    public function createPaymentConfirmationRequest($partnerRefId, $requestType, $momoTransId, $requestId,
                                                     $customerNumber = null, $description = null): PaymentConfirmationRequest
    {

        $rawData = Parameter::PARTNER_CODE . "=" . $this->getPartnerInfo()->getPartnerCode() .
            "&" . Parameter::PARTNER_REF_ID . "=" . $partnerRefId .
            "&" . Parameter::REQUEST_TYPE . "=" . $requestType .
            "&" . Parameter::REQUEST_ID . "=" . $requestId .
            "&" . Parameter::MOMO_TRANS_ID . "=" . $momoTransId;

        $signature = Encoder::hashSha256($rawData, $this->getPartnerInfo()->getSecretKey());
        $this->logger->debug("[PayConfirmRequest] rawData: " . $rawData
            . ', [Signature] -> ' . $signature);

        $arr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::PARTNER_REF_ID => $partnerRefId,
            Parameter::REQUEST_TYPE => $requestType,
            Parameter::REQUEST_ID => $requestId,
            Parameter::MOMO_TRANS_ID => $momoTransId,
            Parameter::SIGNATURE => $signature,
            Parameter::CUSTOMER_NUMBER => $customerNumber,
            Parameter::DESCRIPTION => $description,
        );

        return new PaymentConfirmationRequest($arr);
    }

    public function execute($paymentConfirmationRequest)
    {
        try {
            $data = Converter::objectToJsonStrNoNull($paymentConfirmationRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), $data, $this->getLogger());

            if ($response->getStatusCode() != 200) {
                throw new MoMoException('[PayConfirmRequest][' . $paymentConfirmationRequest->getMomoTransId() . '] -> Error API');
            }

            $paymentConfirmationResponse = new PaymentConfirmationResponse(json_decode($response->getBody(), true));

            return $this->checkResponse($paymentConfirmationResponse);

        } catch (MoMoException $exception) {
            $this->logger->error($exception->getErrorMessage());
        }
        return null;
    }

    public function checkResponse(PaymentConfirmationResponse $paymentConfirmationResponse)
    {
        try {

            if ($paymentConfirmationResponse->getStatus() != 0) {
                return $paymentConfirmationResponse;

            } else {
                $data = $paymentConfirmationResponse->getData();

                $rawHash = Parameter::AMOUNT . "=" . $data->getAmount() .
                    "&" . Parameter::MOMO_TRANS_ID . "=" . $data->getMomoTransId() .
                    "&" . Parameter::PARTNER_CODE . "=" . $data->getPartnerCode() .
                    "&" . Parameter::PARTNER_REF_ID . "=" . $data->getPartnerRefId();

                $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());
                $this->logger->info("[PayConfirmResponse] rawData: " . $rawHash
                    . ', [Signature] -> ' . $signature
                    . ', [MoMoSignature] -> ' . $paymentConfirmationResponse->getSignature());

                if ($signature == $paymentConfirmationResponse->getSignature())
                    return $paymentConfirmationResponse;
                else
                    throw new MoMoException("Wrong signature from MoMo side - please contact with us");
            }

        } catch (MoMoException $exception) {
            $this->logger->error('[PaymentConfirmationResponse][' . $paymentConfirmationResponse->getData()->getPartnerRefId() . '] -> ' . $exception->getErrorMessage());
        }
        return null;
    }
}