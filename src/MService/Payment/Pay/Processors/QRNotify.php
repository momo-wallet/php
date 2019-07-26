<?php

namespace MService\Payment\Pay\Processors;

use MService\Payment\Pay\Models\QRNotificationRequest;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\Constants\RequestType;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\MoMoException;
use MService\Payment\Shared\Utils\Process;

class QRNotify extends Process
{
    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process(Environment $env, string $rawPostData)
    {
        $qrNotify = new QRNotify($env);
        $qrNotificationRequest = $qrNotify->getQRNotificationFromMoMo($rawPostData);

        header("Content-Type: application/json");

        if (is_null($qrNotificationRequest)) {
            http_response_code(400);
            header(' 400 Bad Request');
            $payload = json_encode(array("message" => "Bad Request"));

        } else {
            http_response_code(200);
            header( ' 200 OK');
            $payload = $qrNotify->execute($qrNotificationRequest);
        }

        $qrNotify->logger->debug('[QRNotifyResponse] -> ' . $payload);
        return $payload;
    }

    public function getQRNotificationFromMoMo(string $rawPostData)
    {

        try {
            $jsonArr = json_decode($rawPostData, true);
            $qrNotificationRequest = new QRNotificationRequest($jsonArr);

            if (RequestType::TRANS_TYPE_MOMO_WALLET != $qrNotificationRequest->getTransType()) {
                throw new MoMoException('[$QRNotificationRequest][' . $qrNotificationRequest->getMoMoTransId() . '] -> ' ."Wrong Order Type - Please contact MoMo");
            }
            if ($this->getPartnerInfo()->getPartnerCode() != $qrNotificationRequest->getPartnerCode()) {
                throw new MoMoException('[$QRNotificationRequest][' . $qrNotificationRequest->getMoMoTransId() . '] -> ' . "Wrong PartnerCode - Please contact MoMo");
            }
            if ($this->getPartnerInfo()->getAccessKey() != $qrNotificationRequest->getAccessKey()) {
                throw new MoMoException('[$QRNotificationRequest][' . $qrNotificationRequest->getMoMoTransId() . '] -> ' . "Wrong AccessKey - Please contact MoMo");
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

            $signature = Encoder::hashSha256($rawHash, $this->getPartnerInfo()->getSecretKey());
            $this->logger->debug("[QRNotify From MoMo] rawData: " . $rawHash
                . ', [Signature] -> ' . $signature
                . ', [MoMoSignature] -> ' . $qrNotificationRequest->getSignature());

            if ($signature != $qrNotificationRequest->getSignature()) {
                throw new MoMoException("Wrong Signature from MoMo Server");
            }

            return $qrNotificationRequest;

        } catch (MoMoException $exception) {
            $this->logger->error($exception->getErrorMessage());
        }
        return null;
    }

    public function execute($qrNotificationRequest): string
    {
        //create signature
        $rawHash = Parameter::AMOUNT . "=" . $qrNotificationRequest->getAmount() .
            "&" . Parameter::MESSAGE . "=" . $qrNotificationRequest->getMessage() .
            "&" . Parameter::MOMO_TRANS_ID . "=" . $qrNotificationRequest->getMomoTransId() .
            "&" . Parameter::PARTNER_REF_ID . "=" . $qrNotificationRequest->getPartnerRefId() .
            "&" . Parameter::STATUS . "=" . $qrNotificationRequest->getStatus();

        $signature = hash_hmac("sha256", $rawHash, $this->getPartnerInfo()->getSecretKey());

        $arr = array(
            Parameter::STATUS => $qrNotificationRequest->getStatus(),
            Parameter::MESSAGE => $qrNotificationRequest->getMessage(),
            Parameter::PARTNER_REF_ID => $qrNotificationRequest->getPartnerRefId(),
            Parameter::MOMO_TRANS_ID => $qrNotificationRequest->getMomoTransId(),
            Parameter::AMOUNT => $qrNotificationRequest->getAmount(),
            Parameter::SIGNATURE => $signature
        );

        $payload = json_encode($arr, JSON_UNESCAPED_UNICODE);
        return $payload;
    }

}
