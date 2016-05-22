<?php
namespace Nikapps\NikPay\PaymentProviders\Mellat;

interface MellatInvoiceFinder
{
    /**
     * Find invoice number by token
     *
     * @param string $token
     * @return string|int|null
     */
    public function find($token);
}