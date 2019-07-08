<?php


namespace MService\Payment\Pay\Models;

class PayRequest
{
    private $partnerCode;
    private $partnerRefId;
    private $customerNumber;
    private $description;

    /**
     * PayRequest constructor.
     * @param array of properties
     */
    public function __construct(array $params = array())
    {
        $vars = get_object_vars($this);

        foreach ($vars as $key => $value) {
            if (array_key_exists($key, $params)) {
                $this->{$key} = $params[$key];
            }
        }
    }

    /**
     * @return mixed
     */
    public function getPartnerCode()
    {
        return $this->partnerCode;
    }

    /**
     * @param mixed $partnerCode
     */
    public function setPartnerCode($partnerCode): void
    {
        $this->partnerCode = $partnerCode;
    }

    /**
     * @return mixed
     */
    public function getPartnerRefId()
    {
        return $this->partnerRefId;
    }

    /**
     * @param mixed $partnerRefId
     */
    public function setPartnerRefId($partnerRefId): void
    {
        $this->partnerRefId = $partnerRefId;
    }

    /**
     * @return mixed
     */
    public function getCustomerNumber()
    {
        return $this->customerNumber;
    }

    /**
     * @param mixed $customerNumber
     */
    public function setCustomerNumber($customerNumber): void
    {
        $this->customerNumber = $customerNumber;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

}