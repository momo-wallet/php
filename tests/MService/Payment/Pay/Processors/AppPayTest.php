<?php

namespace MService\Payment\Pay\Processors;

use MService\Payment\Pay\Models\AppPayRequest;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;
use MService\Payment\Shared\Utils\Converter;
use PHPUnit\Framework\TestCase;

class AppPayTest extends TestCase
{

    public function test__construct()
    {
        $env = new Environment("teehee", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'testing');
        $appPay = new AppPay($env);

        $this->assertInstanceOf(Environment::class, $appPay->getEnvironment(), "Wrong Data Type for App Pay Environment");
        $this->assertInstanceOf(PartnerInfo::class, $appPay->getPartnerInfo(), "Wrong Data Type for App Pay PartnerInfo");

        $this->assertEquals("teehee", $appPay->getEnvironment()->getMoMoEndpoint(), "Wrong MoMoEndpoint in AppPay SetUp");
        $this->assertEquals($env->getTarget(), $appPay->getEnvironment()->getTarget(), "Wrong MoMoEndpoint in AppPay SetUp");

    }

    public function testCreateAppPayRequest()
    {
        $env = new Environment("https://test-payment.momo.vn/pay/app", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
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
        $appPay = new AppPay($env);
        $request = $appPay->createAppPayRequest(50000, "sugkvli", $publicKey, '0985659393', $partnerRefId);

        $arr = Converter::objectToArray($request);
        $this->assertInstanceOf(AppPayRequest::class, $request, "Wrong Data Type in createAppPayRequest");
        $this->assertArrayHasKey('partnerCode', $arr, "Missing partnerCode Attribute in AppPayRequest");
        $this->assertArrayHasKey('partnerRefId', $arr, "Missing partnerRefId Attribute in AppPayRequest");
        $this->assertArrayHasKey('customerNumber', $arr, "Missing customerNumber Attribute in AppPayRequest");
        $this->assertArrayHasKey('appData', $arr, "Missing appData Attribute in AppPayRequest");
        $this->assertArrayHasKey('hash', $arr, "Missing hash Attribute in AppPayRequest");
        $this->assertArrayHasKey('version', $arr, "Missing version Attribute in AppPayRequest");
        $this->assertArrayHasKey('payType', $arr, "Missing payType Attribute in AppPayRequest");

    }
}
