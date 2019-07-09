<?php

namespace MService\Payment\Pay\Models;

class TransactionRefundResponse extends PayResponse
{
    private $partnerRefId;
    private $transid;
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
    public function getTransid()
    {
        return $this->transid;
    }

    /**
     * @param mixed $transid
     */
    public function setTransid($transid): void
    {
        $this->transid = $transid;
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

}

