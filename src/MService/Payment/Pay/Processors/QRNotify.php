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

    public static function process(Environment $env, string $rawPostData)
    {
        echo '========================== START QR NOTIFICATION PROCESS ==================', "\n";
        $qrNotify = new QRNotify($env);
        $qrNotificationRequest = $qrNotify->getQRNotificationFromMoMo($rawPostData);

        header("Content-Type: application/json");

        if (is_null($qrNotificationRequest)) {
            http_response_code(400);
            header($_SERVER["SERVER_PROTOCOL"]. ' 400 Bad Request');
            $payload = json_encode(array("message"=>"Bad Request"));

        } else {
            http_response_code(200);
            header($_SERVER["SERVER_PROTOCOL"]. ' 200 OK');
            $payload = $qrNotify->execute($qrNotificationRequest);
        }

        echo $payload, "\n";
        echo '========================== END QR NOTIFICATION PROCESS ==================', "\n";
        return $payload;
    }

    public function getQRNotificationFromMoMo(string $rawPostData)
    {
        try {
            $jsonArr = json_decode($rawPostData, true);
            $qrNotificationRequest = new QRNotificationRequest($jsonArr);
            
            if (RequestType::TRANS_TYPE_MOMO_WALLET != $ipn->getTransType()) {
                throw new MoMoException("Wrong Order Type - Please contact MoMo");
            }
            if ($this->getPartnerInfo()->getPartnerCode() != $ipn->getPartnerCode()) {
                throw new MoMoException("Wrong PartnerCode - Please contact MoMo");
            }
            if ($this->getPartnerInfo()->getAccessKey() != $ipn->getAccessKey()) {
                throw new MoMoException("Wrong AccessKey - Please contact MoMo");
            }            

            $rawHash = Parameter::ACCESS_KEY . "=" . $qrNotificationRequest->getAccessKey() .
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

            echo 'getQRNotificationFromMoMo::rawDataBeforeHash::', $rawHash, "\n";
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

    public function execute(QRNotificationRequest $qrNotificationRequest) : string
    {
        //create signature
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

        $payload = json_encode($arr);
        return $payload;
    }

}
