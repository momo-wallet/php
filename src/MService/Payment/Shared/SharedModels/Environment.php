<?php


namespace MService\Payment\Shared\SharedModels;

use MService\Payment\Shared\SharedModels\MoMoLogger;

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
    public function __construct($momoEndpoint, $partnerInfo, $target, string $channelName = 'MoMoDefault', bool $loggingOff = false, array $handlers = array(), array $processors = array())
    {
        $this->momoEndpoint = $momoEndpoint;
        $this->partnerInfo = $partnerInfo;
        $this->target = $target;
        $this->logger = new MoMoLogger($channelName, $loggingOff, $handlers, $processors);
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
     * @return MoMoLogger
     */
    public function getLogger(): MoMoLogger
    {
        return $this->logger;
    }

    /**
     * @param MoMoLogger $logger
     */
    public function setLogger(MoMoLogger $logger): void
    {
        $this->logger = $logger;
    }


}