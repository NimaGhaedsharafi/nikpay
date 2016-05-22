<?php
namespace Nikapps\NikPay\Exceptions;

class RequestTokenFailedException extends PaymentException
{
    protected $message = "Requesting token (reference id) is failed";

    /**
     * @var int|string
     */
    protected $errorCode;

    /**
     * @return int|string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param int|string $errorCode
     * @return $this
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;

        return $this;
    }


}