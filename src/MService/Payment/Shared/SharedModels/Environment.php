<?php


namespace MService\Payment\Shared\SharedModels;

use MService\Payment\Shared\Utils\MoMoException;

class Environment
{
    private $momoEndpoint;
    private $partnerInfo;
    private $target;

    /**
     * Environment constructor.
     * @param $momoEndpoint
     * @param $partnerInfo
     * @param $target
     */
    public function __construct($momoEndpoint, $partnerInfo, $target)
    {
        $this->momoEndpoint = $momoEndpoint;
        $this->partnerInfo = $partnerInfo;
        $this->target = $target;
    }

    public static function selectEnv($target)
    {
        switch ($target) {
            case "dev":
                $devInfo = new PartnerInfo("MOMOLRJZ20181206", "mTCKt9W3eU1m39TW", "KqBEecvaJf1nULnhPF5htpG3AMtDIOlD");
                $dev = new Environment("https://test-payment.momo.vn", $devInfo, "development");
                return $dev;
            case "prod":
                $productionInfo = new PartnerInfo("MOMO", "F8BBA842ECF85", "K951B6PE1waDMi640xX08PD3vg6EkVlz");
                $production = new Environment("https://payment.momo.vn", $productionInfo, "production");
                return $production;
            default:
                throw new MoMoException("MoMo doesnt provide other environment: dev and prod");
        }
    }

    /**
     * @return mixed
     */
    public function getMomoEndpoint()
    {
        return $this->momoEndpoint;
    }

    /**
     * @param mixed $momoEndpoint
     */
    public function setMomoEndpoint($momoEndpoint): void
    {
        $this->momoEndpoint = $momoEndpoint;
    }

    /**
     * @return mixed
     */
    public function getPartnerInfo(): PartnerInfo
    {
        return $this->partnerInfo;
    }

    /**
     * @param mixed $partnerInfo
     */
    public function setPartnerInfo($partnerInfo): void
    {
        $this->partnerInfo = $partnerInfo;
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param mixed $target
     */
    public function setTarget($target): void
    {
        $this->target = $target;
    }

}