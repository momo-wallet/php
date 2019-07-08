<?php


namespace MService\Payment\Shared\Utils;

use MService\Payment\Shared\SharedModels\Environment;

class Process
{
    protected $environment;
    protected $partnerInfo;
    protected $logger;

    /**
     * AbstractProcess constructor.
     * @param $environment
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
        $this->partnerInfo = $environment->getPartnerInfo();
        $this->logger = $environment->getLogger();
    }

    public static function errorMoMoProcess($code)
    {
        switch ($code) {
            case 0:
                break;
            case 1:
                throw new MoMoException("Empty access key or partner code");
        }
    }

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

}