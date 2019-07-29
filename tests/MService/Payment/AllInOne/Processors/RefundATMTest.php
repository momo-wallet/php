<?php

namespace MService\Payment\AllInOne\Processors;

use MService\Payment\AllInOne\Models\RefundATMRequest;
use MService\Payment\AllInOne\Models\RefundATMResponse;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;
use MService\Payment\Shared\Utils\Converter;
use PHPUnit\Framework\TestCase;


class RefundATMTest extends TestCase
{

    public function test__construct()
    {
        $env = new Environment("teehee", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'testing');
        $refundATM = new RefundATM($env);

        $this->assertInstanceOf(Environment::class, $refundATM->getEnvironment(), "Wrong Data Type for RefundATM Environment");
        $this->assertInstanceOf(PartnerInfo::class, $refundATM->getPartnerInfo(), "Wrong Data Type for RefundATM PartnerInfo");

        $this->assertEquals("teehee", $refundATM->getEnvironment()->getMoMoEndpoint(), "Wrong MoMoEndpoint in RefundATM SetUp");
        $this->assertEquals($env->getTarget(), $refundATM->getEnvironment()->getTarget(), "Wrong MoMoEndpoint in RefundATM SetUp");
    }

    public function testCreateRefundATMRequest()
    {
        $env = new Environment("https://test-payment.momo.vn/gw_payment/transactionProcessor", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
            'development');
        $orderId = time() . "";
        $refundATM = new RefundATM($env);

        $request = $refundATM->createRefundATMRequest($orderId, $orderId, 10000, $orderId, 'SML');
        $this->assertInstanceOf(RefundATMRequest::class, $request, "Wrong Data Type for createRefundATMRequest");

        $arr = Converter::objectToArray($request);
        $this->assertArrayHasKey('partnerCode', $arr, "Missing partnerCode Attribute in RefundATMRequest");
        $this->assertArrayHasKey('accessKey', $arr, "Missing accessKey Attribute in RefundATMRequest");
        $this->assertArrayHasKey('requestId', $arr, "Missing requestId Attribute in RefundATMRequest");
        $this->assertArrayHasKey('amount', $arr, "Missing amount Attribute in RefundATMRequest");
        $this->assertArrayHasKey('bankCode', $arr, "Missing bankCode Attribute in RefundATMRequest");
        $this->assertArrayHasKey('orderId', $arr, "Missing orderId Attribute in RefundATMRequest");
        $this->assertArrayHasKey('transId', $arr, "Missing transId Attribute in RefundATMRequest");
        $this->assertArrayHasKey('requestType', $arr, "Missing requestType Attribute in RefundATMRequest");
        $this->assertArrayHasKey('signature', $arr, "Missing signature Attribute in RefundATMRequest");

        $this->assertEquals('refundMoMoATM', $request->getRequestType(), "Wrong Request Type for RefundATMRequest");
    }

    public function testProcessFailure()
    {
        $env = new Environment("https://test-payment.momo.vn/gw_payment/transactionProcessor", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
            'development');

        $response = RefundATM::process($env, '1562152250', '1562059843', '10000', '2304992176', 'SML');

        $this->assertInstanceOf(RefundATMResponse::class, $response, "Wrong Data Type in execute in RefundATMProcess");

        $arr = Converter::objectToArray($response);
        $this->assertArrayHasKey('partnerCode', $arr, "Missing partnerCode Attribute in RefundATMResponse");
        $this->assertArrayHasKey('accessKey', $arr, "Missing accessKey Attribute in RefundATMResponse");
        $this->assertArrayHasKey('requestId', $arr, "Missing requestId Attribute in RefundATMResponse");
        $this->assertArrayHasKey('orderId', $arr, "Missing orderId Attribute in RefundATMResponse");
        $this->assertArrayHasKey('requestType', $arr, "Missing requestType Attribute in RefundATMResponse");
        $this->assertArrayHasKey('transId', $arr, "Missing transId Attribute in RefundATMResponse");
        $this->assertArrayHasKey('errorCode', $arr, "Missing errorCode Attribute in RefundATMResponse");
        $this->assertArrayHasKey('message', $arr, "Missing message Attribute in RefundATMResponse");
        $this->assertArrayHasKey('localMessage', $arr, "Missing localMessage Attribute in RefundATMResponse");
        $this->assertArrayHasKey('signature', $arr, "Missing signature Attribute in RefundATMResponse");

        $this->assertEquals(6, $response->getErrorCode(), "Wrong Response Body from MoMo Server -- Wrong ErrorCode");
        $this->assertEquals('refundMoMoATM', $response->getRequestType(), "Wrong Response Body from MoMo Server -- Wrong RequestType");
    }
}
