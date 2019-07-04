<?php


namespace MService\Payment\PayGate\Models;

use MService\Payment\PayGate\Models\AIOResponse;

class CaptureIPNRequest extends AIOResponse
{
    public function __construct(array $params = array())
    {
        parent::__construct($params);
    }

    public function jsonSerialize()
    {
        return array_filter(parent::jsonSerialize(), function ($var) { return !is_null($var); });
    }

}