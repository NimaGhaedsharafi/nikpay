<?php
namespace Nikapps\NikPay\Exceptions;

class FailedPaymentException extends PaymentException
{

    /**
     * Payment state
     *
     * @var string
     */
    protected $state;

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }


} 