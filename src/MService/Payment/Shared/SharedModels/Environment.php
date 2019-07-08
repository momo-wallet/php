<?php


namespace MService\Payment\Shared\SharedModels;

use Monolog\Logger;
use MService\Payment\Shared\Utils\MoMoException;

class Environment
{
    private $momoEndpoint;
    private $partnerInfo;
    private $target;
    private $logger;

    /**
     * Environment constructor.
     * @param $momoEndpoint
     * @param $partnerInfo
     * @param $target
     *
     */
    public function __construct($momoEndpoint, $partnerInfo, $target, $path = __DIR__ . '/test.log', $channelName = 'default', $level = Logger::DEBUG)
    {
        $this->momoEndpoint = $momoEndpoint;
        $this->partnerInfo = $partnerInfo;
        $this->target = $target;
        $this->logger = (new Log($path, $channelName, $level))->getLogger();
    }

    public static function selectEnv($target = "dev")
    {
        switch ($target) {
            case "dev":
                $devInfo = new PartnerInfo("mTCKt9W3eU1m39TW", "MOMOLRJZ20181206", "KqBEecvaJf1nULnhPF5htpG3AMtDIOlD");
                $dev = new Environment("https://test-payment.momo.vn", $devInfo, "development");
                return $dev;
            case "prod":
                $productionInfo = new PartnerInfo("F8BBA842ECF85", "MOMO", "K951B6PE1waDMi640xX08PD3vg6EkVlz");
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

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

}