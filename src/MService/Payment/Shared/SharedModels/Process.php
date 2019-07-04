<?php


namespace MService\Payment\Shared\SharedModels;

class Process
{
    private $environment;
    private $partnerInfo;

    /**
     * AbstractProcess constructor.
     * @param $environment
     * @param $partnerInfo
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
        $this->partnerInfo = $environment->getPartnerInfo();
    }

    public static function errorMoMoProcess($code)
    {
        switch ($code) {
            case 0:
                break;
            case 1:
                throw new Exception("Empty access key or partner code");
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