<?php

namespace MService\Payment\Pay\Processors;

use MService\Payment\Pay\Models\TransactionRefundRequest;
use MService\Payment\Pay\Models\TransactionRefundResponse;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;
use MService\Payment\Shared\Utils\Converter;
use PHPUnit\Framework\TestCase;


class TransactionRefundTest extends TestCase
{
    public function test__construct()
    {
        $env = new Environment("teehee", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'testing');
        $refund = new TransactionRefund($env);

        $this->assertInstanceOf(Environment::class, $refund->getEnvironment(), "Wrong Data Type for TransactionRefund Environment");
        $this->assertInstanceOf(PartnerInfo::class, $refund->getPartnerInfo(), "Wrong Data Type for TransactionRefund PartnerInfo");

        $this->assertEquals("teehee", $refund->getEnvironment()->getMoMoEndpoint(), "Wrong MoMoEndpoint in TransactionRefund SetUp");
        $this->assertEquals($env->getTarget(), $refund->getEnvironment()->getTarget(), "Wrong MoMoEndpoint in TransactionRefund SetUp");

    }

    public function testCreateTransactionRefundRequest()
    {
        $env = new Environment("https://test-payment.momo.vn/pay/refund", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'development');
        $publicKey = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkpa+qMXS6O11x7jBGo9W3yxeHEsAdyDE
40UoXhoQf9K6attSIclTZMEGfq6gmJm2BogVJtPkjvri5/j9mBntA8qKMzzanSQaBEbr8FyByHnf
226dsLt1RbJSMLjCd3UC1n0Yq8KKvfHhvmvVbGcWfpgfo7iQTVmL0r1eQxzgnSq31EL1yYNMuaZj
pHmQuT24Hmxl9W9enRtJyVTUhwKhtjOSOsR03sMnsckpFT9pn1/V9BE2Kf3rFGqc6JukXkqK6ZW9
mtmGLSq3K+JRRq2w8PVmcbcvTr/adW4EL2yc1qk9Ec4HtiDhtSYd6/ov8xLVkKAQjLVt7Ex3/agR
PfPrNwIDAQAB
-----END PUBLIC KEY-----";

        $requestId = time() . "";
        $refund = new TransactionRefund($env);
        $request = $refund->createTransactionRefundRequest($requestId, 10000, $publicKey, '1562138427', '2305016460');
        $this->assertInstanceOf(TransactionRefundRequest::class, $request, "Wrong Data Type in createTransactionRefundRequest");

        $arr = Converter::objectToArray($request);
        $this->assertArrayHasKey('partnerCode', $arr, "Missing partnerCode Attribute in createTransactionRefundRequest");
        $this->assertArrayHasKey('requestId', $arr, "Missing requestId Attribute in createTransactionRefundRequest");
        $this->assertArrayHasKey('hash', $arr, "Missing hash Attribute in createTransactionRefundRequest");
        $this->assertArrayHasKey('version', $arr, "Missing version Attribute in createTransactionRefundRequest");

    }

    public function testProcessFailure()
    {
        $env = new Environment("https://test-payment.momo.vn/pay/refund", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'development');
        $publicKey = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkpa+qMXS6O11x7jBGo9W3yxeHEsAdyDE
40UoXhoQf9K6attSIclTZMEGfq6gmJm2BogVJtPkjvri5/j9mBntA8qKMzzanSQaBEbr8FyByHnf
226dsLt1RbJSMLjCd3UC1n0Yq8KKvfHhvmvVbGcWfpgfo7iQTVmL0r1eQxzgnSq31EL1yYNMuaZj
pHmQuT24Hmxl9W9enRtJyVTUhwKhtjOSOsR03sMnsckpFT9pn1/V9BE2Kf3rFGqc6JukXkqK6ZW9
mtmGLSq3K+JRRq2w8PVmcbcvTr/adW4EL2yc1qk9Ec4HtiDhtSYd6/ov8xLVkKAQjLVt7Ex3/agR
PfPrNwIDAQAB
-----END PUBLIC KEY-----";

        $response = TransactionRefund::process($env, '1562138427', 10000, $publicKey, '1562138427', '2305016460');
        $this->assertInstanceOf(TransactionRefundResponse::class, $response, "Wrong Data Type in execute in TransactionRefundProcess");

        $arr = Converter::objectToArray($response);
        $this->assertArrayHasKey('status', $arr, "Missing status Attribute in TransactionRefundProcess");
        $this->assertArrayHasKey('message', $arr, "Missing message Attribute in TransactionRefundProcess");
        $this->assertArrayHasKey('partnerRefId', $arr, "Missing partnerRefId Attribute in TransactionRefundProcess");
        $this->assertArrayHasKey('transid', $arr, "Missing transid Attribute in TransactionRefundProcess");
        $this->assertArrayHasKey('amount', $arr, "Missing amount Attribute in TransactionRefundProcess");

        $this->assertEquals(2128, $response->getStatus(), "Wrong Response Body from MoMo Server for TransactionRefundResponse");
        $this->assertEquals(10000, $response->getAmount(), "Wrong Response Body from MoMo Server for TransactionRefundResponse");
        $this->assertEquals(-1, $response->getTransid(), "Wrong Response Body from MoMo Server for TransactionRefundResponse");
    }
}
