<?php


namespace MService\Payment\PayGate\Models;

class CaptureIPNRequest extends AIOResponse
{
    public function __construct(array $params = array())
    {
        parent::__construct($params);
    }

}