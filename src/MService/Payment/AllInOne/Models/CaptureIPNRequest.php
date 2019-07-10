<?php


namespace MService\Payment\AllInOne\Models;

class CaptureIPNRequest extends AIOResponse
{
    public function __construct(array $params = array())
    {
        parent::__construct($params);
    }

}