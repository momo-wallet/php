<?php


namespace MService\Payment\Pay\Models;

class TransactionQueryRequest extends PayRequest
{
    private $hash;
    private $version;
    private $momoTransId;

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
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param mixed $hash
     */
    public function setHash($hash): void
    {
        $this->hash = $hash;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version): void
    {
        $this->version = $version;
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


}