<?php


namespace MService\Payment\Pay\Models;

class AppPayRequest extends POSPayRequest
{
    private $appData;

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
        return array_filter(array_merge($vars, parent::jsonSerialize()), function ($var) {return !is_null($var);});
    }

    /**
     * @return mixed
     */
    public function getAppData()
    {
        return $this->appData;
    }

    /**
     * @param mixed $appData
     */
    public function setAppData($appData): void
    {
        $this->appData = $appData;
    }
}
