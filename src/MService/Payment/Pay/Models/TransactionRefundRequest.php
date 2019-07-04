<?php


namespace MService\Payment\Pay\Models;

class TransactionRefundRequest extends TransactionQueryRequest
{
    private $requestId;

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
        return array_filter(array_merge($vars, parent::jsonSerialize()), function ($var) { return !is_null($var); });
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

}