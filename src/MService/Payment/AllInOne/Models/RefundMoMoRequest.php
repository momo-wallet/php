<?php


namespace MService\Payment\AllInOne\Models;

use MService\Payment\Shared\Constants\RequestType;

class RefundMoMoRequest extends AIORequest
{
    private $transId;

    public function __construct(array $params = array())
    {
        parent::__construct($params);
        $vars = get_object_vars($this);

        foreach ($vars as $key => $value) {
            if (array_key_exists($key, $params)) {
                $this->{$key} = $params[$key];
            }
        }

        $this->setRequestType(RequestType::REFUND_MOMO_WALLET);
    }

    /**
     * @return mixed
     */
    public function getTransId()
    {
        return $this->transId;
    }

    /**
     * @param mixed $transId
     */
    public function setTransId($transId): void
    {
        $this->transId = $transId;
    }

}