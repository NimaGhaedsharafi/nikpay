<?php
namespace Nikapps\NikPay\Exceptions;

class NotFoundAmountException extends PaymentException
{

    /**
     * @var string
     */
    private $reference;

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     * @return NotFoundAmountException
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

}