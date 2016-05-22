<?php
namespace Nikapps\NikPay\Exceptions;

class NotVerifiedException extends PaymentException
{
    protected $message = 'Payment is not verified by bank';

    /**
     * @var integer|string
     */
    protected $errorCode;

    /**
     * @return integer|string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param integer|string $errorCode
     * @return $this
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
        $this->code = $errorCode;

        return $this;
    }
}
