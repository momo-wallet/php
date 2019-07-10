<?php


namespace MService\Payment\AllInOne\Models;

use MService\Payment\Shared\Constants\RequestType;

class CaptureMoMoRequest extends AIORequest
{
    public function __construct(array $params = array())
    {
        parent::__construct($params);
        $this->setRequestType(RequestType::CAPTURE_MOMO_WALLET);
    }
}