<?php

namespace MService\Payment\AllInOne\Processors;

use MService\Payment\AllInOne\Models\CaptureIPNRequest;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;
use PHPUnit\Framework\TestCase;

class CaptureIPNTest extends TestCase
{

    public function test__construct()
    {
        $env = new Environment("teehee", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'testing');
        $captureIPN = new CaptureIPN($env);

        $this->assertInstanceOf(Environment::class, $captureIPN->getEnvironment(), "Wrong Data Type for QRNotify Environment");
        $this->assertInstanceOf(PartnerInfo::class, $captureIPN->getPartnerInfo(), "Wrong Data Type for QRNotify PartnerInfo");

        $this->assertEquals("teehee", $captureIPN->getEnvironment()->getMoMoEndpoint(), "Wrong MoMoEndpoint in QRNotify SetUp");
        $this->assertEquals($env->getTarget(), $captureIPN->getEnvironment()->getTarget(), "Wrong MoMoEndpoint in QRNotify SetUp");
    }

    public function testMoMoRequestCorrect()
    {
        $env = new Environment("https://test-payment.momo.vn", new PartnerInfo("ZjF6taKUohp7iN8l", 'MOMOTUEN20190312', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
            'development');
        $data = "partnerCode=MOMOTUEN20190312&accessKey=ZjF6taKUohp7iN8l&requestId=1555383430&orderId=1555383430&orderInfo=&orderType=momo_wallet&transId=2302586804&errorCode=0&message=Success&localMessage=Th%C3%A0nh%20c%C3%B4ng&payType=qr&responseTime=2019-04-09%2014%3A53%3A38&extraData=&signature=2a23a88aab6b6dd00b07669d84904778cc9d429c6a5748fa77298b05886a8620&amount=300000";
        $captureIPN = new CaptureIPN($env);

        $request = $captureIPN->getIPNInformationFromMoMo($data);
        $this->assertInstanceOf(CaptureIPNRequest::class, $request, "Wrong Verification Process for Capture MoMo IPN");

        $this->assertEquals('2302586804', $request->getTransId(), "Wrong TransId during creattion of IPNRequest");
        $this->assertEquals('1555383430', $request->getRequestId(), "Wrong RequestId during creattion of IPNRequest");
        $this->assertEquals(0, $request->getErrorCode(), "Wrong ErrorCode during creattion of IPNRequest");
    }

    public function testMoMoRequestWrong()
    {
        $env = new Environment("https://test-payment.momo.vn", new PartnerInfo("ZjF6taKUohp7iN8l", 'MOMOTUEN20190312', 'KqBEecvaJf1nULnhPF5htpG3AMtDIOlD'),
            'development');
        $data = "partnerCode=MOMO&accessKey=ZjF6taKUohp7iN8l&requestId=1555383430&orderId=1555383430&orderInfo=&orderType=momo_wallet&transId=2302586804&errorCode=0&message=Success&localMessage=Th%C3%A0nh%20c%C3%B4ng&payType=qr&responseTime=2019-04-09%2014%3A53%3A38&extraData=&signature=2a23a88aab6b6dd00b07669d84904778cc9d429c6a5748fa77298b05886a8620&amount=300000";
        $captureIPN = new CaptureIPN($env);

        $request = $captureIPN->getIPNInformationFromMoMo($data);
        $this->assertNotInstanceOf(CaptureIPNRequest::class, $request, "Wrong Verification Process for Capture MoMo IPN");
    }

}
