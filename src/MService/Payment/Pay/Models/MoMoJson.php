<?php


namespace MService\Payment\Pay\Models;

class MoMoJson
{
    private $partnerCode;
    private $partnerRefId;
    private $momoTransId;
    private $amount;
    private $description;
    private $transid;
    private $phoneNumber;
    private $status;
    private $message;
    private $billId;
    private $discountAmount;
    private $fee;
    private $customerName;
    private $storeId;
    private $requestDate;
    private $responseDate;

    /**
     * Json constructor.
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
    public function getMomoTransId()
    {
        return $this->momoTransId;
    }

    /**
     * @param mixed $momoTransId
     */
    public function setMomoTransId($momoTransId): void
    {
        $this->momoTransId = $momoTransId;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
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

    /**
     * @return mixed
     */
    public function getTransid()
    {
        return $this->transid;
    }

    /**
     * @param mixed $transid
     */
    public function setTransid($transid): void
    {
        $this->transid = $transid;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param mixed $phoneNumber
     */
    public function setPhoneNumber($phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
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
    public function setStatus($status): void
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
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getBillId()
    {
        return $this->billId;
    }

    /**
     * @param mixed $billId
     */
    public function setBillId($billId): void
    {
        $this->billId = $billId;
    }

    /**
     * @return mixed
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * @param mixed $discountAmount
     */
    public function setDiscountAmount($discountAmount): void
    {
        $this->discountAmount = $discountAmount;
    }

    /**
     * @return mixed
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * @param mixed $fee
     */
    public function setFee($fee): void
    {
        $this->fee = $fee;
    }

    /**
     * @return mixed
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * @param mixed $customerName
     */
    public function setCustomerName($customerName): void
    {
        $this->customerName = $customerName;
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @param mixed $storeId
     */
    public function setStoreId($storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @return mixed
     */
    public function getRequestDate()
    {
        return $this->requestDate;
    }

    /**
     * @param mixed $requestDate
     */
    public function setRequestDate($requestDate): void
    {
        $this->requestDate = $requestDate;
    }

    /**
     * @return mixed
     */
    public function getResponseDate()
    {
        return $this->responseDate;
    }

    /**
     * @param mixed $responseDate
     */
    public function setResponseDate($responseDate): void
    {
        $this->responseDate = $responseDate;
    }

}