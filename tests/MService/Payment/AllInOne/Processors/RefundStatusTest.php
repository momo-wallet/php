<?php

namespace MService\Payment\AllInOne\Processors;

use MService\Payment\AllInOne\Models\RefundStatusRequest;
use MService\Payment\AllInOne\Models\RefundStatusResponse;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;
use MService\Payment\Shared\Utils\Converter;
use PHPUnit\Framework\TestCase;


class RefundStatusTest extends TestCase
{

    public function test__construct()
    {
        $env = new Environment("teehee", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'testing');
        $refundStatus = new RefundStatus($env);

        $this->assertInstanceOf(Environment::class, $refundStatus->getEnvironment(), "Wrong Data Type for RefundStatus Environment");
        $this->assertInstanceOf(PartnerInfo::class, $refundStatus->getPartnerInfo(), "Wrong Data Type for RefundStatus PartnerInfo");

        $this->assertEquals("teehee", $refundStatus->getEnvironment()->getMoMoEndpoint(), "Wrong MoMoEndpoint in RefundStatus SetUp");
        $this->assertEquals($env->getTarget(), $refundStatus->getEnvironment()->getTarget(), "Wrong MoMoEndpoint in RefundStatus SetUp");

    }

    public function testCreateRefundStatusRequest()
    {
        $env = new Environment("https://test-payment.momo.vn/gw_payment/transactionProcessor", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
            'development');
        $orderId = time() . "";
        $requestId = time() . "";
        $refundStatus = new RefundStatus($env);

        $request = $refundStatus->createRefundStatusRequest($orderId, $requestId);
        $this->assertInstanceOf(RefundStatusRequest::class, $request, "Wrong Data Type for createRefundStatusRequest");

        $arr = Converter::objectToArray($request);
        $this->assertArrayHasKey('partnerCode', $arr, "Missing partnerCode Attribute in RefundStatusRequest");
        $this->assertArrayHasKey('accessKey', $arr, "Missing accessKey Attribute in RefundStatusRequest");
        $this->assertArrayHasKey('requestId', $arr, "Missing requestId Attribute in RefundStatusRequest");
        $this->assertArrayHasKey('orderId', $arr, "Missing orderId Attribute in RefundStatusRequest");
        $this->assertArrayHasKey('requestType', $arr, "Missing requestType Attribute in RefundStatusRequest");
        $this->assertArrayHasKey('signature', $arr, "Missing signature Attribute in RefundStatusRequest");

        $this->assertEquals('refundStatus', $request->getRequestType(), "Wrong Request Type for RefundStatusRequest");

    }

    public function testProcessSuccessList()
    {
        $env = new Environment("https://test-payment.momo.vn/gw_payment/transactionProcessor", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
            'development');

        $responseList = RefundStatus::process($env, '1561972963', '1561972963');
        $this->assertNotEquals(0, count($responseList), "Wrong Response Body from MoMo Server -- missing RefundTransactions");

        foreach ($responseList as $index => $response) {
            $this->assertInstanceOf(RefundStatusResponse::class, $response, "Wrong Data Type in execute in RefundStatusProcess");

            $arr = Converter::objectToArray($response);
            $this->assertArrayHasKey('partnerCode', $arr, "Missing partnerCode Attribute in RefundStatusResponse");
            $this->assertArrayHasKey('accessKey', $arr, "Missing accessKey Attribute in RefundStatusResponse");
            $this->assertArrayHasKey('requestId', $arr, "Missing requestId Attribute in RefundStatusResponse");
            $this->assertArrayHasKey('orderId', $arr, "Missing orderId Attribute in RefundStatusResponse");
            $this->assertArrayHasKey('requestType', $arr, "Missing requestType Attribute in RefundStatusResponse");
            $this->assertArrayHasKey('amount', $arr, "Missing amount Attribute in RefundStatusResponse");
            $this->assertArrayHasKey('transId', $arr, "Missing transId Attribute in RefundStatusResponse");
            $this->assertArrayHasKey('errorCode', $arr, "Missing errorCode Attribute in RefundStatusResponse");
            $this->assertArrayHasKey('message', $arr, "Missing message Attribute in RefundStatusResponse");
            $this->assertArrayHasKey('localMessage', $arr, "Missing localMessage Attribute in RefundStatusResponse");
            $this->assertArrayHasKey('signature', $arr, "Missing signature Attribute in RefundStatusResponse");

            $this->assertEquals(0, $response->getErrorCode(), "Wrong Response Body from MoMo Server -- Wrong ErrorCode");
            $this->assertEquals('refundStatus', $response->getRequestType(), "Wrong Response Body from MoMo Server -- Wrong RequestType");
            $this->assertNotEmpty($response->getSignature(), "Wrong Response Body from MoMo Server -- Wrong Signature");
            $this->assertNotEmpty($response->getTransId(), "Wrong Response Body from MoMo Server -- Wrong TransId");
        }
    }

    public function testProcessEmptyList()
    {
        $env = new Environment("https://test-payment.momo.vn/gw_payment/transactionProcessor", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
            'development');

        $responseList = RefundStatus::process($env, '1562148833', '1562148833');
        $this->assertEquals(0, count($responseList), "Wrong Response Body from MoMo Server -- missing RefundTransactions");
    }
}
