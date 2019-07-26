<?php

namespace MService\Payment\AllInOne\Processors;

use MService\Payment\AllInOne\Models\CaptureIPNRequest;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\Constants\RequestType;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\MoMoException;
use MService\Payment\Shared\Utils\Process;

class CaptureIPN extends Process
{
    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process(Environment $env, string $rawPostData)
    {
        $captureIPN = new CaptureIPN($env);
        $captureIPNRequest = $captureIPN->getIPNInformationFromMoMo($rawPostData);

        header("Content-Type: application/json;charset=UTF-8");

        if (is_null($captureIPNRequest)) {
            http_response_code(400);
            header( ' 400 Bad Request');
            $payload = json_encode(array("message" => "Bad Request"));

        } else {
            http_response_code(200);
            header( ' 200 OK');
            $payload = $captureIPN->execute($captureIPNRequest);
        }

        return $payload;
    }

    public function getIPNInformationFromMoMo(string $rawPostData)
    {
        parse_str($rawPostData, $result);
        $ipn = new CaptureIPNRequest($result);

        try {

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

            $signature = Encoder::hashSha256($rawData, $this->getPartnerInfo()->getSecretKey());

            $this->logger->debug('[CaptureMoMoIPNRequest] rawDataBeforeHash: ' . $rawData
                                            . ', [Signature] -> ' . $signature
                                            . ', [MoMoSignature] -> ' . $ipn->getSignature());
            if ($signature != $ipn->getSignature()) {
                throw new MoMoException("Wrong signature from MoMo side - please contact with us");
            }

            $this->logger->info('[CaptureMoMoIPNRequest] verifiedData: ' . $rawPostData);

            return $ipn;
        } catch (MoMoException $e) {
            $this->logger->error('[CaptureMoMoIPNRequest][' . $ipn->getOrderId() . '] -> ' .$e->getErrorMessage());
        }
        return null;
    }

    public function execute($request): string
    {
        //create signature
        $rawHash = Parameter::PARTNER_CODE . "=" . $request->getPartnerCode() .
            "&" . Parameter::ACCESS_KEY . "=" . $request->getAccessKey() .
            "&" . Parameter::REQUEST_ID . "=" . $request->getRequestId() .
            "&" . Parameter::ORDER_ID . "=" . $request->getOrderId() .
            "&" . Parameter::ERROR_CODE . "=" . $request->getErrorCode() .
            "&" . Parameter::MESSAGE . "=" . $request->getMessage() .
            "&" . Parameter::DATE . "=" . $request->getResponseTime() .
            "&" . Parameter::EXTRA_DATA . "=" . $request->getExtraData();

        $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());

        $this->logger->debug("[CaptureMoMoIPNResponse] rawData: " . $rawHash
            . ', [Signature] -> ' . $signature);

        $arr = array(
            Parameter::PARTNER_CODE => $request->getPartnerCode(),
            Parameter::ACCESS_KEY => $request->getAccessKey(),
            Parameter::REQUEST_ID => $request->getRequestId(),
            Parameter::ORDER_ID => $request->getOrderId(),
            Parameter::ERROR_CODE => $request->getErrorCode(),
            Parameter::MESSAGE => $request->getMessage(),
            Parameter::DATE => $request->getResponseTime(),
            Parameter::EXTRA_DATA => $request->getExtraData(),
            Parameter::SIGNATURE => $request->getSignature(),
        );

        $payload = json_encode($arr, JSON_UNESCAPED_UNICODE);
        return $payload;
    }

}
