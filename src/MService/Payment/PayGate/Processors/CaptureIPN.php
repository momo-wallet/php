<?php

namespace MService\Payment\PayGate\Processors;

use MService\Payment\PayGate\Models\CaptureIPNRequest;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\Constants\RequestType;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\Process;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\MoMoException;

class CaptureIPN extends Process
{
    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process(Environment $env, string $data)
    {
        echo '========================== START CAPTURE MOMO IPN PROCESS ==================', "\n";
        $captureIPN = new CaptureIPN($env);
        $captureIPNRequest = $captureIPN->getIPNInformationFromMoMo($data);
        
        header("Content-Type: application/json;charset=UTF-8");

        if (is_null($captureIPNRequest)) {
            http_response_code(400);
            header($_SERVER["SERVER_PROTOCOL"]. ' 400 Bad Request');
            $payload = json_encode(array("message"=>"Bad Request")); 
            
        } else {
            http_response_code(200);
            header($_SERVER["SERVER_PROTOCOL"]. ' 200 OK');
            $payload = $captureIPN->execute($captureIPNRequest);
        }
        
        echo $payload, "\n";
        echo '========================== END CAPTURE MOMO IPN PROCESS ==================', "\n";

        return $payload;
    }

    public function getIPNInformationFromMoMo(string $data)
    {
        try {
            parse_str($data, $result);
            $ipn = new CaptureIPNRequest($result);

            if (RequestType::TRANS_TYPE_MOMO_WALLET != $ipn->getOrderType()) {
                throw new MoMoException("Wrong Order Type - Please contact MoMo");
            }
            if ($this->getPartnerInfo()->getPartnerCode() != $ipn->getPartnerCode()) {
                throw new MoMoException("Wrong PartnerCode - Please contact MoMo");
            }
            if ($this->getPartnerInfo()->getAccessKey() != $ipn->getAccessKey()) {
                throw new MoMoException("Wrong AccessKey - Please contact MoMo");
            }

            //check signature
            $rawData = Parameter::PARTNER_CODE . "=" . $ipn->getPartnerCode() .
                    "&" . Parameter::ACCESS_KEY . "=" . $ipn->getAccessKey() .
                    "&" . Parameter::REQUEST_ID . "=" . $ipn->getRequestId() .
                    "&" . Parameter::AMOUNT . "=" . $ipn->getAmount() .
                    "&" . Parameter::ORDER_ID . "=" . $ipn->getOrderId() .
                    "&" . Parameter::ORDER_INFO . "=" . $ipn->getOrderInfo() .
                    "&" . Parameter::ORDER_TYPE . "=" . $ipn->getOrderType() .
                    "&" . Parameter::TRANS_ID . "=" . $ipn->getTransId() .
                    "&" . Parameter::MESSAGE . "=" . $ipn->getMessage() .
                    "&" . Parameter::LOCAL_MESSAGE . "=" . $ipn->getLocalMessage() .
                    "&" . Parameter::DATE . "=" . $ipn->getResponseTime() .
                    "&" . Parameter::ERROR_CODE . "=" . $ipn->getErrorCode() .
                    "&" . Parameter::PAY_TYPE . "=" . $ipn->getPayType() .
                    "&" . Parameter::EXTRA_DATA . "=" . $ipn->getExtraData();

            echo 'getIPNInformationFromMoMo::rawDataBeforeHash::', $rawData, "\n";
            $signature = Encoder::hashSha256($rawData, $this->getPartnerInfo()->getSecretKey());
            echo 'getIPNInformationFromMoMo::signature::' . $signature, "\n";
            echo 'getIPNInformationFromMoMo::MoMoSignature::' . $ipn->getSignature(), "\n";
            if ($signature != $ipn->getSignature()) {
                throw new MoMoException("Wrong signature from MoMo side - please contact with us");
            }

            if ($ipn->getErrorCode() != 0) {
                echo "getQRNotificationFromMoMo::errorCode::", $ipn->getErrorCode(), "\n";
                echo "getQRNotificationFromMoMo::errorMessage::", $ipn->getMessage(), "\n";
                echo "getQRNotificationFromMoMo::localMessage::", $ipn->getLocalMessage(), "\n";
            } else {
                echo "getQRNotificationFromMoMo::requestId::", $ipn->getRequestId(), "\n";
                echo "getQRNotificationFromMoMo::transId::", $ipn->getTransId(), "\n";
                echo "getQRNotificationFromMoMo::amount::", $ipn->getAmount(), "\n";
            }
            
            return $ipn;
        } catch (MoMoException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

    public function execute(CaptureIPNRequest $captureIPNRequest): string
    {
        //create signature
        $rawHash = Parameter::PARTNER_CODE . "=" . $captureIPNRequest->getPartnerCode() .
                "&" . Parameter::ACCESS_KEY . "=" . $captureIPNRequest->getAccessKey() .
                "&" . Parameter::REQUEST_ID . "=" . $captureIPNRequest->getRequestId() .
                "&" . Parameter::ORDER_ID . "=" . $captureIPNRequest->getOrderId() .
                "&" . Parameter::ERROR_CODE . "=" . $captureIPNRequest->getErrorCode() .
                "&" . Parameter::MESSAGE . "=" . $captureIPNRequest->getMessage() .
                "&" . Parameter::DATE . "=" . $captureIPNRequest->getResponseTime() .
                "&" . Parameter::EXTRA_DATA . "=" . $captureIPNRequest->getExtraData();

        echo "sendCaptureMoMoIPNResponseToMoMoServer::partnerRawDataBeforeHash::" . $rawHash . "\n";
        $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());
        echo "sendCaptureMoMoIPNResponseToMoMoServer::partnerSignature::" . $signature . "\n";

        $arr = array(
                        Parameter::PARTNER_CODE => $captureIPNRequest->getPartnerCode(),
                        Parameter::ACCESS_KEY => $captureIPNRequest->getAccessKey(),
                        Parameter::REQUEST_ID => $captureIPNRequest->getRequestId(),
                        Parameter::ORDER_ID => $captureIPNRequest->getOrderId(),
                        Parameter::ERROR_CODE => $captureIPNRequest->getErrorCode(),
                        Parameter::MESSAGE => $captureIPNRequest->getMessage(),
                        Parameter::DATE => $captureIPNRequest->getResponseTime(),
                        Parameter::EXTRA_DATA => $captureIPNRequest->getExtraData(),
                        Parameter::SIGNATURE => $captureIPNRequest->getSignature(),
                    );

        $payload = json_encode($arr);
        return $payload;
    }

}