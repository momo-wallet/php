<?php


namespace MService\Payment\Pay\Processors;

use MService\Payment\Pay\Models\PaymentConfirmationRequest;
use MService\Payment\Pay\Models\PaymentConfirmationResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\Process;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;

class PaymentConfirmation extends Process
{

    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public function createPaymentConfirmationRequest($partnerRefId, $requestType, $momoTransId, $requestId,
                                                     $customerNumber = null, $description = null): PaymentConfirmationRequest
    {

        $rawData = Parameter::PARTNER_CODE . "=" . $this->getPartnerInfo()->getPartnerCode() .
            "&" . Parameter::PARTNER_REF_ID . "=" . $partnerRefId .
            "&" . Parameter::REQUEST_TYPE . "=" . $requestType .
            "&" . Parameter::REQUEST_ID . "=" . $requestId .
            "&" . Parameter::MOMO_TRANS_ID . "=" . $momoTransId;

        echo 'createPaymentConfirmationRequest::rawDataBeforeHash::', $rawData, "\n";
        $signature = Encoder::hashSha256($rawData, $this->getPartnerInfo()->getSecretKey());
        echo 'createPaymentConfirmationRequest::signature::' . $signature, "\n";

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

    public function execute(PaymentConfirmationRequest $paymentConfirmationRequest)
    {
        try {
            $data = json_encode($paymentConfirmationRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), Parameter::PAY_CONFIRMATION_URI, $data);

            if ($response->getStatusCode() != 200) {
                throw new MoMoException("Error API");
            }

            $paymentConfirmationResponse = new PaymentConfirmationResponse(json_decode($response->getBody(), true));

            return $this->checkResponse($paymentConfirmationResponse);

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
        return null;
    }

    public function checkResponse(PaymentConfirmationResponse $paymentConfirmationResponse)
    {
        try {

            if ($paymentConfirmationResponse->getStatus() != 0) {
                echo "getPaymentConfirmationResponse::errorCode::", $paymentConfirmationResponse->getStatus(), "\n";
                echo "getPaymentConfirmationResponse::errorMessage::", $paymentConfirmationResponse->getMessage(), "\n";
                return $paymentConfirmationResponse;

            } else {
                $data = $paymentConfirmationResponse->getData();

                echo "getPaymentConfirmationResponse::partnerCode::", $data->getPartnerCode(), "\n";
                echo "getPaymentConfirmationResponse::partnerRefId::", $data->getPartnerRefId(), "\n";
                echo "getPaymentConfirmationResponse::MoMoTransId::", $data->getMomoTransId(), "\n";
                echo "getPaymentConfirmationResponse::amount::", $data->getAmount(), "\n";


                $rawHash = Parameter::AMOUNT . "=" . $data->getAmount() .
                    "&" . Parameter::MOMO_TRANS_ID . "=" . $data->getMomoTransId() .
                    "&" . Parameter::PARTNER_CODE . "=" . $data->getPartnerCode() .
                    "&" . Parameter::PARTNER_REF_ID . "=" . $data->getPartnerRefId();

                echo "getPaymentConfirmationResponse::partnerRawDataBeforeHash::" . $rawHash . "\n";
                $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());
                echo "getPaymentConfirmationResponse::partnerSignature::" . $signature . "\n";
                echo "getPaymentConfirmationResponse::momoSignature::" . $paymentConfirmationResponse->getSignature() . "\n";

                if ($signature == $paymentConfirmationResponse->getSignature())
                    return $paymentConfirmationResponse;
                else
                    throw new MoMoException("Wrong signature from MoMo side - please contact with us");
            }

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
        return null;
    }

    public static function process($env, $partnerRefId, $requestType, $momoTransId, $requestId, $customerNumber = null, $description = null)
    {
        try {
            echo '========================== START PAYMENT CONFIRMATION PROCESS ==================', "\n";

            $paymentConfirmation = new PaymentConfirmation($env);
            $paymentConfirmationRequest = $paymentConfirmation->createPaymentConfirmationRequest($partnerRefId, $requestType, $momoTransId, $requestId, $customerNumber, $description);
            $paymentConfirmationResponse = $paymentConfirmation->execute($paymentConfirmationRequest);


            echo '========================== END PAYMENT CONFIRMATION PROCESS ==================', "\n";
            return $paymentConfirmationResponse;

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
    }
}