<?php

use Nikapps\NikPay\Bank;
use Nikapps\NikPay\InvoiceVerifier;
use Nikapps\NikPay\NikPay;
use Nikapps\NikPay\PaymentProviders\Mellat\MellatConfig;
use Nikapps\NikPay\Purchase;

require_once __DIR__ . '/vendor/autoload.php';

$config = (new MellatConfig())
    ->setMerchantId('terminal/merchant id')
    ->setPassword('password')
    ->setUsername('username');

$mellat = (new NikPay())
    ->bank(Bank::MELLAT, $config);

$purchase = (new Purchase())
    ->setAmount(1000)
    ->rial()
    ->setInvoice(123456789); // unique invoice number

echo $mellat->prepare($purchase)
    ->generateForm();

/* --------- Callback --------- */

$result = $mellat->invoiceVerifier(new DefaultInvoiceVerifier())
    ->handleCallback()
    ->result();

echo $result->reference();


// Implement InvoiceVerifier
class DefaultInvoiceVerifier implements InvoiceVerifier
{

    /**
     * Verify reference is not already exist (checking for double spending)
     *
     * @param string|integer $reference [Given by bank]
     * @return boolean true if invoice is valid
     */
    public function verifyReference($reference)
    {
        // TODO: Implement verifyReference() method.
        return true;
    }

    /**
     * @param string|integer $invoice [Given by bank]
     * @param integer $amount [Given by bank]
     * @return boolean true if amount is valid
     */
    public function verifyAmount($invoice, $amount)
    {
        return true;
    }
}
