<?php
namespace Nikapps\NikPay\Exceptions;

use Exception;

class NotFoundInvoiceException extends Exception
{
    protected $message = 'Invoice number is not found';

    /**
     * @var string|int
     */
    protected $invoice;

    /**
     * @var string
     */
    protected $token;

    /**
     * @return int|string
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param int|string $invoice
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
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }


}