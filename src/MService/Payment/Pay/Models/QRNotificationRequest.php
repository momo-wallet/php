<?php


namespace MService\Payment\Pay\Models;

class QRNotificationRequest extends PayResponse
{
    private $partnerRefId;
    private $momoTransId;
    private $amount;
    private $partnerCode;
    private $accessKey;
    private $partnerTransId;
    private $transType;
    private $responseTime;
    private $storeId;

    public function __construct(array $params = array())
    {
        parent::__construct($params);
        $vars = get_object_vars($this);

        foreach ($vars as $key => $value) {
            if (array_key_exists($key, $params)) {
                $this->{$key} = $params[$key];
            }
        }
    }

    /**
     * @return mixed
     */
    public function getPartnerRefId()
    {
        return $this->partnerRefId;
    }

    /**
     * @param mixed $partnerRefId
     */
    public function setPartnerRefId($partnerRefId): void
    {
        $this->partnerRefId = $partnerRefId;
    }

    /**
     * @return mixed
     */
    public function getMomoTransId()
    {
        return $this->momoTransId;
    }

    /**
     * @param mixed $momoTransId
     */
    public function setMomoTransId($momoTransId): void
    {
        $this->momoTransId = $momoTransId;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getPartnerCode()
    {
        return $this->partnerCode;
    }

    /**
     * @param mixed $partnerCode
     */
    public function setPartnerCode($partnerCode): void
    {
        $this->partnerCode = $partnerCode;
    }

    /**
     * @return mixed
     */
    public function getAccessKey()
    {
        return $this->accessKey;
    }

    /**
     * @param mixed $accessKey
     */
    public function setAccessKey($accessKey): void
    {
        $this->accessKey = $accessKey;
    }

    /**
     * @return mixed
     */
    public function getPartnerTransId()
    {
        return $this->partnerTransId;
    }

    /**
     * @param mixed $partnerTransId
     */
    public function setPartnerTransId($partnerTransId): void
    {
        $this->partnerTransId = $partnerTransId;
    }

    /**
     * @return mixed
     */
    public function getTransType()
    {
        return $this->transType;
    }

    /**
     * @param mixed $transType
     */
    public function setTransType($transType): void
    {
        $this->transType = $transType;
    }

    /**
     * @return mixed
     */
    public function getResponseTime()
    {
        return $this->responseTime;
    }

    /**
     * @param mixed $responseTime
     */
    public function setResponseTime($responseTime): void
    {
        $this->responseTime = $responseTime;
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @param mixed $storeId
     */
    public function setStoreId($storeId): void
    {
        $this->storeId = $storeId;
    }

}