<?php


namespace MService\Payment\Pay\Models;

class PaymentConfirmationRequest extends PayRequest
{
    private $momoTransId;
    private $requestType;
    private $requestId;
    private $signature;

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
     * @return string
     */
    public function getMomoTransId()
    {
        return $this->momoTransId;
    }

    /**
     * @param string $momoTransId
     */
    public function setMomoTransId($momoTransId): void
    {
        $this->momoTransId = $momoTransId;
    }

    /**
     * @return string
     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * @param string $requestType
     */
    public function setRequestType($requestType): void
    {
        $this->requestType = $requestType;
    }

    /**
     * @return string
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @param string $requestId
     */
    public function setRequestId($requestId): void
    {
        $this->requestId = $requestId;
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param string $signature
     */
    public function setSignature($signature): void
    {
        $this->signature = $signature;
    }
}