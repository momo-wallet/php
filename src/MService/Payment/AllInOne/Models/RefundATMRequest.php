<?php


namespace MService\Payment\AllInOne\Models;

use MService\Payment\Shared\Constants\RequestType;

class RefundATMRequest extends RefundMoMoRequest
{
    private $bankCode;

    public function __construct(array $params = array())
    {
        parent::__construct($params);
        $vars = get_object_vars($this);

        foreach ($vars as $key => $value) {
            if (array_key_exists($key, $params)) {
                $this->{$key} = $params[$key];
            }
        }

        $this->setRequestType(RequestType::REFUND_ATM);
    }

    /**
     * @return mixed
     */
    public function getBankCode()
    {
        return $this->bankCode;
    }

    /**
     * @param mixed $bankCode
     */
    public function setBankCode($bankCode): void
    {
        $this->bankCode = $bankCode;
    }


}