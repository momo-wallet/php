<?php

namespace MService\Payment\Pay\Processors;

use MService\Payment\Pay\Models\QRNotificationRequest;
use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;
use PHPUnit\Framework\TestCase;

include_once "../../../../../loader.php";

class QRNotifyTest extends TestCase
{
    public function test__construct()
    {
        $env = new Environment("teehee", new PartnerInfo("mTCKt9W3eU1m39TW", 'MOMOIQA420180417', 'PPuDXq1KowPT1ftR8DvlQTHhC03aul17'),
            'testing');
        $qrNotify = new QRNotify($env);

        $this->assertInstanceOf(Environment::class, $qrNotify->getEnvironment(), "Wrong Data Type for QRNotify Environment");
        $this->assertInstanceOf(PartnerInfo::class, $qrNotify->getPartnerInfo(), "Wrong Data Type for QRNotify PartnerInfo");

        $this->assertEquals("teehee", $qrNotify->getEnvironment()->getMoMoEndpoint(), "Wrong MoMoEndpoint in QRNotify SetUp");
        $this->assertEquals($env->getTarget(), $qrNotify->getEnvironment()->getTarget(), "Wrong MoMoEndpoint in QRNotify SetUp");

    }

    public function testMoMoRequestWrong()
    {
        $data = "{
  \"partnerCode\": \"MOMOIQA420180417\",
  \"accessKey\": \"TNWFx9JWayevKPiB8LyTgODiCSrjstXN\",
  \"amount\": 10000,
  \"partnerRefId\": \"B001221\",
  \"partnerTransId\": \"\",
  \"transType\": \"momo_wallet\",
  \"momoTransId\": \"43121679\",
  \"status\": 0,
  \"message\": \"Thành Công\",
  \"responseTime\": 1555472829549,
  \"signature\": \"a97c8a08e574a336edbda1e08373a00861dc7a894d09c98a23fd9e834e35645c\",
  \"storeId\": \"store001\"
}";
        $qrNotify = new QRNotify(Environment::selectEnv('dev'));
        $request = $qrNotify->getQRNotificationFromMoMo($data);

        $this->assertNotInstanceOf(QRNotificationRequest::class, $request, "Wrong Verification Process for MoMo QR Notification");
    }

    public function testMoMoRequestCorrect()
    {
        $data = "{
  \"partnerCode\": \"MOMOIQA420180417\",
  \"accessKey\": \"TNWFx9JWayevKPiB8LyTgODiCSrjstXN\",
  \"amount\": 10000,
  \"partnerRefId\": \"B001221\",
  \"partnerTransId\": \"\",
  \"transType\": \"momo_wallet\",
  \"momoTransId\": \"43121679\",
  \"status\": 0,
  \"message\": \"Thành Công\",
  \"responseTime\": 1555472829549,
  \"signature\": \"3ec88652f5d86997780a6adf1545c2617ca9e39be66f94937cb6187ebd66d1b4\",
  \"storeId\": \"store001\"
}";
        $qrNotify = new QRNotify(Environment::selectEnv('dev'));
        $request = $qrNotify->getQRNotificationFromMoMo($data);

        $this->assertInstanceOf(QRNotificationRequest::class, $request, "Wrong Verification Process for MoMo QR Notification");

        $this->assertEquals(0, $request->getStatus(), "Wrong Status data when creating QRNotificationRequest");
        $this->assertEquals('momo_wallet', $request->getTransType(), "Wrong TransType data when creating QRNotificationRequest");
        $this->assertEquals('B001221', $request->getPartnerRefId(), "Wrong partnerRefId data when creating QRNotificationRequest");

    }
}
