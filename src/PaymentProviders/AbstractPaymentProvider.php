<?php
namespace Nikapps\NikPay\PaymentProviders;

use Nikapps\NikPay\InvoiceVerifier;
use Nikapps\NikPay\Purchase;

abstract class AbstractPaymentProvider implements PaymentProvider
{

    /**
     * @var InvoiceVerifier
     */
    protected $invoiceVerifier;

    public function fetchToken(Purchase $purchase)
    {
        return $this->prepare($purchase);
    }

    public function token(Purchase $purchase)
    {
        return $this->prepare($purchase);
    }

    public function redirect($form = null, $token = null)
    {
        return $this->generateForm($form, $token);
    }

    public function reverse($reference, array $options = [])
    {
        return $this->refund($reference, $options);
    }

    public function reverseTransaction($reference, array $options = [])
    {
        return $this->refund($reference, $options);
    }

    public function recur($reference, array $options = [])
    {
        return $this->refund($reference, $options);
    }

    public function getResult()
    {
        return $this->getResult();
    }

    public function invoiceVerifier(InvoiceVerifier $invoiceVerifier)
    {
        $this->invoiceVerifier = $invoiceVerifier;

        return $this;
    }

}