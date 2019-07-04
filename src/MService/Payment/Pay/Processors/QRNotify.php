<?php


namespace MService\Payment\Pay\Processors;

use MService\Payment\Pay\Models\QRNotificationRequest;
use MService\Payment\Pay\Models\QRNotificationResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\Process;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;

class QRNotify extends Process
{
    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process(Environment $env, string $data)
    {
        try {
            echo '========================== START QR NOTIFICATION PROCESS ==================', "\n";
            $qrNotify = new QRNotify($env);
            $qrNotificationRequest = $qrNotify->getQRNotificationFromMoMo($data);

            if (is_null($qrNotificationRequest)) {
                throw new MoMoException('MoMo POST Request for QR Notification Payment is invalid');
            }
            $qrNotificationResponse = $qrNotify->execute($qrNotificationRequest);
            echo '========================== END QR NOTIFICATION PROCESS ==================', "\n";

            return $qrNotificationResponse;

        } catch (MoMoException $exception) {
            echo $exception->getErrorMessage();
        }
    }

    public function getQRNotificationFromMoMo(string $data)
    {
        try {
            $jsonArr = json_decode($data, true);
            $qrNotificationRequest = new QRNotificationRequest($jsonArr);

            $rawData = Parameter::ACCESS_KEY . "=" . $qrNotificationRequest->getAccessKey() .
                "&" . Parameter::AMOUNT . "=" . $qrNotificationRequest->getAmount() .
                "&" . Parameter::MESSAGE . "=" . $qrNotificationRequest->getMessage() .
                "&" . Parameter::MOMO_TRANS_ID . "=" . $qrNotificationRequest->getMomoTransId() .
                "&" . Parameter::PARTNER_CODE . "=" . $qrNotificationRequest->getPartnerCode() .
                "&" . Parameter::PARTNER_REF_ID . "=" . $qrNotificationRequest->getPartnerRefId() .
                "&" . Parameter::PARTNER_TRANS_ID . "=" . $qrNotificationRequest->getPartnerTransId() .
                "&" . Parameter::DATE . "=" . $qrNotificationRequest->getResponseTime() .
                "&" . Parameter::STATUS . "=" . $qrNotificationRequest->getStatus() .
                "&" . Parameter::STORE_ID . "=" . $qrNotificationRequest->getStoreId() .
                "&" . Parameter::TRANS_TYPE . "=" . $qrNotificationRequest->getTransType();

            echo 'getQRNotificationFromMoMo::rawDataBeforeHash::', $rawData, "\n";
            $signature = Encoder::hashSha256($rawData, $this->getPartnerInfo()->getSecretKey());
            echo 'getQRNotificationFromMoMo::signature::' . $signature, "\n";
            echo 'getQRNotificationFromMoMo::MoMoSignature::' . $qrNotificationRequest->getSignature(), "\n";

            if ($signature != $qrNotificationRequest->getSignature()) {
                throw new MoMoException("Wrong Signature from MoMo Server");
            }

            if ($qrNotificationRequest->getStatus() != 0) {
                echo "getQRNotificationFromMoMo::errorCode::", $qrNotificationRequest->getStatus(), "\n";
                echo "getQRNotificationFromMoMo::errorMessage::", $qrNotificationRequest->getMessage(), "\n";
            } else {
                echo "getQRNotificationFromMoMo::partnerRefId::", $qrNotificationRequest->getPartnerRefId(), "\n";
                echo "getQRNotificationFromMoMo::momoTransId::", $qrNotificationRequest->getMomoTransId(), "\n";
                echo "getQRNotificationFromMoMo::amount::", $qrNotificationRequest->getAmount(), "\n";
            }

            return $qrNotificationRequest;

        } catch (MoMoException $e) {
            echo $e->getMessage();
        }
        return null;
    }

    public function execute(QRNotificationRequest $qrNotificationRequest)
    {
        try {
            //check signature
            $rawHash = Parameter::AMOUNT . "=" . $qrNotificationRequest->getAmount() .
                "&" . Parameter::MESSAGE . "=" . $qrNotificationRequest->getMessage() .
                "&" . Parameter::MOMO_TRANS_ID . "=" . $qrNotificationRequest->getMomoTransId() .
                "&" . Parameter::PARTNER_REF_ID . "=" . $qrNotificationRequest->getPartnerRefId() .
                "&" . Parameter::STATUS . "=" . $qrNotificationRequest->getStatus();

            echo "sendQRNotificationResponseToMoMoServer::partnerRawDataBeforeHash::" . $rawHash . "\n";
            $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());
            echo "sendQRNotificationResponseToMoMoServer::partnerSignature::" . $signature . "\n";

            $arr = array(
                Parameter::STATUS => $qrNotificationRequest->getStatus(),
                Parameter::MESSAGE => $qrNotificationRequest->getMessage(),
                Parameter::PARTNER_REF_ID => $qrNotificationRequest->getPartnerRefId(),
                Parameter::MOMO_TRANS_ID => $qrNotificationRequest->getMomoTransId(),
                Parameter::AMOUNT => $qrNotificationRequest->getAmount(),
                Parameter::SIGNATURE => $signature
            );

            $qrNotificationResponse = new QRNotificationResponse($arr);
            $data = json_encode($qrNotificationResponse);

            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), Parameter::PAY_QR_CODE_URI, $data);

            if ($response->getStatusCode() != 200) {
                throw new MoMoException("Error API");
            }
            return $qrNotificationResponse;

        } catch (MoMoException $e) {
            echo $e->getErrorMessage();
        }

        return null;
    }

}