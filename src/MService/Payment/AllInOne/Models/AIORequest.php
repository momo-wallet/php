<?php


namespace MService\Payment\AllInOne\Models;

class AIORequest
{
    private $partnerCode;
    private $orderId;
    private $orderInfo;
    private $accessKey;
    private $amount;
    private $signature;
    private $extraData;
    private $requestId;

    private $notifyUrl;
    private $returnUrl;
    private $requestType;

    /**
     * AIORequest constructor.
     * @param array $params of properties
     */
    public function __construct(array $params = array())
    {
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
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param mixed $orderId
     */
    public function setOrderId($orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @return mixed
     */
    public function getOrderInfo()
    {
        return $this->orderInfo;
    }

    /**
     * @param mixed $orderInfo
     */
    public function setOrderInfo($orderInfo): void
    {
        $this->orderInfo = $orderInfo;
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
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount(string $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param mixed $signature
     */
    public function setSignature($signature): void
    {
        $this->signature = $signature;
    }

    /**
     * @return mixed
     */
    public function getExtraData()
    {
        return $this->extraData;
    }

    /**
     * @param mixed $extraData
     */
    public function setExtraData($extraData): void
    {
        $this->extraData = $extraData;
    }

    /**
     * @return mixed
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @param mixed $requestId
     */
    public function setRequestId($requestId): void
    {
        $this->requestId = $requestId;
    }

    /**
     * @return mixed
     */
    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    /**
     * @param mixed $notifyUrl
     */
    public function setNotifyUrl($notifyUrl): void
    {
        $this->notifyUrl = $notifyUrl;
    }

    /**
     * @return mixed
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param mixed $returnUrl
     */
    public function setReturnUrl($returnUrl): void
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * @return mixed
     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * @param mixed $requestType
     */
    public function setRequestType($requestType): void
    {
        $this->requestType = $requestType;
    }

}
