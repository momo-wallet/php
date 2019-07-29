<?php


namespace MService\Payment\Pay\Processors;

use MService\Payment\Pay\Models\TransactionQueryRequest;
use MService\Payment\Pay\Models\TransactionQueryResponse;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\Constants\RequestType;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\Utils\Converter;
use MService\Payment\Shared\Utils\Encoder;
use MService\Payment\Shared\Utils\HttpClient;
use MService\Payment\Shared\Utils\MoMoException;
use MService\Payment\Shared\Utils\Process;

class TransactionQuery extends Process
{
    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
    }

    public static function process($env, $requestId, $publicKey, $partnerRefId, $momoTransId = null)
    {
        $transactionQuery = new TransactionQuery($env);

        try {
            $transactionQueryRequest = $transactionQuery->createTransactionQueryRequest($requestId, $publicKey, $partnerRefId, $momoTransId);
            $transactionQueryResponse = $transactionQuery->execute($transactionQueryRequest);
            return $transactionQueryResponse;

        } catch (MoMoException $exception) {
            $transactionQuery->logger->error($exception->getErrorMessage());
        }
    }

    public function createTransactionQueryRequest($requestId, $publicKey, $partnerRefId, $momoTransId = null): TransactionQueryRequest
    {

        $jsonArr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::PARTNER_REF_ID => $partnerRefId,
            Parameter::REQUEST_ID => $requestId,
            Parameter::MOMO_TRANS_ID => $momoTransId
        );

        $hash = Encoder::encryptRSA($jsonArr, $publicKey);
        $this->logger->debug("[TransactionQueryRequest] rawData: " . Converter::arrayToJsonStrNoNull($jsonArr)
            . ', [Signature] -> ' . $hash);

        $arr = array(
            Parameter::PARTNER_CODE => $this->getPartnerInfo()->getPartnerCode(),
            Parameter::PARTNER_REF_ID => $partnerRefId,
            Parameter::HASH => $hash,
            Parameter::VERSION => RequestType::VERSION,
            Parameter::MOMO_TRANS_ID => $momoTransId,
        );

        return new TransactionQueryRequest($arr);
    }

    public function execute($transactionQueryRequest)
    {
        try {
            $data = Converter::objectToJsonStrNoNull($transactionQueryRequest);
            $response = HttpClient::HTTPPost($this->getEnvironment()->getMomoEndpoint(), $data, $this->getLogger());

            if ($response->getStatusCode() != 200) {
                throw new MoMoException('[TransactionQueryRequest][' . $transactionQueryRequest->getPartnerRefId() . '] -> ' . "Error API");
            }

            $transactionQueryResponse = new TransactionQueryResponse(json_decode($response->getBody(), true));

            return $transactionQueryResponse;
        } catch (MoMoException $exception) {
            $this->logger->error($exception->getErrorMessage());
        }
        return null;
    }
}