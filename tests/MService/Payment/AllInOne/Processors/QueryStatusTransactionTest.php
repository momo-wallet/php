<?php

namespace MService\Payment\AllInOne\Processors;

use MService\Payment\AllInOne\Models\QueryStatusRequest;
use MService\Payment\AllInOne\Models\QueryStatusResponse;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;
use MService\Payment\Shared\Utils\Converter;
use PHPUnit\Framework\TestCase;

class QueryStatusTransactionTest extends TestCase
{

    public function test__construct()
    {
        $env = new Environment("teehee", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'testing');
        $query = new QueryStatusTransaction($env);

        $this->assertInstanceOf(Environment::class, $query->getEnvironment(), "Wrong Data Type for QueryStatusTransaction Environment");
        $this->assertInstanceOf(PartnerInfo::class, $query->getPartnerInfo(), "Wrong Data Type for QueryStatusTransaction PartnerInfo");

        $this->assertEquals("teehee", $query->getEnvironment()->getMoMoEndpoint(), "Wrong MoMoEndpoint in QueryStatusTransaction SetUp");
        $this->assertEquals($env->getTarget(), $query->getEnvironment()->getTarget(), "Wrong MoMoEndpoint in QueryStatusTransaction SetUp");

    }

    public function testCreateQueryStatusRequest()
    {
        $env = new Environment("https://test-payment.momo.vn/gw_payment/transactionProcessor", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
            'development');
        $orderId = time() . "";
        $requestId = time() . "";
        $query = new QueryStatusTransaction($env);

        $request = $query->createQueryStatusRequest($orderId, $requestId);
        $this->assertInstanceOf(QueryStatusRequest::class, $request, "Wrong Data Type for createQueryStatusRequest");

        $arr = Converter::objectToArray($request);
        $this->assertArrayHasKey('partnerCode', $arr, "Missing partnerCode Attribute in QueryStatusRequest");
        $this->assertArrayHasKey('accessKey', $arr, "Missing accessKey Attribute in QueryStatusRequest");
        $this->assertArrayHasKey('requestId', $arr, "Missing requestId Attribute in QueryStatusRequest");
        $this->assertArrayHasKey('orderId', $arr, "Missing orderId Attribute in QueryStatusRequest");
        $this->assertArrayHasKey('requestType', $arr, "Missing requestType Attribute in QueryStatusRequest");
        $this->assertArrayHasKey('signature', $arr, "Missing signature Attribute in QueryStatusRequest");

        $this->assertEquals('transactionStatus', $request->getRequestType(), "Wrong Request Type for QueryStatusRequest");
    }

    public function testProcessTransInit()
    {
        $env = new Environment("https://test-payment.momo.vn/gw_payment/transactionProcessor", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
            'development');

        $response = QueryStatusTransaction::process($env, '1562147883', '1562147883');
        $this->assertInstanceOf(QueryStatusResponse::class, $response, "Wrong Data Type in execute in QueryStatusTransactionProcess");

        $arr = Converter::objectToArray($response);
        $this->assertArrayHasKey('partnerCode', $arr, "Missing partnerCode Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('accessKey', $arr, "Missing accessKey Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('requestId', $arr, "Missing requestId Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('orderId', $arr, "Missing orderId Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('requestType', $arr, "Missing requestType Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('amount', $arr, "Missing amount Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('transId', $arr, "Missing transId Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('payType', $arr, "Missing payType Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('errorCode', $arr, "Missing errorCode Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('message', $arr, "Missing message Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('localMessage', $arr, "Missing localMessage Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('extraData', $arr, "Missing extraData Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('signature', $arr, "Missing signature Attribute in QueryStatusResponse");

        $this->assertEquals(-1, $response->getErrorCode(), "Wrong Response Body from MoMo Server -- Wrong ErrorCode");
        $this->assertEquals('transactionStatus', $response->getRequestType(), "Wrong Response Body from MoMo Server -- Wrong RequestType");
        $this->assertNotEmpty($response->getSignature(), "Wrong Response Body from MoMo Server -- Wrong Signature");

    }

    public function testProcessFailure()
    {
        $env = new Environment("https://test-payment.momo.vn/gw_payment/transactionProcessor", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
            'development');

        $response = QueryStatusTransaction::process($env, time() . '', time() . '');
        $this->assertInstanceOf(QueryStatusResponse::class, $response, "Wrong Data Type in execute in QueryStatusTransactionProcess");

        $arr = Converter::objectToArray($response);
        $this->assertArrayHasKey('partnerCode', $arr, "Missing partnerCode Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('accessKey', $arr, "Missing accessKey Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('requestId', $arr, "Missing requestId Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('orderId', $arr, "Missing orderId Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('requestType', $arr, "Missing requestType Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('amount', $arr, "Missing amount Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('transId', $arr, "Missing transId Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('payType', $arr, "Missing payType Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('errorCode', $arr, "Missing errorCode Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('message', $arr, "Missing message Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('localMessage', $arr, "Missing localMessage Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('extraData', $arr, "Missing extraData Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('signature', $arr, "Missing signature Attribute in QueryStatusResponse");

        $this->assertEquals('transactionStatus', $response->getRequestType(), "Wrong Response Body from MoMo Server -- Wrong RequestType");
    }

    public function testProcessSuccess()
    {
        $env = new Environment("https://test-payment.momo.vn/gw_payment/transactionProcessor", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOLRJZ20181206', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
            'development');

        $response = QueryStatusTransaction::process($env, '1561972963', '1561972963');
        $this->assertInstanceOf(QueryStatusResponse::class, $response, "Wrong Data Type in execute in QueryStatusTransactionProcess");

        $arr = Converter::objectToArray($response);
        $this->assertArrayHasKey('partnerCode', $arr, "Missing partnerCode Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('accessKey', $arr, "Missing accessKey Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('requestId', $arr, "Missing requestId Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('orderId', $arr, "Missing orderId Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('requestType', $arr, "Missing requestType Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('amount', $arr, "Missing amount Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('transId', $arr, "Missing transId Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('payType', $arr, "Missing payType Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('errorCode', $arr, "Missing errorCode Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('message', $arr, "Missing message Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('localMessage', $arr, "Missing localMessage Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('extraData', $arr, "Missing extraData Attribute in QueryStatusResponse");
        $this->assertArrayHasKey('signature', $arr, "Missing signature Attribute in QueryStatusResponse");

        $this->assertEquals(0, $response->getErrorCode(), "Wrong Response Body from MoMo Server -- Wrong ErrorCode");
        $this->assertEquals(2304963974, $response->getTransId(), "Wrong Response Body from MoMo Server -- Wrong TransId");
        $this->assertEquals('transactionStatus', $response->getRequestType(), "Wrong Response Body from MoMo Server -- Wrong RequestType");
        $this->assertNotEmpty($response->getSignature(), "Wrong Response Body from MoMo Server -- Wrong Signature");

    }

}
