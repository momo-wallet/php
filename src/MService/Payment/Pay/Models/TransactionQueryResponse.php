<?php


namespace MService\Payment\Pay\Models;


class TransactionQueryResponse extends PayResponse
{
    private $data;

    public function __construct(array $params = array())
    {
        parent::__construct($params);
        $this->setData($params['data']);
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return array_merge($vars, parent::jsonSerialize());
    }

    /**
     * @return mixed
     */
    public function getData(): MoMoJson
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        if (is_null($data)) {
            $this->data = new MoMoJson();
        } else if (is_array($data)) {
            $this->data = new MoMoJson($data);
        } else {
            $this->data = $data;
        }
    }
}