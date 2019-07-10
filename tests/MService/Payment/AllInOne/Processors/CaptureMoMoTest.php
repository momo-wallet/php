<?php

namespace MService\Payment\AllInOne\Processors;

use MService\Payment\AllInOne\Models\CaptureMoMoRequest;
use MService\Payment\AllInOne\Models\CaptureMoMoResponse;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;
use MService\Payment\Shared\Utils\Converter;
use PHPUnit\Framework\TestCase;

class CaptureMoMoTest extends TestCase
{
    public function test__construct()
    {
        $env = new Environment("teehee", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'testing');
        $captureMoMoWallet = new CaptureMoMo($env);

        $this->assertInstanceOf(Environment::class, $captureMoMoWallet->getEnvironment(), "Wrong Data Type for CaptureMoMo Environment");
        $this->assertInstanceOf(PartnerInfo::class, $captureMoMoWallet->getPartnerInfo(), "Wrong Data Type for CaptureMoMo PartnerInfo");

        $this->assertEquals("teehee", $captureMoMoWallet->getEnvironment()->getMoMoEndpoint(), "Wrong MoMoEndpoint in CaptureMoMo SetUp");
        $this->assertEquals($env->getTarget(), $captureMoMoWallet->getEnvironment()->getTarget(), "Wrong MoMoEndpoint in CaptureMoMo SetUp");
    }

    public function testCreateCaptureMoMoRequest()
    {
        $env = new Environment("https://test-payment.momo.vn/gw_payment/transactionProcessor", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
            'development');
        $orderId = time() . "";
        $requestId = time() . "";
        $captureMoMo = new CaptureMoMo($env);
        $testURL = "https://google.com.vn";

        $request = $captureMoMo->createCaptureMoMoRequest($orderId, "Payw With MoMo", '50000', 'asjyt', $requestId, $testURL, $testURL);
        $this->assertInstanceOf(CaptureMoMoRequest::class, $request, "Wrong Data Type for createCaptureMoMoRequest");

        $arr = Converter::objectToArray($request);
        $this->assertArrayHasKey('partnerCode', $arr, "Missing partnerCode Attribute in CaptureMoMoRequest");
        $this->assertArrayHasKey('accessKey', $arr, "Missing accessKey Attribute in CaptureMoMoRequest");
        $this->assertArrayHasKey('requestId', $arr, "Missing requestId Attribute in CaptureMoMoRequest");
        $this->assertArrayHasKey('amount', $arr, "Missing amount Attribute in CaptureMoMoRequest");
        $this->assertArrayHasKey('orderId', $arr, "Missing orderId Attribute in CaptureMoMoRequest");
        $this->assertArrayHasKey('orderInfo', $arr, "Missing orderInfo Attribute in CaptureMoMoRequest");
        $this->assertArrayHasKey('returnUrl', $arr, "Missing returnUrl Attribute in CaptureMoMoRequest");
        $this->assertArrayHasKey('notifyUrl', $arr, "Missing notifyUrl Attribute in CaptureMoMoRequest");
        $this->assertArrayHasKey('requestType', $arr, "Missing requestType Attribute in CaptureMoMoRequest");
        $this->assertArrayHasKey('signature', $arr, "Missing signature Attribute in CaptureMoMoRequest");
        $this->assertArrayHasKey('extraData', $arr, "Missing extraData Attribute in CaptureMoMoRequest");

        $this->assertEquals('captureMoMoWallet', $request->getRequestType(), "Wrong Request Type for CaptureMoMoRequest");

    }

    public function testProcessSuccess()
    {
        $env = new Environment("https://test-payment.momo.vn/gw_payment/transactionProcessor", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
            'development');
        $orderId = time() . "";
        $requestId = time() . "";

        $response = CaptureMoMo::process($env, $orderId, "Pay With MoMo", '50000', "sjygdvi", $requestId, "https://google.com.vn", "https://google.com.vn");
        $this->assertInstanceOf(CaptureMoMoResponse::class, $response, "Wrong Data Type in execute in CaptureMoMoProcess");

        $arr = Converter::objectToArray($response);
        $this->assertArrayHasKey('requestId', $arr, "Missing requestId Attribute in CaptureMoMoProcess");
        $this->assertArrayHasKey('errorCode', $arr, "Missing errorCode Attribute in CaptureMoMoProcess");
        $this->assertArrayHasKey('message', $arr, "Missing message Attribute in CaptureMoMoProcess");
        $this->assertArrayHasKey('localMessage', $arr, "Missing localMessage Attribute in CaptureMoMoProcess");
        $this->assertArrayHasKey('requestType', $arr, "Missing requestType Attribute in CaptureMoMoProcess");
        $this->assertArrayHasKey('payUrl', $arr, "Missing payUrl Attribute in CaptureMoMoProcess");
        $this->assertArrayHasKey('signature', $arr, "Missing signature Attribute in CaptureMoMoProcess");

        $this->assertEquals(0, $response->getErrorCode(), "Wrong Response Body from MoMo Server -- Wrong ErrorCode");
        $this->assertEquals('captureMoMoWallet', $response->getRequestType(), "Wrong Response Body from MoMo Server -- Wrong RequestType");
        $this->assertNotEmpty($response->getPayUrl(), "Wrong Response Body from MoMo Server -- Wrong PayURL");
        $this->assertNotEmpty($response->getSignature(), "Wrong Response Body from MoMo Server -- Wrong Signature");

    }

    public function testProcessFailure()
    {
        $env = new Environment("https://test-payment.momo.vn/gw_payment/transactionProcessor", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
            'development');

        $response = CaptureMoMo::process($env, '1562147883', "Pay With MoMo", "35000", "sjygdvi", '1562147883', "https://google.com.vn", "https://google.com.vn");
        $this->assertInstanceOf(CaptureMoMoResponse::class, $response, "Wrong Data Type in execute in CaptureMoMoProcess");

        $arr = Converter::objectToArray($response);
        $this->assertArrayHasKey('requestId', $arr, "Missing requestId Attribute in CaptureMoMoProcess");
        $this->assertArrayHasKey('errorCode', $arr, "Missing errorCode Attribute in CaptureMoMoProcess");
        $this->assertArrayHasKey('message', $arr, "Missing message Attribute in CaptureMoMoProcess");
        $this->assertArrayHasKey('localMessage', $arr, "Missing localMessage Attribute in CaptureMoMoProcess");
        $this->assertArrayHasKey('requestType', $arr, "Missing requestType Attribute in CaptureMoMoProcess");
        $this->assertArrayHasKey('payUrl', $arr, "Missing payUrl Attribute in CaptureMoMoProcess");
        $this->assertArrayHasKey('signature', $arr, "Missing signature Attribute in CaptureMoMoProcess");

        $this->assertEquals(6, $response->getErrorCode(), "Wrong Response Body from MoMo Server -- Wrong ErrorCode");
        $this->assertEquals('captureMoMoWallet', $response->getRequestType(), "Wrong Response Body from MoMo Server -- Wrong RequestType");
        $this->assertEmpty($response->getPayUrl(), "Wrong Response Body from MoMo Server -- Wrong PayURL");
        $this->assertNotEmpty($response->getSignature(), "Wrong Response Body from MoMo Server -- Wrong Signature");

    }
}
