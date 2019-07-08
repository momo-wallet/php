<?php


namespace MService\Payment\Pay\Models;

class PaymentConfirmationResponse extends PayResponse
{
    private $data;

    public function __construct(array $params = array())
    {
        parent::__construct($params);

        if (array_key_exists('data', $params)) {
            $this->setData($params['data']);
        }
    }

    /**
     * @return MoMoJson
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed (array or MoMoJson object) $data
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
