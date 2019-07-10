<?php

namespace MService\Payment\AllInOne\Processors;

use MService\Payment\AllInOne\Models\PayATMRequest;
use MService\Payment\AllInOne\Models\PayATMResponse;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;
use MService\Payment\Shared\Utils\Converter;
use PHPUnit\Framework\TestCase;


class PayATMTest extends TestCase
{

    public function test__construct()
    {
        $env = new Environment("teehee", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'testing');
        $payATM = new PayATM($env);

        $this->assertInstanceOf(Environment::class, $payATM->getEnvironment(), "Wrong Data Type for CaptureMoMo Environment");
        $this->assertInstanceOf(PartnerInfo::class, $payATM->getPartnerInfo(), "Wrong Data Type for CaptureMoMo PartnerInfo");

        $this->assertEquals("teehee", $payATM->getEnvironment()->getMoMoEndpoint(), "Wrong MoMoEndpoint in CaptureMoMo SetUp");
        $this->assertEquals($env->getTarget(), $payATM->getEnvironment()->getTarget(), "Wrong MoMoEndpoint in CaptureMoMo SetUp");
    }

    public function testCreatePayATMRequest()
    {
        $env = new Environment("https://test-payment.momo.vn/gw_payment/transactionProcessor", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
            'development');
        $orderId = time() . "";
        $requestId = time() . "";
        $payATM = new PayATM($env);
        $testURL = "https://google.com.vn";

        $request = $payATM->createPayATMRequest($orderId, 'Pay With ATM', '35000', 'fgbfg', $requestId, $testURL, $testURL, 'SML');
        $this->assertInstanceOf(PayATMRequest::class, $request, "Wrong Data Type for createPayATMRequest");

        $arr = Converter::objectToArray($request);
        $this->assertArrayHasKey('partnerCode', $arr, "Missing partnerCode Attribute in createPayATMRequest");
        $this->assertArrayHasKey('accessKey', $arr, "Missing accessKey Attribute in createPayATMRequest");
        $this->assertArrayHasKey('requestId', $arr, "Missing requestId Attribute in createPayATMRequest");
        $this->assertArrayHasKey('bankCode', $arr, "Missing bankCode Attribute in createPayATMRequest");
        $this->assertArrayHasKey('amount', $arr, "Missing amount Attribute in createPayATMRequest");
        $this->assertArrayHasKey('orderId', $arr, "Missing orderId Attribute in createPayATMRequest");
        $this->assertArrayHasKey('orderInfo', $arr, "Missing orderInfo Attribute in createPayATMRequest");
        $this->assertArrayHasKey('returnUrl', $arr, "Missing returnUrl Attribute in createPayATMRequest");
        $this->assertArrayHasKey('notifyUrl', $arr, "Missing notifyUrl Attribute in createPayATMRequest");
        $this->assertArrayHasKey('extraData', $arr, "Missing extraData Attribute in createPayATMRequest");
        $this->assertArrayHasKey('requestType', $arr, "Missing requestType Attribute in createPayATMRequest");
        $this->assertArrayHasKey('signature', $arr, "Missing signature Attribute in createPayATMRequest");

        $this->assertEquals('payWithMoMoATM', $request->getRequestType(), "Wrong Request Type for PayATMRequest");

    }

    public function testProcessSuccess()
    {
        $env = new Environment("https://test-payment.momo.vn/gw_payment/transactionProcessor", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
            'development');
        $orderId = time() . "";
        $requestId = time() . "";

        $response = PayATM::process($env, $orderId, "Pay With MoMo", "35000", '', $requestId, "https://google.com.vn", "https://google.com.vn", "SML");
        $this->assertInstanceOf(PayATMResponse::class, $response, "Wrong Data Type in execute in PayATMProcess");

        $arr = Converter::objectToArray($response);
        $this->assertArrayHasKey('requestId', $arr, "Missing requestId Attribute in PayATMProcess");
        $this->assertArrayHasKey('payUrl', $arr, "Missing payUrl Attribute in PayATMProcess");
        $this->assertArrayHasKey('errorCode', $arr, "Missing errorCode Attribute in PayATMProcess");
        $this->assertArrayHasKey('orderId', $arr, "Missing orderId Attribute in PayATMProcess");
        $this->assertArrayHasKey('message', $arr, "Missing message Attribute in PayATMProcess");
        $this->assertArrayHasKey('localMessage', $arr, "Missing localMessage Attribute in PayATMProcess");
        $this->assertArrayHasKey('requestType', $arr, "Missing requestType Attribute in PayATMProcess");
        $this->assertArrayHasKey('signature', $arr, "Missing signature Attribute in PayATMProcess");

        $this->assertEquals('payWithMoMoATM', $response->getRequestType(), "Wrong Response Body from MoMo Server -- Wrong RequestType");

    }

    public function testProcessFailure()
    {
        $env = new Environment("https://test-payment.momo.vn/gw_payment/transactionProcessor", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
            'development');

        $response = PayATM::process($env, '1562148833', "Pay With MoMo", "35000", '', '1562148833', "https://google.com.vn", "https://google.com.vn", "SML");
        $this->assertInstanceOf(PayATMResponse::class, $response, "Wrong Data Type in execute in PayATMProcess");

        $arr = Converter::objectToArray($response);
        $this->assertArrayHasKey('requestId', $arr, "Missing requestId Attribute in PayATMProcess");
        $this->assertArrayHasKey('payUrl', $arr, "Missing payUrl Attribute in PayATMProcess");
        $this->assertArrayHasKey('errorCode', $arr, "Missing errorCode Attribute in PayATMProcess");
        $this->assertArrayHasKey('orderId', $arr, "Missing orderId Attribute in PayATMProcess");
        $this->assertArrayHasKey('message', $arr, "Missing message Attribute in PayATMProcess");
        $this->assertArrayHasKey('localMessage', $arr, "Missing localMessage Attribute in PayATMProcess");
        $this->assertArrayHasKey('requestType', $arr, "Missing requestType Attribute in PayATMProcess");
        $this->assertArrayHasKey('signature', $arr, "Missing signature Attribute in PayATMProcess");

        $this->assertEquals(6, $response->getErrorCode(), "Wrong Response Body from MoMo Server -- Wrong ErrorCode");
        $this->assertEquals('payWithMoMoATM', $response->getRequestType(), "Wrong Response Body from MoMo Server -- Wrong RequestType");
        $this->assertEmpty($response->getPayUrl(), "Wrong Response Body from MoMo Server -- Wrong PayURL");
        $this->assertEmpty($response->getSignature(), "Wrong Response Body from MoMo Server -- Wrong Signature");
    }

}