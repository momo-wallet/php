<?php

namespace MService\Payment\Pay\Processors;


use MService\Payment\Pay\Models\MoMoJson;
use MService\Payment\Pay\Models\TransactionQueryRequest;
use MService\Payment\Pay\Models\TransactionQueryResponse;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;
use MService\Payment\Shared\Utils\Converter;
use PHPUnit\Framework\TestCase;

class TransactionQueryTest extends TestCase
{
    public function test__construct()
    {
        $env = new Environment("teehee", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'testing');
        $query = new TransactionQuery($env);

        $this->assertInstanceOf(Environment::class, $query->getEnvironment(), "Wrong Data Type for TransactionQuery Environment");
        $this->assertInstanceOf(PartnerInfo::class, $query->getPartnerInfo(), "Wrong Data Type for TransactionQuery PartnerInfo");

        $this->assertEquals("teehee", $query->getEnvironment()->getMoMoEndpoint(), "Wrong MoMoEndpoint in TransactionQuery SetUp");
        $this->assertEquals($env->getTarget(), $query->getEnvironment()->getTarget(), "Wrong MoMoEndpoint in TransactionQuery SetUp");

    }

    public function testCreateTransactionQueryRequest()
    {
        $env = new Environment("https://test-payment.momo.vn/pay/query-status", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'development');
        $publicKey = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkpa+qMXS6O11x7jBGo9W3yxeHEsAdyDE
40UoXhoQf9K6attSIclTZMEGfq6gmJm2BogVJtPkjvri5/j9mBntA8qKMzzanSQaBEbr8FyByHnf
226dsLt1RbJSMLjCd3UC1n0Yq8KKvfHhvmvVbGcWfpgfo7iQTVmL0r1eQxzgnSq31EL1yYNMuaZj
pHmQuT24Hmxl9W9enRtJyVTUhwKhtjOSOsR03sMnsckpFT9pn1/V9BE2Kf3rFGqc6JukXkqK6ZW9
mtmGLSq3K+JRRq2w8PVmcbcvTr/adW4EL2yc1qk9Ec4HtiDhtSYd6/ov8xLVkKAQjLVt7Ex3/agR
PfPrNwIDAQAB
-----END PUBLIC KEY-----";

        $momoTransId = time() . "";
        $query = new TransactionQuery($env);
        $request = $query->createTransactionQueryRequest('1562138468', $publicKey, '1562138427', $momoTransId);
        $this->assertInstanceOf(TransactionQueryRequest::class, $request, "Wrong Data Type in createTransactionQueryRequest");

        $arr = Converter::objectToArray($request);
        $this->assertArrayHasKey('partnerCode', $arr, "Missing partnerCode Attribute in createTransactionQueryRequest");
        $this->assertArrayHasKey('partnerRefId', $arr, "Missing partnerRefId Attribute in createTransactionQueryRequest");
        $this->assertArrayHasKey('hash', $arr, "Missing hash Attribute in createTransactionQueryRequest");
        $this->assertArrayHasKey('version', $arr, "Missing version Attribute in createTransactionQueryRequest");

    }

