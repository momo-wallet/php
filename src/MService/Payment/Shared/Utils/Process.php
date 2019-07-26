<?php


namespace MService\Payment\Shared\Utils;

use MService\Payment\Shared\SharedModels\MoMoLogger;
use MService\Payment\Shared\SharedModels\Environment;

abstract class Process
{
    protected $environment;
    protected $partnerInfo;
    protected $logger;

    /**
     * Process constructor.
     * @param $environment
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
        $this->partnerInfo = $environment->getPartnerInfo();
        $this->logger = $environment->getLogger();
    }

    abstract protected function execute($request);

    /**
     * @return mixed
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param mixed $environment
     */
    public function setEnvironment($environment): void
    {
        $this->environment = $environment;
    }

    /**
     * @return mixed
     */
    public function getPartnerInfo()
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