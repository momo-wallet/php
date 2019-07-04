<?php


namespace MService\Payment\Pay\Models;

class QRNotificationResponse extends PayResponse
{
    private $partnerRefId;
    private $momoTransId;
    private $amount;

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

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return array_filter(array_merge($vars, parent::jsonSerialize()), function ($var) {
            return !is_null($var);
        });
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
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }


}