    public function testProcessSuccessful()
    {
        $env = new Environment("https://test-payment.momo.vn/pay/query-status", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'development');
        $publicKey = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkpa+qMXS6O11x7jBGo9W3yxeHEsAdyDE
40UoXhoQf9K6attSIclTZMEGfq6gmJm2BogVJtPkjvri5/j9mBntA8qKMzzanSQaBEbr8FyByHnf
226dsLt1RbJSMLjCd3UC1n0Yq8KKvfHhvmvVbGcWfpgfo7iQTVmL0r1eQxzgnSq31EL1yYNMuaZj
pHmQuT24Hmxl9W9enRtJyVTUhwKhtjOSOsR03sMnsckpFT9pn1/V9BE2Kf3rFGqc6JukXkqK6ZW9
mtmGLSq3K+JRRq2w8PVmcbcvTr/adW4EL2yc1qk9Ec4HtiDhtSYd6/ov8xLVkKAQjLVt7Ex3/agR
PfPrNwIDAQAB
-----END PUBLIC KEY-----";

        $response = TransactionQuery::process($env, '1562138468', $publicKey, '1562138427');
        $this->assertInstanceOf(TransactionQueryResponse::class, $response, "Wrong Data Type in execute in TransactionQueryProcess");

        $arr = Converter::objectToArray($response);
        $this->assertArrayHasKey('status', $arr, "Missing status Attribute in TransactionQueryProcess");
        $this->assertArrayHasKey('message', $arr, "Missing message Attribute in TransactionQueryProcess");
        $this->assertArrayHasKey('data', $arr, "Missing data Attribute in TransactionQueryProcess");
        $this->assertInstanceOf(MoMoJson::class, $response->getData(), "Wrong Data Type for data Attribute in TransactionQueryProcess -- Must be Json");

        $jsonArr = Converter::objectToArray($response->getData());
        $this->assertArrayHasKey('message', $jsonArr, "Missing message Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('status', $jsonArr, "Missing status Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('amount', $jsonArr, "Missing amount Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('transid', $jsonArr, "Missing transid Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('partnerCode', $jsonArr, "Missing partnerCode Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('billId', $jsonArr, "Missing billId Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('discountAmount', $jsonArr, "Missing discountAmount Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('fee', $jsonArr, "Missing fee Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('phoneNumber', $jsonArr, "Missing phoneNumber Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('customerName', $jsonArr, "Missing customerName Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('storeId', $jsonArr, "Missing storeId Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('requestDate', $jsonArr, "Missing requestDate Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('responseDate', $jsonArr, "Missing responseDate Attribute in JSON TransactionQueryProcess");

        $this->assertEquals(0, $response->getStatus(), "Wrong Response Body from MoMo for TransactionQueryResponse");
    }

    public function testProcessFailure()
    {
        $env = new Environment("https://test-payment.momo.vn/pay/query-status", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'development');
        $publicKey = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkpa+qMXS6O11x7jBGo9W3yxeHEsAdyDE
40UoXhoQf9K6attSIclTZMEGfq6gmJm2BogVJtPkjvri5/j9mBntA8qKMzzanSQaBEbr8FyByHnf
226dsLt1RbJSMLjCd3UC1n0Yq8KKvfHhvmvVbGcWfpgfo7iQTVmL0r1eQxzgnSq31EL1yYNMuaZj
pHmQuT24Hmxl9W9enRtJyVTUhwKhtjOSOsR03sMnsckpFT9pn1/V9BE2Kf3rFGqc6JukXkqK6ZW9
mtmGLSq3K+JRRq2w8PVmcbcvTr/adW4EL2yc1qk9Ec4HtiDhtSYd6/ov8xLVkKAQjLVt7Ex3/agR
PfPrNwIDAQAB
-----END PUBLIC KEY-----";

        $response = TransactionQuery::process($env, time() . '', $publicKey, time() . '');
        $this->assertInstanceOf(TransactionQueryResponse::class, $response, "Wrong Data Type in execute in TransactionQueryProcess");

        $arr = Converter::objectToArray($response);
        $this->assertArrayHasKey('status', $arr, "Missing status Attribute in TransactionQueryProcess");
        $this->assertArrayHasKey('message', $arr, "Missing message Attribute in TransactionQueryProcess");
        $this->assertArrayHasKey('data', $arr, "Missing data Attribute in TransactionQueryProcess");
        $this->assertInstanceOf(MoMoJson::class, $response->getData(), "Wrong Data Type for data Attribute in TransactionQueryProcess -- Must be Json");

        $jsonArr = Converter::objectToArray($response->getData());
        $this->assertArrayHasKey('message', $jsonArr, "Missing message Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('status', $jsonArr, "Missing status Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('amount', $jsonArr, "Missing amount Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('transid', $jsonArr, "Missing transid Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('partnerCode', $jsonArr, "Missing partnerCode Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('billId', $jsonArr, "Missing billId Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('discountAmount', $jsonArr, "Missing discountAmount Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('fee', $jsonArr, "Missing fee Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('phoneNumber', $jsonArr, "Missing phoneNumber Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('customerName', $jsonArr, "Missing customerName Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('storeId', $jsonArr, "Missing storeId Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('requestDate', $jsonArr, "Missing requestDate Attribute in JSON TransactionQueryProcess");
        $this->assertArrayHasKey('responseDate', $jsonArr, "Missing responseDate Attribute in JSON TransactionQueryProcess");


        $this->assertEquals(0, $response->getStatus(), "Wrong Response Body from MoMo for TransactionQueryResponse");
        $this->assertEmpty($response->getData()->getStatus(), "Wrong Response Body from MoMo for TransactionQueryResponse");

    }

}
