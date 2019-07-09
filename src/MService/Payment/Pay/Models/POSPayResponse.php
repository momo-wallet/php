<?php


namespace MService\Payment\Pay\Models;

class POSPayResponse
{
    private $status;
    private $message;

    public function __construct(array $params = array())
    {
        $vars = get_object_vars($this);
        $this->setMessage($params['message']);
        $this->setStatus($params['status']);
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        if (is_null($message)) {
            $this->message = new MoMoJson();
        } else if (is_array($message)) {
            $this->message = new MoMoJson($message);
        } else {
            $this->message = $message;
        }
    }

}