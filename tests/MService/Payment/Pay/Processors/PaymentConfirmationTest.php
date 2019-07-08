<?php

namespace MService\Payment\Pay\Processors;

use MService\Payment\Pay\Models\MoMoJson;
use MService\Payment\Pay\Models\PaymentConfirmationRequest;
use MService\Payment\Pay\Models\PaymentConfirmationResponse;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;
use MService\Payment\Shared\Utils\Converter;
use PHPUnit\Framework\TestCase;

class PaymentConfirmationTest extends TestCase
{

    public function test__construct()
    {
        $env = new Environment("teehee", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'testing');
        $payConfirm = new PaymentConfirmation($env);

        $this->assertInstanceOf(Environment::class, $payConfirm->getEnvironment(), "Wrong Data Type for Payment Confirmation Environment");
        $this->assertInstanceOf(PartnerInfo::class, $payConfirm->getPartnerInfo(), "Wrong Data Type for Payment Confirmation PartnerInfo");

        $this->assertEquals("teehee", $payConfirm->getEnvironment()->getMoMoEndpoint(), "Wrong MoMoEndpoint in Payment Confirmation SetUp");
        $this->assertEquals($env->getTarget(), $payConfirm->getEnvironment()->getTarget(), "Wrong MoMoEndpoint in Payment Confirmation SetUp");

    }

    public function testCreatePaymentConfirmationRequest()
    {
        $env = new Environment("https://test-payment.momo.vn/pay/confirm", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'development');

        $partnerRefId = time() . "";
        $requestId = time() . "";
        $payConfirm = new PaymentConfirmation($env);
        $request = $payConfirm->createPaymentConfirmationRequest($partnerRefId, "capture", "drthdr", $requestId);
        $this->assertInstanceOf(PaymentConfirmationRequest::class, $request, "Wrong Data Type in createPaymentConfirmationRequest");

        $arr = Converter::objectToArray($request);
        $this->assertArrayHasKey('partnerCode', $arr, "Missing partnerCode Attribute in createPaymentConfirmationRequest");
        $this->assertArrayHasKey('partnerRefId', $arr, "Missing partnerRefId Attribute in createPaymentConfirmationRequest");
        $this->assertArrayHasKey('requestType', $arr, "Missing requestType Attribute in createPaymentConfirmationRequest");
        $this->assertArrayHasKey('requestId', $arr, "Missing requestId Attribute in createPaymentConfirmationRequest");
        $this->assertArrayHasKey('momoTransId', $arr, "Missing momoTransId Attribute in createPaymentConfirmationRequest");
        $this->assertArrayHasKey('signature', $arr, "Missing signature Attribute in createPaymentConfirmationRequest");
    }

    public function testProcess()
    {
        $env = new Environment("https://test-payment.momo.vn/pay/confirm", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'development');
        $requestId = time() . "";

        $response = PaymentConfirmation::process($env, '1562138427', "capture", "2305016460", $requestId);
        $this->assertInstanceOf(PaymentConfirmationResponse::class, $response, "Wrong Data Type in execute in PaymentConfirmationProcess");

        $arr = Converter::objectToArray($response);
        $this->assertArrayHasKey('status', $arr, "Missing status Attribute in PaymentConfirmationProcess");
        $this->assertArrayHasKey('message', $arr, "Missing message Attribute in PaymentConfirmationProcess");
        $this->assertArrayHasKey('data', $arr, "Missing data Attribute in PaymentConfirmationProcess");
        $this->assertArrayHasKey('signature', $arr, "Missing signature Attribute in PaymentConfirmationProcess");
        if ($response->getStatus() == 0) {
            $this->assertInstanceOf(MoMoJson::class, $response->getData(), "Wrong Data Type for data Attribute in PaymentConfirmationProcess -- Must be Json");

            $jsonArr = Converter::objectToArray($response->getData());
            $this->assertArrayHasKey('partnerCode', $jsonArr, "Missing partnerCode Attribute in JSON PaymentConfirmationProcess");
            $this->assertArrayHasKey('partnerRefId', $jsonArr, "Missing partnerRefId Attribute in JSON PaymentConfirmationProcess");
            $this->assertArrayHasKey('momoTransId', $jsonArr, "Missing momoTransId Attribute in JSON PaymentConfirmationProcess");
            $this->assertArrayHasKey('amount', $jsonArr, "Missing amount Attribute in JSON PaymentConfirmationProcess");
        }
    }

}
