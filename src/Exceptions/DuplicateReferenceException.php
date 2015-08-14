<?php
namespace Nikapps\NikPay\Exceptions;

class DuplicateReferenceException extends PaymentException
{

    protected $message = "The reference number already exists";

    /**
     * @var string
     */
    protected $reference;

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     * @return $this
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }


} 