<?php

namespace MService\Payment\Pay\Processors;


use MService\Payment\Pay\Models\MoMoJson;
use MService\Payment\Pay\Models\POSPayRequest;
use MService\Payment\Pay\Models\POSPayResponse;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;
use MService\Payment\Shared\Utils\Converter;
use PHPUnit\Framework\TestCase;

class POSPayTest extends TestCase
{

    public function test__construct()
    {
        $env = new Environment("teehee", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'testing');
        $posPay = new POSPay($env);

        $this->assertInstanceOf(Environment::class, $posPay->getEnvironment(), "Wrong Data Type for POSPayment Environment");
        $this->assertInstanceOf(PartnerInfo::class, $posPay->getPartnerInfo(), "Wrong Data Type for POSPayment PartnerInfo");

        $this->assertEquals("teehee", $posPay->getEnvironment()->getMoMoEndpoint(), "Wrong MoMoEndpoint in POSPayment SetUp");
        $this->assertEquals($env->getTarget(), $posPay->getEnvironment()->getTarget(), "Wrong MoMoEndpoint in POSPayment SetUp");

    }

    public function testCreatePOSPayRequest()
    {
        $env = new Environment("https://test-payment.momo.vn/pay/pos", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'development');
        $publicKey = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkpa+qMXS6O11x7jBGo9W3yxeHEsAdyDE
40UoXhoQf9K6attSIclTZMEGfq6gmJm2BogVJtPkjvri5/j9mBntA8qKMzzanSQaBEbr8FyByHnf
226dsLt1RbJSMLjCd3UC1n0Yq8KKvfHhvmvVbGcWfpgfo7iQTVmL0r1eQxzgnSq31EL1yYNMuaZj
pHmQuT24Hmxl9W9enRtJyVTUhwKhtjOSOsR03sMnsckpFT9pn1/V9BE2Kf3rFGqc6JukXkqK6ZW9
mtmGLSq3K+JRRq2w8PVmcbcvTr/adW4EL2yc1qk9Ec4HtiDhtSYd6/ov8xLVkKAQjLVt7Ex3/agR
PfPrNwIDAQAB
-----END PUBLIC KEY-----";

        $partnerRefId = time() . "";
        $posPay = new POSPay($env);
        $request = $posPay->createPOSPayRequest('MM468121859458188758', 50000, $publicKey, $partnerRefId);
        $this->assertInstanceOf(POSPayRequest::class, $request, "Wrong Data Type in createPOSPayRequest");

        $arr = Converter::objectToArray($request);
        $this->assertArrayHasKey('partnerCode', $arr, "Missing partnerCode Attribute in createPOSPayRequest");
        $this->assertArrayHasKey('partnerRefId', $arr, "Missing partnerRefId Attribute in createPOSPayRequest");
        $this->assertArrayHasKey('hash', $arr, "Missing hash Attribute in createPOSPayRequest");
        $this->assertArrayHasKey('version', $arr, "Missing version Attribute in createPOSPayRequest");
        $this->assertArrayHasKey('payType', $arr, "Missing payType Attribute in createPOSPayRequest");
    }

    public function testProcess()
    {
        $env = new Environment("https://test-payment.momo.vn/pay/pos", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'development');
        $publicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkpa+qMXS6O11x7jBGo9W3yxeHEsAdyDE40UoXhoQf9K6attSIclTZMEGfq6gmJm2BogVJtPkjvri5/j9mBntA8qKMzzanSQaBEbr8FyByHnf226dsLt1RbJSMLjCd3UC1n0Yq8KKvfHhvmvVbGcWfpgfo7iQTVmL0r1eQxzgnSq31EL1yYNMuaZjpHmQuT24Hmxl9W9enRtJyVTUhwKhtjOSOsR03sMnsckpFT9pn1/V9BE2Kf3rFGqc6JukXkqK6ZW9mtmGLSq3K+JRRq2w8PVmcbcvTr/adW4EL2yc1qk9Ec4HtiDhtSYd6/ov8xLVkKAQjLVt7Ex3/agRPfPrNwIDAQAB";
        $partnerRefId = time() . "";

        $response = POSPay::process($env, 'MM468121859458188758', 50000, $publicKey, $partnerRefId, null, null, null);
        $this->assertInstanceOf(POSPayResponse::class, $response, "Wrong Data Type in execute in POSPayProcess");

        $arr = Converter::objectToArray($response);
        $this->assertArrayHasKey('status', $arr, "Missing status Attribute in POSPayProcess");
        $this->assertArrayHasKey('message', $arr, "Missing message Attribute in POSPayProcess");
        $this->assertInstanceOf(MoMoJson::class, $response->getMessage(), "Wrong Data Type for data Attribute in POSPayProcess -- Must be Json");

        $jsonArr = Converter::objectToArray($response->getMessage());
        $this->assertArrayHasKey('description', $jsonArr, "Missing description Attribute in JSON POSPayProcess");
        $this->assertArrayHasKey('transid', $jsonArr, "Missing transid Attribute in JSON POSPayProcess");
        $this->assertArrayHasKey('amount', $jsonArr, "Missing amount Attribute in JSON POSPayProcess");
        $this->assertArrayHasKey('phoneNumber', $jsonArr, "Missing phoneNumber Attribute in JSON POSPayProcess");

    }
}
