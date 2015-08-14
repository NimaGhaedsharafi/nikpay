<?php
namespace Nikapps\NikPay\Exceptions;

class NotEqualAmountException extends PaymentException
{

    protected $message = "Given amount form bank is not equal to provided amount";
    /**
     * @var string
     */
    protected $bankAmount;

    /**
     * @var string
     */
    protected $invoice;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @return string
     */
    public function getBankAmount()
    {
        return $this->bankAmount;
    }

    /**
     * @param string $bankAmount
     * @return $this
     */
    public function setBankAmount($bankAmount)
    {
        $this->bankAmount = $bankAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param string $invoice
     * @return $this
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }

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